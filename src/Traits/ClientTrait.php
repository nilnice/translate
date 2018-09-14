<?php

namespace Nilnice\Translate\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

trait ClientTrait
{
    /**
     * @var array
     */
    protected $guzzleOptions = [];

    /**
     * Get Guzzle http client instance.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getGuzzleClient(): ClientInterface
    {
        return new Client($this->guzzleOptions);
    }

    /**
     * @param array $options
     */
    public function setGuzzleOptions(array $options = []): void
    {
        $this->guzzleOptions = $options;
    }
}