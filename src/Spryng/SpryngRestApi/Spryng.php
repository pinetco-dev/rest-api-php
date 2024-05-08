<?php

namespace Spryng\SpryngRestApi;

use Spryng\SpryngRestApi\Http\HttpClient;
use Spryng\SpryngRestApi\Resources\BalanceClient;
use Spryng\SpryngRestApi\Resources\MessageClient;

class Spryng
{
    public const VERSION = '1.0.0';

    private string $baseUrl = 'https://rest.spryngsms.com/v1';

    public MessageClient $message;

    public BalanceClient $balance;

    public static HttpClient $http;

    protected string $apiKey = '';

    public function __construct(?string $apiKey = '')
    {
        if ($apiKey !== null) {
            $this->setApiKey($apiKey);
        }

        self::$http = new HttpClient();

        $this->message = new MessageClient($this);
        $this->balance = new BalanceClient($this);
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param  mixed  $apiKey
     */
    public function setApiKey($apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get the current version of the library
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }
}
