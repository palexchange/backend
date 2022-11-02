<?php

namespace App\Providers;

use App\Events\DocumentDeletedEvent;
use App\Events\DocumentStoredEvent;
use App\Events\DocumentUpdatedEvent;
use App\Listeners\HandleDocumentDeletedEvent;
use App\Listeners\HandleDocumentStoreEvent;
use App\Listeners\HandleDocumentUpdatedEvent;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Observers\UserObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        DocumentStoredEvent::class => [
            HandleDocumentStoreEvent::class
        ],
        DocumentUpdatedEvent::class => [
            HandleDocumentUpdatedEvent::class
        ],
        DocumentDeletedEvent::class => [
            HandleDocumentDeletedEvent::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
