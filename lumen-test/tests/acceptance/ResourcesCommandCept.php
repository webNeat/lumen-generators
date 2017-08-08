<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('Generate RESTful resources from a file');
$I->writeToFile('database/database.sqlite', '');

$I->runShellCommand('php artisan wn:resources tests/_data/ResourcesTest.yml');

// Checking the model
$I->seeInShellOutput('Author model generated');
$I->seeInShellOutput('Book model generated');
$I->seeInShellOutput('Library model generated');
$I->seeFileFound('./app/Author.php');
$I->seeFileFound('./app/Book.php');
$I->seeFileFound('./app/Library.php');
$I->deleteFile('./app/Author.php');
$I->deleteFile('./app/Book.php');
$I->deleteFile('./app/Library.php');

// Checking the migration
$I->seeInShellOutput('authors migration generated');
$I->seeInShellOutput('books migration generated');
$I->seeInShellOutput('libraries migration generated');
// Can't check for specific file names, so we'll just strip the directory
$I->cleanDir('database/migrations');
$I->writeToFile('database/migrations/.gitkeep', '');

// Checking the RESTActions trait
$I->seeFileFound('./app/Http/Controllers/RESTActions.php');
$I->deleteFile('./app/Http/Controllers/RESTActions.php');

// Checking the controller
$I->seeInShellOutput('AuthorsController generated');
$I->seeInShellOutput('LibrariesController generated');
$I->seeInShellOutput('BooksController generated');
$I->seeFileFound('./app/Http/Controllers/AuthorsController.php');
$I->seeFileFound('./app/Http/Controllers/LibrariesController.php');
$I->seeFileFound('./app/Http/Controllers/BooksController.php');

$I->deleteFile('./app/Http/Controllers/AuthorsController.php');
$I->deleteFile('./app/Http/Controllers/LibrariesController.php');
$I->deleteFile('./app/Http/Controllers/BooksController.php');


// Checking routes
$I->openFile('./app/Http/routes.php');
$I->seeInThisFile('
$app->get(\'author\', \'AuthorsController@all\');
$app->get(\'author/{id}\', \'AuthorsController@get\');
$app->post(\'author\', \'AuthorsController@add\');
$app->put(\'author/{id}\', \'AuthorsController@put\');
$app->delete(\'author/{id}\', \'AuthorsController@remove\');');

$I->seeInThisFile('
$app->get(\'book\', \'BooksController@all\');
$app->get(\'book/{id}\', \'BooksController@get\');
$app->post(\'book\', \'BooksController@add\');
$app->put(\'book/{id}\', \'BooksController@put\');
$app->delete(\'book/{id}\', \'BooksController@remove\');');

$I->seeInThisFile('
$app->get(\'library\', \'LibrariesController@all\');
$app->get(\'library/{id}\', \'LibrariesController@get\');
$app->post(\'library\', \'LibrariesController@add\');
$app->put(\'library/{id}\', \'LibrariesController@put\');
$app->delete(\'library/{id}\', \'LibrariesController@remove\');');
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

// Checking model factory
// $I->openFile('./database/factories/ModelFactory.php');
// $I->seeInThisFile(
// "/**
//  * Factory definition for model App\TaskCategory.
//  */
// \$factory->define(App\TaskCategory::class, function (\$faker) {
//     return [
//      'name' => \$faker->word,
//      'descr' => \$faker->paragraph,
//      'due' => \$faker->date,
//     ];
// });");
$I->writeToFile('./database/factories/ModelFactory.php', "<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

\$factory->define(App\User::class, function (\$faker) {
    return [
        'name' => \$faker->name,
        'email' => \$faker->email,
        'password' => str_random(10),
        'remember_token' => str_random(10),
    ];
});
");

$I->deleteFile('database/database.sqlite');


// Checking database seeder
// $I->openFile('./database/seeds/TaskCategoriesTableSeeder.php');
// $I->seeInThisFile('
// use Illuminate\Database\Seeder;

// class TaskCategoriesTableSeeder extends Seeder
// {
//     public function run()
//     {
//         factory(App\TaskCategory::class, 10)->create();
//     }
// }');
// $I->deleteFile('./database/seeds/TaskCategoriesTableSeeder.php');
