<?php

namespace Spryng\SpryngRestApi;

use Illuminate\Support\Facades\Http;
use Spryng\SpryngRestApi\Exceptions\ValidationException;
use Spryng\SpryngRestApi\Objects\Message;

class SpryngClient extends BaseClient
{
    /**
     * @throws \Spryng\SpryngRestApi\Exceptions\ValidationException
     */
    public function send(Message $message)
    {
        try {
            $response = Http::withToken($this->api->getApiKey())
                ->post($this->api->getBaseUrl().'/messages', [
                    'encoding'   => $message->getEncoding(),
                    'body'       => $message->getBody(),
                    'route'      => $message->getRoute(),
                    'originator' => $message->getOriginator(),
                    'recipients' => $message->getRecipients(),
                    'reference'  => $message->getReference(),
                    'scheduled_at' => $message->getScheduledAt(),
                ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new ValidationException($response->body(), $response->status());
            }
        } catch (ValidationException $e) {
            // Handle exception
            throw new ValidationException($e->getMessage());
        }

    }
}
