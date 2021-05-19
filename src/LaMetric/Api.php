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

        return $this->mapData([
            'price' => '$' . round($data->c, 2)
        ]);
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
        $frame = new Frame();
        $frame->setText($data['price']);
        $frame->setIcon('45180');

        $frameCollection->addFrame($frame);

        return $frameCollection;
    }
}
