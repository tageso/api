<?php

namespace App\Http\Controllers;

use App\Events\ItemUpdated;
use App\Exceptions\HTTPException;
use App\Exceptions\NotLoggedInException;
use App\Http\Resources\Access;
use App\Http\Resources\Category;
use App\Http\Resources\Comment;
use App\Http\Resources\Organisation;
use App\Http\Resources\Protocol;
use App\Http\Resources\ProtocolItem;
use App\Http\Resources\UserProfile;
use App\Models\Categories;
use App\Models\Comments;
use App\Models\Item;
use App\Models\Organisations;
use App\Models\ProtocolItems;
use App\Models\User;
use App\Models\UserOrganisations;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Laravel\Lumen\Routing\Controller as BaseController;
use TaGeSo\APIResponse\Response;

class ItemController extends BaseController
{

    public function listItems($organisation_id, Response $response)
    {
        $organisation = Organisations::getById($organisation_id);

        if ($organisation == null) {
            throw new HTTPException("ORganisation not found", 404);
        }

        //Throw Exception if organisation is private and user is not logged in
        if (!$organisation->public && !Auth::check()) {
            throw new NotLoggedInException();
        }


        //Create Access Object
        $access = null;
        if (Auth::check()) {
            $access = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);
            if (!$access->read) {
                throw new HTTPException("You don't have access to this Organisation", 403);
            }
        } else {
            $access = UserOrganisations::guestAccess($organisation, 0);
            if (!$access->read) {
                throw new NotLoggedInException();
            }
        }


        $categories = Categories::query()
            ->where("organisation_id", "=", $organisation_id)
            ->orderBy("position", "ASC")
            ->get(["id"]);

        $categorieIds = [];
        foreach ($categories as $category) {
            $categorieIds[] = $category->id;
        }


        $items = Item::query()
            ->where("status", "=", "active")
            ->whereIn("category_id", $categorieIds)
            ->orderBy("category_id", "ASC")
            ->orderBy("position", "ASC")
            ->paginate(20);

        $response->setPagination(
            $items->currentPage(),
            $items->lastPage(),
            $items->perPage()
        );

        return $response->withData(\App\Http\Resources\Item::collection(($items)));
    }

    public function createItem($organisation_id, Request $request, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $access = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);

        if (!$access->edit) {
            throw new HTTPException("You don't have Permission to change this Item", 403);
        }

        $item = new Item();
        $item->name = $request->input("name");
        $item->user_id = Auth::user()->id;
        $item->category_id = $request->input("category");
        $item->status = "active";
        $item->description = $request->input("description", "");
        $item->calculateNexFreePosition();
        $item->saveOrFail();

        return $response->withData(new \App\Http\Resources\Item($item));
    }

    public function updateItem($organisation_id, $item_id, Request $request, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $access = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);

        if (!$access->edit) {
            throw new HTTPException("You don't have Permission to change this Item", 403);
        }

        $item = Item::query()->where("id", "=", $item_id)->first();
        $category = Categories::query()->where("id", "=", $item->category_id)->first();

        if ($category->organisation_id != $organisation_id) {
            throw new HTTPException("Item Category not in the Organisation", 400);
        }

        $changeArray = [];
        if ($request->input("name", false)) {
            $changeArray["name"] = ["old" => $item->name, "new" => $request->input("name")];
            $item->name =$request->input("name");
        }

        if ($request->input("description", false)) {
            $changeArray["description"] = ["old" => $item->description, "new" => $request->input("description")];
            $item->description =$request->input("description");
        }

        $item->saveOrFail();
        Event::fire(new ItemUpdated($item, $changeArray));

        return $response->withData(new \App\Http\Resources\Item($item));
    }

    public function closeItem($organisation_id, $item_id, Request $request, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $access = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);

        if (!$access->edit) {
            throw new HTTPException("You don't have Permission to change this Item", 403);
        }

        $item = Item::query()->where("id", "=", $item_id)->first();
        $category = Categories::query()->where("id", "=", $item->category_id)->first();

        if ($category->organisation_id != $organisation_id) {
            throw new HTTPException("Item Category not in the Organisation", 400);
        }

        $changeArray = [];
        $changeArray["status"] = ["new" => "closed", "old" => $item->status];
        $item->status = "closed";

        $item->saveOrFail();
        Event::fire(new ItemUpdated($item, $changeArray));

        return $response->withData(new \App\Http\Resources\Item($item));
    }

    public function detailItemDeprecated($organisation_id, $item_id, Response $response)
    {
        $organisation = Organisations::getById($organisation_id);

        if (!$organisation->public) {
            if (!Auth::check()) {
                throw new NotLoggedInException();
            }
            $access = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);
            if (!$access->read) {
                throw new HTTPException("You don't have permission to see this item", 403);
            }
        }


        $item = Item::query()->where("id", "=", $item_id)->first();
        $category = Categories::query()->where("id", "=", $item->category_id)->first();
        $agenda = Organisations::getById($category->organisation_id);
        $author = \App\Models\UserProfile::query()->where("user_id", "=", $item->user_id)->first();
        $access = UserOrganisations::guestAccess($organisation, null);

        if (Auth::check()) {
            $access = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);
        }

        $protocolItems = ProtocolItems::query()->where("item_id", "=", $item_id)->get();
        $comments = Comments::query()->where("item_id", "=", $item_id)->get();

        $history = [];
        foreach ($protocolItems as $i) {
            $history[] = $i;
        }
        foreach ($comments as $i) {
            $history[] = $i;
        }

        uasort($history, [$this, "sortHistory"]);

        $historyResponse = [];
        foreach ($history as $i) {
            if (get_class($i)=="App\Models\ProtocolItems") {
                $historyResponse[] = new ProtocolItem($i);
            } else {
                $historyResponse[] = new Comment($i);
            }
        }

        $data = [];
        $data["item"] = new \App\Http\Resources\Item($item);
        $data["category"] = new Category($category);
        $data["agenda"] = new Organisation($agenda);
        $data["autor"] = new UserProfile($author);
        $data["access"] = new Access($access);
        $data["history"] = $historyResponse;

        return $response->withData($data);
    }

    private function sortHistory($a, $b)
    {
        if ($a->getDate() == $b->getDate()) {
            return 0;
        }
        return ($a->getDate() < $b->getDate()) ? -1 : 1;
    }
}
