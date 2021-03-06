<?php

namespace App\Console\Commands;

use App\Events\NewsEvent;
use App\Events\OrganisationUpdated;
use App\Exceptions\HTTPException;
use App\Models\Categorie;
use App\Models\Categories;
use App\Models\Comments;
use App\Models\EmailValidation;
use App\Models\Event;
use App\Models\Item;
use App\Models\Organisations;
use App\Models\Protocol;
use App\Models\ProtocolItems;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\UserOrganisations;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// app components

/**
 * Import the Data from the current LIVE-API
 *
 * This is a temporary API Function.
 *
 * @package App\Console\Commands
 */
class ImportFromLiveAPI extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'import:fromLiveAPI';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Data from the Live Mongo-DB API (migration from live)';


    /**
     * @param \GuzzleHttp\Client $client
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(\GuzzleHttp\Client $client): void
    {
        $start = time();
        $this->call('migrate:fresh');

        $this->info("Create System Users");

        $user = User::query()->where("id", 1)->first();
        if ($user === null) {
            $user = new User();
            $user->name = "system";
            $user->email = "info@tageso.de";
            $user->status = "disabled";
            $user->admin = true;
            $user->mailStatus = "disabled";
            $user->password = hash("sha512", "system");
            $user->saveOrFail();

            $userProfile = new UserProfile();
            $userProfile->username = "System";
            $userProfile->user_id = $user->id;
            $userProfile->saveOrFail();

            $user = new User();
            $user->name = "codeception";
            $user->email = "codeception@tageso.de";
            $user->mailStatus = "disabled";
            $user->admin = false;
            $user->status = "disabled";
            $user->password = hash("sha512", "codeception");
            $user->saveOrFail();

            $user = new User();
            $user->name = "admin";
            $user->email = "admin@tageso.de";
            $user->mailStatus = "disabled";
            $user->admin = false;
            $user->status = "active";
            $user->password = hash("sha512", "admin");
            $user->saveOrFail();
        }

        //Import other users

        $this->info("Migrate Users");

        $res = $client->request('GET', 'https://api.tageso.de/migration/accounts', [
            'headers' => ['Authorization' => getenv("TMP_LIVE_API")]
        ]);
        $res = json_decode((string)$res->getBody());
        foreach ($res->data as $data) {
            if (empty($data->mail)) {
                continue;
            }
            $user = new User();
            $user->name = $data->username;
            $user->email = $data->mail;
            $user->password = $data->password;
            $user->admin = $data->admin;
            $user->developer = $data->developer;
            $user->systemAccount = $data->systemAccount;
            $user->status = "active";
            if ($data->openMailCheck) {
                $user->status = "validateSend";
            }

            if ($data->delete) {
                $user->status = "deleted";
            }
            $user->mailStatus = "active";
            if ($data->disabledMails) {
                $user->mailStatus = "disabled";
            }
            $user->disabledMailsToken = $data->disabledMailsToken;
            $user->old_uid = $data->_id;
            $user->saveOrFail();

            //Create E-Mail Validation Objects
            $emailValidation = new EmailValidation();
            $emailValidation->email = $user->email;
            $emailValidation->user_id = $user->id;
            $emailValidation->token = $data->mailCheckToken;

            if ($data->openMailCheck) {
                $emailValidation->status = "validationSend";
            } else {
                $emailValidation->status = "validated";
            }
            $emailValidation->used_for = "user";
            $emailValidation->saveOrFail();


            // Create User Profile
            if ($user->status == "active") {
                $userProfile = new UserProfile();
                $userProfile->username = $data->callName;
                $userProfile->user_id = $user->id;
                $userProfile->saveOrFail();
            }

            //Create last login
            $userLogin = new UserLogin();
            $userLogin->user_id = $user->id;
            $userLogin->login = date("Y-m-d H:i:s", $data->lastLogin);
            $userLogin->saveOrFail();
        }

        //Organisations
        $this->info("Migrate Organisations");

        $res = $client->request('GET', 'https://api.tageso.de/migration/agendas', [
            'headers' => ['Authorization' => getenv("TMP_LIVE_API")]
        ]);
        $res = json_decode((string)$res->getBody());


        foreach ($res->data as $data) {
            $organisation = new Organisations();
            $organisation->name = $data->name;
            $organisation->public = $data->public;
            $organisation->url = $data->_id;
            $organisation->status = "active";
            if ($data->delete) {
                $organisation->status = "deleted";
            }
            $organisation->user_id = 1;
            $organisation->old_uid = $data->_id;
            $organisation->saveOrFail();
        }

        //Organisation Access
        $this->info("Migrate Organisation Access");
        $res = $client->request('GET', 'https://api.tageso.de/migration/agendas/access', [
            'headers' => ['Authorization' => getenv("TMP_LIVE_API")]
        ]);
        $res = json_decode((string)$res->getBody());

        foreach ($res->data as $data) {
            $userOrganisation = new UserOrganisations();
            $user = User::query()->where("old_uid", "=", $data->account)->first();
            if ($user === null) {
                var_dump($data);
                var_dump($user);
            }

            $organisation = Organisations::query()->where("old_uid", "=", $data->agenda)->first();

            $userOrganisation->user_id = $user->id;
            $userOrganisation->organisation_id = $organisation->id;
            $userOrganisation->access = $data->access;
            $userOrganisation->read = $data->read;
            $userOrganisation->comment = (boolean)$data->comment;
            $userOrganisation->edit = $data->edit;
            $userOrganisation->protocol = $data->protocol;
            $userOrganisation->admin = $data->admin;
            $userOrganisation->notification_protocol = $data->notificationMailProtocol;
            $userOrganisation->old_uid = $data->_id;
            $userOrganisation->saveOrFail();
        }


        //Organisation Categories
        $this->info("Migrate Organisation Categories");
        $res = $client->request('GET', 'https://api.tageso.de/migration/categories', [
            'headers' => ['Authorization' => getenv("TMP_LIVE_API")]
        ]);
        $res = json_decode((string)$res->getBody());

        foreach ($res->data as $data) {
            $organisation = Organisations::query()->where("old_uid", "=", $data->agenda)->first();
            if ($organisation === null) {
                $this->warn("Organisation ".$data->agenda." not found");
                continue;
            }
            $categorie = new Categories();
            $categorie->name = "";
            $categorie->old_uid = $data->_id;
            $categorie->name = $data->name;
            if ($data->position === null) {
                $this->warn("Position for Cat ".$data->_id.": ".$data->name." not found");
                $data->position = 0;
            }
            $categorie->position = $data->position;
            $categorie->organisation_id = $organisation->id;
            $categorie->user_id = 1;
            $categorie->status = "active";
            if ($data->deleted == true) {
                $categorie->status = "deleted";
            }
            $categorie->saveOrFail();
        }

        //Items per Categorie
        $this->info("Migrate Items");
        $res = $client->request('GET', 'https://api.tageso.de/migration/items', [
            'headers' => ['Authorization' => getenv("TMP_LIVE_API")]
        ]);
        $res = json_decode((string)$res->getBody());

        foreach ($res->data as $data) {
            $item = new Item();
            $item->user_id = User::query()->where("old_uid", "=", $data->account)->first()->id;
            $item->category_id = Categories::query()->where("old_uid", "=", $data->category)->first()->id;
            $item->status = "active";
            if ($data->done == true) {
                $item->status = "closed";
            }
            $item->name = $data->name;
            $item->description = $data->description;
            $item->position = $data->position;
            if ($item->position === null) {
                $item->position = 0;
                $this->warn("Position for Item ".$item->name." not found");
            }
            $item->old_uid = $data->_id;
            $item->saveOrFail();
        }

        $this->info("Migrate Protocols");
        $res = $client->request('GET', 'https://api.tageso.de/migration/protocol', [
            'headers' => ['Authorization' => getenv("TMP_LIVE_API")]
        ]);
        $res = json_decode((string)$res->getBody());

        foreach ($res->data as $data) {
            $organisation = Organisations::query()->where("old_uid", "=", $data->agenda)->first();
            $protocol = new Protocol();
            $protocol->organisation_id = $organisation->id;
            $protocol->user_id = User::query()->where("old_uid", "=", $data->accountCreate)->first()->id;
            $protocol->user_closed = User::query()->where("old_uid", "=", $data->accountClosed)->first()->id;
            $protocol->status = "open";
            if ($data->canceld) {
                $protocol->status = "canceled";
            }

            if ($data->done && !$data->canceld) {
                $protocol->status = "closed";
            }
            $protocol->start = date("Y-m-d H:i:s", strtotime($data->date));
            $protocol->ende = date("Y-m-d H:i:s", strtotime($data->date));
            $protocol->old_uid = $data->_id;
            $protocol->saveOrFail();

            //Categories
            $position = 0;
            foreach ($data->agendaItems as $agendaItem) {
                $this->info("Migrate Categories for ".$organisation->name);
                $category = Categories::query()->where("old_uid", "=", $agendaItem->_id)->first();
                if ($category->position != $position) {
                    $event = new Event();
                    $event->eventType = "App\Events\CategoryUpdated";
                    $event->eventObjectId = $category->id;
                    $changeArray = ["position" => ["old" => $position, "new" => $category->position]];
                    if ($category->name != $agendaItem->name) {
                        $changeArray["name"] =["old" => $agendaItem->name, "new" => $category->name];
                    }
                    $event->payload = \GuzzleHttp\json_encode(["changes" => $changeArray]);
                    $event->setCreatedAt(date("Y-m-d H:i:s", strtotime($data->date) + 10));
                    $event->setUpdatedAt(date("Y-m-d H:i:s", strtotime($data->date) + 10));
                    $event->saveOrFail();
                }
                $itemPosition = 0;
                foreach ($agendaItem->items as $itemData) {
                    $item = Item::query()->where("old_uid", "=", $itemData->_id)->first();
                    if ($item->position != $itemPosition) {
                        $event = new Event();
                        $event->eventType = "App\Events\ItemUpdated";
                        $event->eventObjectId = $item->id;
                        $changeArray = ["position" => ["old" => $position, "new" => $item->position]];
                        if ($item->name != $itemData->name) {
                            $changeArray["name"] =["old" => $itemData->name, "new" => $item->name];
                        }
                        if ($item->description != $itemData->description) {
                            $changeArray["description"] =["old" => $itemData->description, "new" => $item->description];
                        }
                        $event->payload = \GuzzleHttp\json_encode(["changes" => $changeArray]);
                        $event->setCreatedAt(date("Y-m-d H:i:s", strtotime($data->date) + 10));
                        $event->setUpdatedAt(date("Y-m-d H:i:s", strtotime($data->date) + 10));
                        $event->saveOrFail();
                    }
                }
                $position++;
            }
        }

        $this->info("Migrate Protocol Items");
        $res = $client->request('GET', 'https://api.tageso.de/migration/protocoentry', [
            'headers' => ['Authorization' => getenv("TMP_LIVE_API")]
        ]);
        $res = json_decode((string)$res->getBody());
        foreach ($res->data as $data) {
            if ($data->text == "" && $data->close == false) {
                $this->warn("Empty and unclosed Protocol entry");
                continue;
            }
            $protocol = Protocol::query()->where("old_uid", "=", $data->protocol)->first();
            $user = User::query()->where("old_uid", "=", $data->autor)->first();
            $item = Item::query()->where("old_uid", "=", $data->agendaItem)->first();

            $protocolItem = new ProtocolItems();
            $protocolItem->user_id = $user->id;
            $protocolItem->protocol_id = $protocol->id;
            $protocolItem->old_uid = $data->_id;
            $protocolItem->item_id = $item->id;
            $protocolItem->description = $data->text;
            $protocolItem->markedAsClosed = $data->close;
            $protocolItem->saveOrFail();
        }

        $this->info("Migrate Comments for Items");
        $res = $client->request('GET', 'https://api.tageso.de/migration/comments', [
            'headers' => ['Authorization' => getenv("TMP_LIVE_API")]
        ]);
        $res = json_decode((string)$res->getBody());
        foreach ($res->data as $data) {
            $comment = new Comments();
            $item = Item::query()->where("old_uid", "=", $data->agendaItem)->first();
            $user = User::query()->where("old_uid", "=", $data->account)->first();

            $comment->user_id = $user->id;
            $comment->item_id = $item->id;
            $comment->text = $data->text;
            $comment->setCreatedAt(date("Y-m-d H:i:s", $data->date));
            $comment->setUpdatedAt(date("Y-m-d H:i:s", $data->date));
            $comment->old_uid = $data->_id;
            $comment->saveOrFail();
        }

        $this->info("Migrate Notifications");
        $res = $client->request('GET', 'https://api.tageso.de/migration/notifications', [
            'headers' => ['Authorization' => getenv("TMP_LIVE_API")]
        ]);
        $res = json_decode((string)$res->getBody());
        foreach ($res->data as $data) {
            $target = User::query()->where("old_uid", "=", $data->targetAccount)->first();
            $user = User::query()->where("old_uid", "=", $data->account)->first();
            $organisation = Organisations::query()->where("old_uid", "=", $data->agenda)->first();

            $newsEvent = new NewsEvent($user->id, $target->id, $data->typ);
            $newsEvent->overwriteTimestamp($data->date);
            $newsEvent->setOrganisation($organisation->id);


            if ($data->agendaItem != null) {
                $agendaI = Item::query()->where("old_uid", "=", $data->agendaItem)->first();
                $newsEvent->setItem($agendaI->id);
            }


            if (isset($data->payload->protocolID)) {
                $protocol = Protocol::query()->where("old_uid", "=", $data->payload->protocolID)->first();
                $newsEvent->setProtocol($protocol->id);
            }

            if (isset($data->payload->comment)) {
                $newsEvent->setText($data->payload->comment);
            }

            event($newsEvent);
        }

        $dauer = time() - $start;
        $this->info("Dauer Migration der Daten aus dem Live System: ".$dauer." Sekunden");
    }
}
