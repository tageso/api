<?php

namespace App\Http\Controllers;

use App\Events\CategoryUpdated;
use App\Exceptions\HTTPException;
use App\Exceptions\NotLoggedInException;
use App\Http\Resources\Category;
use App\Models\Categories;
use App\Models\Item;
use App\Models\Organisations;
use App\Models\UserOrganisations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller as BaseController;
use TaGeSo\APIResponse\Response;

class CategoryController extends BaseController
{
    public function listCategories($id, Response $response)
    {
        $organisation = Organisations::getById($id);
        if ($organisation == null) {
            throw new HTTPException("Organisation not found", 404);
        }

        if ($organisation->public == false) {
            if (!Auth::check()) {
                throw new NotLoggedInException();
            }

            $organisationAuth = UserOrganisations::query()
                ->where("user_id", "=", Auth::user()->id)
                ->where("organisation_id", "=", $id)
                ->first();

            if ($organisationAuth == null || $organisationAuth->access == false || $organisationAuth->read == false) {
                throw new HTTPException("You don't have permission to see this Page", 403);
            }
        }

        $categories = Categories::getForOrganisation($organisation->id);

        foreach ($categories as $category) {
            $category->openItemsCount = count(
                Item::query()
                    ->where("category_id", "=", $category->id)
                    ->where("status", "=", "active")
                    ->get()
            );
        }


         $response->setPagination($categories->currentPage(), $categories->lastPage(), $categories->perPage());

        return $response->withData(Category::collection($categories));
    }

    public function updateCategoriesDeprecated($id, Request $request, Response $response)
    {
        Log::debug("Update Category");
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $access = UserOrganisations::getAccess(Auth::user()->id, $id);
        if (!$access->admin) {
            throw new HTTPException("Only admins can change the Categories", 403);
        }

        $json = \GuzzleHttp\json_decode($request->getContent(), true);

        $position = 0;
        foreach ($json["categories"] as $categoryData) {
            $category = Categories::query()->where("id", "=", $categoryData["id"])->first();
            $category->position = $position;
            $category->saveOrFail();
            $position++;
        }
    }

    public function createCategory($id, Request $request, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $access = UserOrganisations::getAccess(Auth::user()->id, $id);
        if (!$access->admin) {
            throw new HTTPException("Only admins can create a Categories", 403);
        }

        $category = new Categories();
        $category->organisation_id= $id;
        $category->user_id = Auth::user()->id;
        $category->name = $request->input("name");
        $category->status = "active";

        $category->calculateNexFreePosition();
        $category->saveOrFail();

        return $response->withData(new Category($category));
    }

    public function deleteCategory($id, $category_id, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $access = UserOrganisations::getAccess(Auth::user()->id, $id);
        if (!$access->admin) {
            throw new HTTPException("Only admins can create a Categories", 403);
        }

        $category = Categories::query()->where("id", "=", $category_id)->first();
        if ($category->organisation_id != $id) {
            throw new HTTPException("Category is not in the Organisation", 400);
        }

        $changeArray = ["status" => ["old" => $category->status, "new" => "deleted"]];
        $category->status = "deleted";
        $category->saveOrFail();

        Event::fire(new CategoryUpdated($category, $changeArray));

        return $response->withData(new Category($category));
    }
}
