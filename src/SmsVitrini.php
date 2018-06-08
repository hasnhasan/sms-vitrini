<?php

namespace HasnHasan\SmsVitrini;
use Illuminate\Support\Facades\Facade;

/**
 * Class SmsVitrini.
 *
 * @method static SmsVitriniResponseInterface sendShortMessage(array|string|ShortMessage $receivers, string|null $body = null)
 * @method static SmsVitriniResponseInterface sendShortMessages(array|ShortMessageCollection $messages)
 */
class SmsVitrini extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sms-vitrini';
    }
}
