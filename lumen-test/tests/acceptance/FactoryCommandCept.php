<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('generate model factories without fields');
$I->runShellCommand('php artisan wn:factory "App\Task"');
$I->seeInShellOutput('App\Task factory generated');
$I->openFile('./database/factories/ModelFactory.php');
$I->seeInThisFile('
$factory->define(App\Task::class, function ($faker) {
    return [
        // Fields here
    ];
});');
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

$I->wantTo('generate model factories with fields');
$I->runShellCommand('php artisan wn:factory "App\Task" --fields="title:sentence(3),description:paragraph(3),due:date,hidden:boolean"');
$I->seeInShellOutput('App\Task factory generated');
$I->openFile('./database/factories/ModelFactory.php');
$I->seeInThisFile(
"        'title' => \$faker->sentence(3)," . PHP_EOL .
"        'description' => \$faker->paragraph(3)," . PHP_EOL .
"        'due' => \$faker->date," . PHP_EOL .
"        'hidden' => \$faker->boolean,"
);
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
