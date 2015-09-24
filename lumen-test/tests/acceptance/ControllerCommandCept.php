<?php 
$I = new AcceptanceTester($scenario);

$I->wantTo('generate a RESTful controller with short model name');
$I->runShellCommand('php artisan wn:controller Test');
$I->seeInShellOutput('TestsController generated');
$I->seeFileFound('./app/Http/Controllers/TestsController.php');
$I->openFile('./app/Http/Controllers/TestsController.php');
$I->seeFileContentsEqual('<?php namespace App\Http\Controllers;


class TestsController extends Controller {

	const MODEL = "App\\Test";

	use RESTActions;

}
');
$I->deleteFile('./app/Http/Controllers/TestsController.php');

$I->wantTo('generate a RESTful controller with full model name');
$I->runShellCommand('php artisan wn:controller "App\Models\Category"');
$I->seeInShellOutput('CategoriesController generated');
$I->seeFileFound('./app/Http/Controllers/CategoriesController.php');
$I->openFile('./app/Http/Controllers/CategoriesController.php');
$I->seeFileContentsEqual('<?php namespace App\Http\Controllers;


class CategoriesController extends Controller {

	const MODEL = "App\\Models\\Category";

	use RESTActions;

}
');
$I->deleteFile('./app/Http/Controllers/CategoriesController.php');
