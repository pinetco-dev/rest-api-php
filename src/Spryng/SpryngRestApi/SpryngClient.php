<?php

namespace Spryng\SpryngRestApi;

use Illuminate\Support\Facades\Http;
use Spryng\SpryngRestApi\Exceptions\ValidationException;
use Spryng\SpryngRestApi\Objects\Message;

class SpryngClient extends BaseClient
{
    public function send(Message $message)
    {
        try {
            $response = Http::withToken($this->api->getApiKey())
                ->post($this->api->getBaseUrl().'/messages', [
                    'encoding'     => $message->getEncoding(),
                    'body'         => $message->getBody(),
                    'route'        => $message->getRoute(),
                    'originator'   => $message->getOriginator(),
                    'recipients'   => $message->getRecipients(),
                    'reference'    => $message->getReference(),
                    'scheduled_at' => $message->getScheduledAt(),
                ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                // Handle unsuccessful response
                $errorCode = $response->status();
                $errorMessage = $response->body();
                // Process error information as needed
                throw new ValidationException("API request failed with error code $errorCode: $errorMessage");
            }
        } catch (ValidationException $e) {
            // Handle exception
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
}
