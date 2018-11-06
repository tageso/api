<?php

namespace App\Console\Commands;

use App\Jobs\ExportDocxJob;
use App\Jobs\ExportOdtJob;
use App\Jobs\ExportPDFJob;
use App\Models\Categorie;
use App\Models\Item;
use App\Models\Organisations;
use App\Models\Protocol;
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
class ExportEverything extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'export:everything';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recreate every Protocol for every File-Typ';


    /**
     * @param \GuzzleHttp\Client $client
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(): void
    {
        $sync = false;
        $this->info("Recreate every Protocol for every File-Typ");
        $protocols = Protocol::query()->where("status", "=", "closed")->get();
        foreach ($protocols as $protocol) {
            $this->info("Protocol: ".$protocol->id);

            $this->info("Create PDF");
            $job = new ExportPDFJob($protocol->id);
            if ($sync) {
                $this->info("Syncron");
                $job->onConnection("sync");
            } else {
                $job->onConnection("database");
            }
            dispatch($job);
            $this->info("PDF was created");
            $this->info("Create Docx");
            $job = new ExportDocxJob($protocol->id);
            if ($sync) {
                $this->info("Syncron");
                $job->onConnection("sync");
            } else {
                $job->onConnection("database");
            }
            dispatch($job);
            $this->info("Docx was created");
            $this->info("Create ODT");
            $job = new ExportOdtJob($protocol->id);
            if ($sync) {
                $this->info("Syncron");
                $job->onConnection("sync");
            } else {
                $job->onConnection("database");
            }
            dispatch($job);
            $this->info("ODT was created");
        }
    }
}
