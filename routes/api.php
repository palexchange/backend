<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
$controllers = require base_path('vendor/composer/autoload_classmap.php');
$controllers = array_keys($controllers);
$controllers = array_filter($controllers, function ($controller) {
    return (strpos($controller, 'Controllers') !== false) && (strpos($controller, 'Controllers\\Controller') === false)  && strlen($controller) > 0 && strpos($controller, 'Laravel') === false && strpos($controller, 'Auth') === false && (strpos($controller, 'Controller')   !== false);
});
// dd($controllers);
array_map(function ($controller) {
    // $controllerName = str_replace('App\Http\Controllers\\', '', $controller);
    // $models = substr($controllerName, 0, -10);
    // $models = preg_split('/(?=[A-Z])/', $models, -1, PREG_SPLIT_NO_EMPTY);
    // $models = array_map(function ($model) {
    //     return lcfirst($model);
    // }, $models);
    // $params = join(".", $models);
    // dd($controller);
    if (method_exists($controller, 'routeName'))
        Route::apiResource($controller::routeName(), $controller);
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