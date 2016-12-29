<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('generate a seeder with default options');
$I->runShellCommand('php artisan wn:seeder "App\Task"');
$I->seeInShellOutput('TasksTableSeeder generated');
$I->openFile('./database/seeds/TasksTableSeeder.php');
$I->seeInThisFile('
use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{
    public function run()
    {
        factory(App\Task::class, 10)->create();
    }
}');
$I->deleteFile('./database/seeds/TasksTableSeeder.php');

$I->wantTo('generate a seeder with custom options');
$I->runShellCommand('php artisan wn:seeder "App\Category" --count=25');
$I->seeInShellOutput('CategoriesTableSeeder generated');
$I->openFile('./database/seeds/CategoriesTableSeeder.php');
$I->seeInThisFile('
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        factory(App\Category::class, 25)->create();
    }
}');
$I->deleteFile('./database/seeds/CategoriesTableSeeder.php');
