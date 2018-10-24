<?php

namespace App\Http\Controllers;

use App\Events\ItemUpdated;
use App\Events\OrganisationCreate;
use App\Events\OrganisationUpdated;
use App\Exceptions\NotLoggedInException;
use App\Http\Resources\Access;
use App\Http\Resources\Category;
use App\Http\Resources\Organisation;
use App\Models\Categories;
use App\Models\Item;
use App\Models\Organisations;
use App\Models\UserOrganisations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Event;
use App\Exceptions\HTTPException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TaGeSo\APIResponse\Response;

class AgendaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getAgenda($id, Response $response)
    {
        Log::debug("Get Agenda ".$id);
        $organisation = Organisations::getById($id);
        Log::debug("Found Organisation: ".$organisation->name." (".$organisation->id.")");
        if ($organisation == null) {
            throw new HTTPException("Organisation not found", 404);
        }

        if (!$organisation->public) {
            if (!Auth::check()) {
                throw new NotLoggedInException();
            }

            $organisationAuth = UserOrganisations::getAccess(Auth::user()->id, $organisation->id);

            if ($organisationAuth == null || $organisationAuth->access == false || $organisationAuth->read == false) {
                throw new HTTPException("You don't have permission to see this Page", 403);
            }
        } else {
            Log::debug("Organisation public");
        }


        $categories = Categories::getForOrganisation($organisation->id);

        Log::debug("Found ".count($categories)." Categories");

        foreach ($categories as $category) {
            $category->items = Item::query()
                ->where("category_id", "=", $category->id)
                ->where("status", "=", "active")
                ->orderBy("position", "ASC")
                ->get();

            $category->openItemsCount = count($category->items);
        }

        return $response->withData(Category::collection($categories));
    }

    public function saveAgenda($id, Request $request, Response $response)
    {

        Log::debug("Save Agenda Controller");
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $organisation = Organisations::getById($id);
        Log::debug("Save Agenda for Organisation: ".$organisation->name);

        $organisationAccess = UserOrganisations::getAccess(Auth::user()->id, $organisation->id);

        if ($organisationAccess->access == false || $organisationAccess->edit == false) {
            throw new HTTPException("You don't have permission to change the Agend of this Organisation", 403);
        }
        Log::debug("Access to ".$organisation->name." given");



        $data = json_decode($request->getContent());
        if ($data === null) {
            throw new \Exception("Content cant pars as Json");
        }

        foreach ($data as $categorie) {
            // Check if the Category has upddated
            $cat = Categories::query()->where("id", "=", $categorie->id)->first();
            if ($categorie->name != $cat->name) {
                throw new HTTPException("Can't update Category name with the saveAgenda function", 400);
            }
            if ($categorie->position != $cat->position) {
                throw new HTTPException("Cant't update Category position with the saveAgenda function.", 400);
            }
            if ($categorie->status != $cat->status) {
                throw new HTTPException("Can't update Category status with the saveAgenda function.", 400);
            }

            Log::debug("Check Categorie: ".$cat->name);

            // Check all Items
            $itemPosition = 0;
            foreach ($categorie->items as $item) {
                $it = Item::query()->where("id", "=", $item->id)->first();
                Log::debug("Check Item: ".$it->name);
                $changeArray = [];
                if ($it->position != $itemPosition) {
                    Log::debug("Update Position in Categorie");
                    $changeArray["position"] = ["old" => $it->position, "new" => $itemPosition];
                    $it->position = $itemPosition;
                }
                if ($it->category_id != $cat->id) {
                    // @todo check if the new Category is in the same organisation and active
                    $changeArray["category"] = ["old"=>$it->category_id, "new" => $cat->id];
                    $it->category_id = $cat->id;
                }
                if (count($changeArray) > 0) {
                    // @todo fire item Change Event
                    Log::info("Update Item ".$it->name);
                    $it->saveOrFail();
                    event(new ItemUpdated($it, $changeArray));
                } else {
                    Log::debug("Nothing to save");
                }

                $itemPosition++;
            }
        }
    }

    public function oldAgendaCall($id, Request $request, Response $response)
    {
        Log::warning("Call depricated function");
        $organisation = Organisations::getById($id);
        if ($organisation == null) {
            throw new HTTPException("Organisation not found", 404);
        }

        if ($organisation->public == false) {
            if (!Auth::check()) {
                throw new NotLoggedInException();
            }

            $organisationAuth = UserOrganisations::getAccess(Auth::user()->id, $id);

            if ($organisationAuth == null || $organisationAuth->access == false || $organisationAuth->read == false) {
                throw new HTTPException("You don't have permission to see this Page", 403);
            }
        }

        $categories = Categories::getForOrganisation($organisation->id);

        foreach ($categories as $category) {
            $category->items = Item::query()
                ->where("category_id", "=", $category->id)
                ->where("status", "=", "active")
                ->orderBy("position", "ASC")
                ->get();
            $category->openItemsCount = count($category->items);
        }

        $res = [];
        $res["allItems"] = Category::collection($categories);
        $res["agenda"] = new Organisation($organisation);
        if (Auth::user() == null) {
            $res["access"] = new Access(UserOrganisations::guestAccess($organisation));
        } else {
            $res["access"] = new Access(UserOrganisations::getAccess(Auth::user()->id, $organisation->id));
        }

        return $response->withData($res);
    }
}
