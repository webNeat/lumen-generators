<?php 
$I = new AcceptanceTester($scenario);

$I->wantTo('generate a migration without schema');
$I->runShellCommand('php artisan wn:migration tasks');
$I->seeInShellOutput('tasks migration generated');
$I->seeFileFound('./database/migrations/create_tasks.php');
$I->openFile('./database/migrations/create_tasks.php');
$I->seeFileContentsEqual('<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksMigration extends Migration
{
    
    public function up()
    {
        Schema::create(\'tasks\', function(Blueprint $table) {
            $table->increments(\'id\');
			// Schema declaration
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop(\'tasks\');
    }
}
');
$I->deleteFile('./database/migrations/create_tasks.php');

$I->wantTo('generate a migration without schema');
$I->runShellCommand('php artisan wn:migration tasks --schema="amount:decimal.5,2:after.\'size\':default.8 title:string:nullable"');
$I->seeInShellOutput('tasks migration generated');
$I->seeFileFound('./database/migrations/create_tasks.php');
$I->openFile('./database/migrations/create_tasks.php');
$I->seeFileContentsEqual('<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksMigration extends Migration
{
    
    public function up()
    {
        Schema::create(\'tasks\', function(Blueprint $table) {
            $table->increments(\'id\');
			$table->decimal(\'amount\', 5, 2)->after(\'size\')->default(8);
			$table->string(\'title\')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop(\'tasks\');
    }
}
');
$I->deleteFile('./database/migrations/create_tasks.php');