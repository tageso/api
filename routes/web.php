<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/* Public Routes */
$router->post("/v2/account/login", ["uses" => 'AccountController@login']);
$router->post("/v2/account/register", ["uses" => 'AccountController@register']);
$router->get("/v2/account/validation/{validation_id}/{token}", ["uses" => 'AccountController@validateMail']);

/* Application Routes */
$router->group(['prefix' => 'v2'], function () use ($router) {
    $router->group(['prefix' => 'account'], function () use ($router) {
        $router->get("/me", ["uses" => 'AccountController@getAccountMe']);
    });
    $router->group(['prefix' => 'profile'], function () use ($router) {
        $router->get("/me", ["uses" => 'AccountController@getAccountMe']);
        $router->get("/{user_id}", ["uses" => 'ProfileController@getProfile']);
        $router->patch("/{user_id}", ["uses" => 'ProfileController@updateProfile']);
    });
    $router->group(['prefix' => 'organisations'], function () use ($router) {
        $router->get("/", ["uses" => 'OrganisationController@listOrganisationForUser']);
        $router->post("/", ["uses" => 'OrganisationController@createOrganisation']);
        $router->get("/{id}", ["uses" => 'OrganisationController@getOrganisation']);
        $router->patch("/{id}", ["uses" => 'OrganisationController@updateOrganisation']);
        $router->delete("/{id}", ["uses" => 'OrganisationController@deleteOrganisation']);
        $router->group(['prefix' => '/{id}/categories'], function () use ($router) {
            $router->get("/", ["uses" => 'CategoryController@listCategories']);
            $router->post("/", ["uses" => 'CategoryController@createCategory']);
            $router->patch("/deprecated", ["uses" => 'CategoryController@updateCategoriesDeprecated']);
            $router->delete("/{category_id}", ["uses" => 'CategoryController@deleteCategory']);
        });
        $router->group(['prefix' => '/{id}/agenda'], function () use ($router) {
            $router->get("/", ["uses" => 'AgendaController@getAgenda']);
            $router->put("/", ["uses" => 'AgendaController@saveAgenda']);
            $router->get("/deprecated", ["uses" => 'AgendaController@oldAgendaCall']);
        });
        $router->group(['prefix' => '/{organisation_id}/item'], function () use ($router) {
            $router->get("/", ["uses" => 'ItemController@listItems']);
            $router->post("/", ["uses" => 'ItemController@createItem']);
            $router->patch("/{item_id}", ["uses" => "ItemController@updateItem"]);
            $router->delete("/{item_id}", ["uses" => "ItemController@closeItem"]);
            $router->get("/{item_id}/deprecated", ["uses" => 'ItemController@detailItemDeprecated']);
        });
        $router->group(['prefix' => '/{organisation_id}/access'], function () use ($router) {
            $router->get("/", ["uses" => 'AccessController@listAccess']);
            $router->get("/me", ["uses" => 'AccessController@getAccessDetailsMe']);
            $router->get("/{user_id}", ["uses" => 'AccessController@getAccessDetails']);
            $router->patch("/{user_id}", ["uses" => 'AccessController@editAccess']);
        });
        $router->group(['prefix' => '/{organisation_id}/notification/deprecated'], function () use ($router) {
            $router->post("/", ["uses" => 'AccessController@notificationDeprecated']);
        });
        $router->group(['prefix' => '/{organisation_id}/protocol'], function () use ($router) {
            $router->get("/", ["uses" => 'ProtocolController@listProtocols']);
            $router->post("/", ["uses" => 'ProtocolController@createProtocol']);
            $router->group(['prefix' => '/open'], function () use ($router) {
                $router->delete("/deprecated", ["uses" => "ProtocolController@cancelProtocol"]);
                $router->post("/deprecated", ["uses" => "ProtocolController@saveProtocolItems"]);
                $router->post("/save/deprecated", ["uses" => "ProtocolController@saveProtocolItemsAndClose"]);
                $router->get("/deprecated", ["uses" => "AgendaController@getAgendProtocolDeprecated"]);
            });
            $router->get("/{protocol_id}/deprecated", ["uses" => 'ProtocolController@getProtocolDeprecated']);

        });
    });
});

$router->get('/info', function (\App\Lib\Metainfo $metainfo) use ($router) {
    $data = [];
    $data["lumen"] = $router->app->version();
    $data["commitHash"] = $metainfo->getCommitHash();
    return \GuzzleHttp\json_encode($data);
});
$router->get('', function () {
    return redirect('/index.html');
});
