<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('generate RESTful routes for a resource with default controller');
$I->runShellCommand('php artisan wn:route project-type');
$I->seeInShellOutput('project-type routes generated');
$I->openFile('./app/Http/routes.php');
$I->seeInThisFile("
\$app->get('project-type', 'ProjectTypesController@all');
\$app->get('project-type/{id}', 'ProjectTypesController@get');
\$app->post('project-type', 'ProjectTypesController@add');
\$app->put('project-type/{id}', 'ProjectTypesController@put');
\$app->delete('project-type/{id}', 'ProjectTypesController@remove');
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

$app->get("/", function () use ($app) {
    return $app->welcome();
});
');


$I->wantTo('generate RESTful routes for a resource with custom controller');
$I->runShellCommand('php artisan wn:route foo --controller=customController');
$I->seeInShellOutput('foo routes generated');
$I->openFile('./app/Http/routes.php');
$I->seeInThisFile("
\$app->get('foo', 'customController@all');
\$app->get('foo/{id}', 'customController@get');
\$app->post('foo', 'customController@add');
\$app->put('foo/{id}', 'customController@put');
\$app->delete('foo/{id}', 'customController@remove');
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

$app->get("/", function () use ($app) {
    return $app->welcome();
});
');