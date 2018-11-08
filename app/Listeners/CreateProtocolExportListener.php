<?php

namespace App\Listeners;

use App\Console\Commands\ExportPDF;
use App\Events\ExampleEvent;
use App\Events\ProtocolClosed;
use App\Jobs\ExportDocxJob;
use App\Jobs\ExportOdtJob;
use App\Jobs\ExportPDFJob;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateProtocolExportListener
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
     * @param  ProtocolClosed  $event
     * @return void
     */
    public function handle(ProtocolClosed $event)
    {
        $event = $event->getProtocol();
        $job = new ExportPDFJob($event->id);
        dispatch($job);

        $job = new ExportDocxJob($event->id);
        dispatch($job);

        $job = new ExportOdtJob($event->id);
        dispatch($job);
    }
}
