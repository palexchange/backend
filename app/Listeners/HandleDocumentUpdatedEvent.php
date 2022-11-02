<?php

namespace App\Listeners;

use App\Events\DocumentUpdatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleDocumentUpdatedEvent
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\DocumentUpdatedEvent  $event
     * @return void
     */
    public function handle(DocumentUpdatedEvent $event)
    {
        $document = $event->document;
        if ($document->status == 1 && $document->getRawOriginal('status') == 1) {
            $document->dispose();
        }
    }
}
