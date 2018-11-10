<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('generate RESTful routes for a resource with default controller');
$I->runShellCommand('php artisan wn:route project-type');
$I->seeInShellOutput('project-type routes generated');
$I->openFile('./app/Http/routes.php');
$I->seeInThisFile("
\$router->get('project-type', 'ProjectTypesController@all');
\$router->get('project-type/{id}', 'ProjectTypesController@get');
\$router->post('project-type', 'ProjectTypesController@add');
\$router->put('project-type/{id}', 'ProjectTypesController@put');
\$router->delete('project-type/{id}', 'ProjectTypesController@remove');
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


$I->wantTo('generate RESTful routes for a resource with custom controller');
$I->runShellCommand('php artisan wn:route foo --controller=customController');
$I->seeInShellOutput('foo routes generated');
$I->openFile('./app/Http/routes.php');
$I->seeInThisFile("
\$router->get('foo', 'customController@all');
\$router->get('foo/{id}', 'customController@get');
\$router->post('foo', 'customController@add');
\$router->put('foo/{id}', 'customController@put');
\$router->delete('foo/{id}', 'customController@remove');
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


$I->wantTo('run wn:routes in Lumen 5.3+');
if(!file_exists('./routes')) {
    mkdir('./routes');
}
$I->writeToFile('./routes/web.php', '<?php

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

$I->runShellCommand('php artisan wn:route foo --controller=customController');
$I->seeInShellOutput('foo routes generated');
$I->openFile('./routes/web.php');
$I->seeInThisFile("
\$router->get('foo', 'customController@all');
\$router->get('foo/{id}', 'customController@get');
\$router->post('foo', 'customController@add');
\$router->put('foo/{id}', 'customController@put');
\$router->delete('foo/{id}', 'customController@remove');
");
$I->deleteDir('./routes');
