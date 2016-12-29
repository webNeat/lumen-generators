<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('generate a pivot table seeder');
$I->runShellCommand('php artisan wn:pivot-seeder tasks ShortTag');
$I->seeInShellOutput('ShortTagTaskTableSeeder generated');
$I->openFile('./database/seeds/ShortTagTaskTableSeeder.php');
$I->seeInThisFile("
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ShortTagTaskTableSeeder extends Seeder
{
    public function run()
    {
        \$faker = Faker::create();

        \$firstIds = DB::table('short_tags')->lists('id');
        \$secondIds = DB::table('tasks')->lists('id');

        for(\$i = 0; \$i < 10; \$i++) {
            DB::table('short_tag_task')->insert([
                'short_tag_id' => \$faker->randomElement(\$firstIds),
                'task_id' => \$faker->randomElement(\$secondIds)
            ]);
        }
    }
}");
$I->deleteFile('./database/seeds/ShortTagTaskTableSeeder.php');