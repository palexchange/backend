<?php

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

$controllers = require base_path('vendor/composer/autoload_classmap.php');
$controllers = array_keys($controllers);
$controllers = array_filter($controllers, function ($controller) {
    return (strpos($controller, 'Controllers') !== false) && strlen($controller) > 0 && strpos($controller, 'Base') == false && strpos($controller, 'Auth') == false && strpos($controller, 'App') >= 0;
});

array_map(function ($controller) {
    if (method_exists($controller, 'routeName'))
        Route::apiResource($controller::routeName(), $controller);
}, $controllers);




// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
