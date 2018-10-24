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
    protected $signature = 'clean:deletetAccounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean User-Profile and Informations from deletet Accounts';


    /**
     * @param \GuzzleHttp\Client $client
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(): void
    {
        $users = User::query()
            ->where("status", "=", "deleted")
            ->where("password", "!=", "")
            ->get();

        if (count($users) > 0) {
            foreach ($users as $user) {
                $user->email = "";
                $user->password = "";
                $user->admin = 0;
                $user->developer = 0;
                $user->systemAccount = 0;
                $user->mailStatus = "disabled";
                $user->mailToken = "";
                $user->disabledMailsToken = "";
                //$user->name = hash("sha512", $user->name); // @todo add again disabled for now
                $user->save();

                $userProfile = UserProfile::query()->where("user_id", "=", $user->id)->first();
                $userProfile->delete();
            }
            Cache::flush();
        }
    }
}
