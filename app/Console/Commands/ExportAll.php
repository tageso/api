<?php

namespace App\Console\Commands;

use App\Jobs\ExportDocxJob;
use App\Jobs\ExportOdtJob;
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
class ExportAll extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'export:all {protocolID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recreate All-Protocol';


    /**
     * @param \GuzzleHttp\Client $client
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(): void
    {
        if (!is_numeric($this->argument('protocolID'))) {
            throw new \Exception("protocolID is not numeric");
        }
        $this->info("Create Protocol Syncron");
        $this->info("Create PDF");
        $job = new ExportPDFJob($this->argument('protocolID'));
        $job->onConnection("sync");
        dispatch($job);
        $this->info("PDF was created");
        $this->info("Create Docx");
        $job = new ExportDocxJob($this->argument('protocolID'));
        $job->onConnection("sync");
        dispatch($job);
        $this->info("Docx was created");
        $this->info("Create ODT");
        $job = new ExportOdtJob($this->argument('protocolID'));
        $job->onConnection("sync");
        dispatch($job);
        $this->info("ODT was created");
    }
}
