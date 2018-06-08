<?php

namespace HasnHasan\SmsVitrini;

use GuzzleHttp\Client;
use UnexpectedValueException;
use Erdemkeren\SmsVitrini\Http\Clients;
use Erdemkeren\SmsVitrini\ShortMessage;
use Erdemkeren\SmsVitrini\SmsVitriniService;
use Illuminate\Support\ServiceProvider;
use Erdemkeren\SmsVitrini\ShortMessageFactory;
use Erdemkeren\SmsVitrini\ShortMessageCollection;
use Erdemkeren\SmsVitrini\ShortMessageCollectionFactory;
use Erdemkeren\SmsVitrini\Http\Responses\SmsVitriniResponseInterface;

/**
 * Class SmsVitriniServiceProvider.
 */
class SmsVitriniServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->registerSmsVitriniClient();
        $this->registerSmsVitriniService();
    }

    /**
     * Register the SmsVitrini Client binding with the container.
     *
     * @return void
     */
    private function registerSmsVitriniClient()
    {
        $this->app->bind(Clients\SmsVitriniClientInterface::class, function () {
            $username = config('services.SmsVitrini.username');
            $password = config('services.SmsVitrini.password');
            $originator = config('services.SmsVitrini.originator');

            switch (config('services.SmsVitrini.client', 'http')) {
                case 'http':
                    $timeout = config('services.SmsVitrini.timeout');
                    $endpoint = config('services.SmsVitrini.http.endpoint');
                    $client = new Clients\SmsVitriniHttpClient(
                        new Client(['timeout' => $timeout]), $endpoint, $username, $password, $originator);
                    break;
                case 'xml':
                    $endpoint = config('services.SmsVitrini.xml.endpoint');
                    $client = new Clients\SmsVitriniXmlClient($endpoint, $username, $password, $originator);
                    break;
                default:
                    throw new UnexpectedValueException('Unknown SmsVitrini API client has been provided.');
            }

            return $client;
        });
    }

    /**
     * Register the sms-vitrini service.
     */
    private function registerSmsVitriniService()
    {
        $beforeSingle = function (ShortMessage $shortMessage) {
            event(new Events\SendingMessage($shortMessage));
        };

        $afterSingle = function (SmsVitriniResponseInterface $response, ShortMessage $shortMessage) {
            event(new Events\MessageWasSent($shortMessage, $response));
        };

        $beforeMany = function (ShortMessageCollection $shortMessages) {
            event(new Events\SendingMessages($shortMessages));
        };

        $afterMany = function (SmsVitriniResponseInterface $response, ShortMessageCollection $shortMessages) {
            event(new Events\MessagesWereSent($shortMessages, $response));
        };

        $this->app->singleton('sms-vitrini', function ($app) use ($beforeSingle, $afterSingle, $beforeMany, $afterMany) {
            return new SmsVitriniService(
                $app->make(Clients\SmsVitriniClientInterface::class),
                new ShortMessageFactory(),
                new ShortMessageCollectionFactory(),
                $beforeSingle,
                $afterSingle,
                $beforeMany,
                $afterMany
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'sms-vitrini',
            Clients\SmsVitriniClientInterface::class,
        ];
    }
}
