<?php

declare(strict_types=1);

namespace LaMetric;

use GuzzleHttp\Client as HttpClient;
use Predis\Client as RedisClient;
use LaMetric\Response\{Frame, FrameCollection};

class Api
{
    /**
     * @param HttpClient $HttpClient
     * @param array $credentials
     */
    public function __construct(private HttpClient $httpClient, private RedisClient $redisClient, private array $credentials = [])
    {
    }

    /**
     * @param array $parameters
     *
     * @return FrameCollection
     */
    public function fetchData(array $parameters = []): FrameCollection
    {
        $parameters['symbol'] = strtoupper($parameters['symbol']);

        $redisKey = 'lametric:stocks:' . strtolower($parameters['symbol']);
        $json = $this->redisClient->get($redisKey);

        if (!$json) {
            $url = 'https://finnhub.io/api/v1/quote?symbol=' . $parameters['symbol'] . '&token=' . $this->credentials['api_key'];

            $res = $this->httpClient->request('GET', $url);
            $json = (string) $res->getBody();

            $this->redisClient->set($redisKey, $json, 'ex', 60);
        }

        $data = json_decode($json);

        $responseData = [
            'price' => '$' . round($data->c, 2)
        ];

        if ($parameters['stock_name'] === 'true') {
            array_unshift($responseData, $parameters['symbol']);
        }
   
        if ($parameters['daily_change'] === 'true') {

            $changePercent = $data->c !== 0 ? round(100 - ($data->pc / $data->c) * 100, 2) : 0;

            $percentage = ($changePercent >= 0 ? '+' : '') . $changePercent . '%';

            array_push($responseData, $percentage);
        }
        
        return $this->mapData($responseData);
    }

    /**
     * @param array $data
     *
     * @return FrameCollection
     */
    private function mapData(array $data = []): FrameCollection
    {
        $frameCollection = new FrameCollection();

        /**
         * Transform data as FrameCollection and Frame
         */

        foreach ($data as $value) {
            $frame = new Frame();
            $frame->setText($value);

            switch($value[0]) {
                case '-':
                    $frame->setIcon('124');
                    break;
                case '+':
                    $frame->setIcon('120');
                    break;
                case '$':
                    $frame->setIcon('34');
                    break;
                default:
                    $frame->setIcon('');
            }

            $frameCollection->addFrame($frame);
        }

        return $frameCollection;
    }
}
