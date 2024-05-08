## Spryng SMS REST Library for PHP

This repository contains the source code for the Spryng SMS REST API library for PHP. This library will make it very easy to integrate the SMS gateway into your application. It offers all the functionality that the API has to offer.

### Installation

Installation is easily done using composer:

```bash
composer require spryng/rest-api-php
```

When the installation is complete, you can initialize the library with your API key:

```php
require_once "vendor/autoload.php";

use Spryng\SpryngRestApi\Spryng;

$spryng = new Spryng($apiKey);

```

### Sending messages

To send a message, supply the `send` method with the information about the message you'd like to send:

```php
use Spryng\SpryngRestApi\Objects\Message;
use Spryng\SpryngRestApi\Spryng;

$spryng = new Spryng($apiKey);

$message = new Message();
$message->setBody('My message');
$message->setRecipients(['31612344567', '31698765432']);
$message->setOriginator('My Company');

$response = $spryng->message->send($message);

if ($response->wasSuccessful())
{
	$message = $response->toObject();
	echo "Message with ID " . $message->getId() . " was send successfully!\n";
}
else if ($response->serverError())
{
	echo "Message could not be send because of a server error...\n";
}
else
{
	echo "Message could not be send. Response code: " . $response->getResponseCode() ."\n";
}
```

### Getting info about a message

Single messages can be queried by their ID:

```php
use Spryng\SpryngRestApi\Objects\Message;
use Spryng\SpryngRestApi\Spryng;

$spryng = new Spryng($apiKey);

$response = $spryng->message->getMessage("9dbc5ffb-7524-4fae-9514-51decd94a44f");

if ($resposne->wasSuccessful())
{
	echo "The body of the message is: " . $response->toObject()->getBody() . "\n";
}
```

### Listing messages

You can list the messages you have send in a paginated manner. You can also apply filters to get a sub-set of the messages you have send:

```php
use Spryng\SpryngRestApi\Objects\Message;
use Spryng\SpryngRestApi\Spryng;

$spryng = new Spryng($apiKey);

$response = $spryng->message->showAll(
	1, // page
	20, // limit: items per page
	[ // An array of filters
		'recipient_number' => '31612345667'
	]
);

if ($response->wasSuccessful())
{
	// Will return an instance of MessageCollection
	$messages = $response->toObject();
	echo "Found " . $messages->getTotal() . " results:\n";
	
	foreach ($messages->getData() as $message)
	{
		echo sprintf("ID: %s ('%s') send on: %s\n", 
			$message->getId(), 
			$message->getBody(), 
			$message->getCreatedAt()
		);
	}
}
```

### Getting your balance

You can also check the remaining credit balance on your account:

```php
use Spryng\SpryngRestApi\Objects\Message;
use Spryng\SpryngRestApi\Spryng;

$spryng = new Spryng($apiKey);

$balance = $spryng->balance->get()->toObject();
echo "You have " . $balance->getAmount() . " credits remaining\n";
```

## Setting up your Spryng account in laravel

Add the environment variables to your `config/services.php`:

```php
// config/services.php
...
'spryng' => [
        'access_key' => env('SPRYNG_ACCESS_KEY', ''),
        'originator' => env('SPRYNG_ORIGINATOR', ''),
        'recipients' => env('SPRYNG_RECIPIENTS', []),
    ],
...
```

Add your Spryng Access Key, Default originator (name or number of sender), and default recipients to your `.env`:

```php
// .env
...
SPRYNG_ACCESS_KEY=
SPRYNG_ORIGINATOR=
SPRYNG_RECIPIENTS=
],
...
```

You can create a 'SpryngChannel.php' in 'App/Channels'

```php
use Illuminate\Notifications\Notification;
use Spryng\SpryngRestApi\Exceptions\ValidationException;
use Spryng\SpryngRestApi\SpryngClient;

class SpryngChannel
{
    private SpryngClient $client;

    public function __construct(SpryngClient $client)
    {
        $this->client = $client;
    }

    public function send($notifiable, Notification $notification)
    {
        $config = config('services.spryng');

        $spryngMessage = $notification->toSpryng($notifiable);

        if (is_string($spryngMessage)) {
            $spryngMessageObj = new \Spryng\SpryngRestApi\Objects\Message();
            $spryngMessageObj->setBody($spryngMessage);
            $spryngMessage = $spryngMessageObj;
        }

        $spryngMessage->setOriginator($config['originator']);

        $data = [];

        if ($to = $notifiable->routeNotificationFor('spryng')) {
            $spryngMessage->setRecipients([$to]);
        }

        try {
            $data = $this->client->send($spryngMessage);
        } catch (ValidationException $e) {
            logger()->error($e->getMessage());
        }

        return $data;
    }
}
```

Now you can use the channel in your `via()` method inside the notification:

``` php
use App\Channels\SpryngChannel;
use Spryng\SpryngRestApi\Objects\Message;
use Illuminate\Notifications\Notification;

class VpsServerOrdered extends Notification
{
    public function via($notifiable)
    {
        return [SpryngChannel::class];
    }

    public function toSpryng($notifiable): Message
    {
        $message = new Message();
        $message->setBody('This is message');

        return $message;
    }
}
```