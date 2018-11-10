<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('generate a RESTful controller with short model name');
$I->runShellCommand('php artisan wn:controller Test --no-routes');
$I->seeInShellOutput('TestsController generated');
$I->seeFileFound('./app/Http/Controllers/TestsController.php');
$I->openFile('./app/Http/Controllers/TestsController.php');
$I->seeFileContentsEqual('<?php namespace App\Http\Controllers;

class TestsController extends Controller {

    const MODEL = \'App\\Test\';

    use RESTActions;

}
');
$I->deleteFile('./app/Http/Controllers/TestsController.php');

$I->wantTo('generate a RESTful controller with full model name and routes');
$I->runShellCommand('php artisan wn:controller "App\Models\Category"');
$I->seeInShellOutput('CategoriesController generated');
$I->seeFileFound('./app/Http/Controllers/CategoriesController.php');
$I->openFile('./app/Http/Controllers/CategoriesController.php');
$I->seeFileContentsEqual('<?php namespace App\Http\Controllers;

class CategoriesController extends Controller {

    const MODEL = \'App\\Models\\Category\';

    use RESTActions;

}
');
$I->deleteFile('./app/Http/Controllers/CategoriesController.php');
$I->openFile('./app/Http/routes.php');
$I->seeInThisFile("
\$router->get('category', 'CategoriesController@all');
\$router->get('category/{id}', 'CategoriesController@get');
\$router->post('category', 'CategoriesController@add');
\$router->put('category/{id}', 'CategoriesController@put');
\$router->delete('category/{id}', 'CategoriesController@remove');
");
$I->writeToFile('./app/Http/routes.php', '<?php

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

$router->get("/", function () use ($router) {
    return \'Hello World\';
});
');