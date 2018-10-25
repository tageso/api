<?php

namespace App\Console\Commands;

use App\Models\Categorie;
use App\Models\Item;
use App\Models\Organisations;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// app components

/**
 * Exports all open salesOrder
 *
 * @author Horst Schwarz <horst.schwarz@idealo.de>
 *
 * @package App\Console\Commands
 */
class CleanDeletetAccounts extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'tageso:makeAdmin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a User to an Admin via CLI';


    /**
     * @param \GuzzleHttp\Client $client
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(): void
    {
        throw new \Exception("ToDo");
    }
}
