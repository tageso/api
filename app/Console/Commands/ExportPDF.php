<?php

namespace App\Console\Commands;

use App\Jobs\ExportPDFJob;
use App\Models\Categorie;
use App\Models\Item;
use App\Models\Organisations;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// app components

/**
 *
 * Setup
 *
 * @package App\Console\Commands
 */
class ExportPDF extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'export:pdf {protocolID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recreate a PDF-Protocol';


    /**
     * @param \GuzzleHttp\Client $client
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(): void
    {
        if (!is_numeric($this->argument('protocolID'))) {
            throw new \Exception("protocolID is not numeric");
        }
        $job = new ExportPDFJob($this->argument('protocolID'));
        $this->info("Create Protocol Syncron");
        $job->onConnection("sync");
        dispatch($job);
        $this->info("PDF was created");
    }
}
