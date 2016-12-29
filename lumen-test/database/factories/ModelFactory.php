<?php

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

$factory->define(App\User::class, function ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => str_random(10),
        'remember_token' => str_random(10),
    ];
});
/**
 * Factory definition for model App\Task.
 */
$factory->define(App\Task::class, function ($faker) {
    return [
		// Fields here
    ];
});

/**
 * Factory definition for model App\TaskCategory.
 */
$factory->define(App\TaskCategory::class, function ($faker) {
    return [
        'name' => $faker->word,
        'descr' => $faker->paragraph,
        'due' => $faker->date,
        'project_id' => $faker->key,
        'user_id' => $faker->key,
    ];
});
