<?php

declare(strict_types=1);

namespace LaMetric;

use GuzzleHttp\Client;
use LaMetric\Response\{Frame, FrameCollection};

class Api
{
    /**
     * @param Client $client
     * @param array $credentials
     */
    public function __construct(private Client $client, private array $credentials = [])
    {
    }

    /**
     * @param array $parameters
     *
     * @return FrameCollection
     */
    public function fetchData(array $parameters = []): FrameCollection
    {
        $url = 'https://finnhub.io/api/v1/quote?symbol=' . $parameters['symbol'] . '&token=' . $this->credentials['api_key'];

        $res = $this->client->request('GET', $url);
        $json = (string) $res->getBody();

        $data = json_decode($json);

        $responseData = [
            'price' => '$' . round($data->c, 2)
        ];

        if ($parameters['stock_name'] === 'true') {
            array_unshift($responseData, $parameters['symbol']);
        }
   
        if ($parameters['daily_change'] === 'true') {

            $previousPrice = $data->pc;
            $changePercent = round(100 - ($data->pc / $data->c) * 100, 2); 

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
