<?php

use App\Http\Controllers\AuthController;
use App\Imports\PartyImport;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$controllers = require base_path('vendor/composer/autoload_classmap.php');
$controllers = array_keys($controllers);
$controllers = array_filter($controllers, function ($controller) {
    return (strpos($controller, 'Controllers') !== false) && strlen($controller) > 0 && strpos($controller, 'Base') == false && strpos($controller, 'Auth') == false && strpos($controller, 'App') >= 0;
});
$controllers = require base_path('vendor/composer/autoload_classmap.php');
$controllers = array_keys($controllers);
$controllers = array_filter($controllers, function ($controller) {
    return (strpos($controller, 'Controllers') !== false) && (strpos($controller, 'Controllers\\Controller') === false)  && strlen($controller) > 0 && strpos($controller, 'Laravel') === false && strpos($controller, 'Auth') === false && (strpos($controller, 'Controller')   !== false);
});

array_map(function ($controller) {

    if (method_exists($controller, 'routeName')) {
        // Artisan::call('make:resource ' . ucfirst(Str::camel($controller::routeName())) . 'Resource ');
        Route::apiResource($controller::routeName(), $controller);
    }
}, $controllers);

Route::group([
    'prefix' => 'auth',
    'middleware' => 'api',
    'as' => 'auth.'
], function () {
    $auth_routes = ['login', 'me', 'logout', 'refresh'];
    foreach ($auth_routes as $auth_route) {
        Route::post("/" . $auth_route, [AuthController::class, $auth_route])->name($auth_route);
    }
    Route::get("user", [AuthController::class, 'user']);
});

Route::get('/import', function () {
    // Excel::import(new PartyImport(1), base_path() . '/excel/k_parties.xlsx');
    // Excel::import(new PartyImport(2), base_path() . '/excel/ah_parties.xlsx');
    return response()->json(['data' => 'success', 'All good!']);
});
