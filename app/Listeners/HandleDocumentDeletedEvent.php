<?php

namespace App\Listeners;

use App\Events\DocumentDeletedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleDocumentDeletedEvent
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
     * @param  \App\Events\DocumentDeletedEvent  $event
     * @return void
     */
    public function handle(DocumentDeletedEvent $event)
    {
        # code...
        $document = $event->document;
        if ($document->status == 1) {
            $document->dispose();
        }
    }
}
