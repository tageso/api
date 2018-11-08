<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\UserLoggedInEvent' => [
            'App\Listeners\SaveUserLoginListener',
        ],
        'App\Events\UserRegisterEvent' => [
            'App\Listeners\UserRegistertSendValidationMail',
            'App\Listeners\EventLogListener'
        ],
        'App\Events\OrganisationCreate' => [
            'App\Listeners\EventLogListener'
        ],
        'App\Events\OrganisationUpdated' => [
            'App\Listeners\EventLogListener'
        ],
        'App\Events\ItemUpdated' => [
            'App\Listeners\EventLogListener'
        ],
        'App\Events\CategoryUpdated' => [
            'App\Listeners\EventLogListener'
        ],
        'App\Events\NewsEvent' => [
            #'App\Listeners\NewsListener', # Dont use this now
            'App\Listeners\EventLogListener'
        ],
        'App\Events\ProtocolClosed' => [
            'App\Listeners\CreateProtocolExportListener'
        ]


        #
    ];
}
