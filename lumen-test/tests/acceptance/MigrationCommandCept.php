<?php 
$I = new AcceptanceTester($scenario);

$I->wantTo('generate a migration without schema');
$I->runShellCommand('php artisan wn:migration tasks --file=create_tasks');
$I->seeInShellOutput('tasks migration generated');
$I->seeFileFound('./database/migrations/create_tasks.php');
$I->openFile('./database/migrations/create_tasks.php');
$I->seeFileContentsEqual('<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    
    public function up()
    {
        Schema::create(\'tasks\', function(Blueprint $table) {
            $table->increments(\'id\');
            // Schema declaration
            // Constraints declaration
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

$I->wantTo('generate a migration with schema');
$I->runShellCommand('php artisan wn:migration tasks --file=create_tasks --schema="amount:decimal.5,2:after.\'size\':default.8 title:string:nullable"');
$I->seeInShellOutput('tasks migration generated');
$I->seeFileFound('./database/migrations/create_tasks.php');
$I->openFile('./database/migrations/create_tasks.php');
$I->seeFileContentsEqual('<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    
    public function up()
    {
        Schema::create(\'tasks\', function(Blueprint $table) {
            $table->increments(\'id\');
            $table->decimal(\'amount\', 5, 2)->after(\'size\')->default(8);
            $table->string(\'title\')->nullable();
            // Constraints declaration
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

$I->wantTo('generate a migration with schema and foreign keys');
$I->runShellCommand('php artisan wn:migration tasks --file=create_tasks --keys="category_type_id user_id:identifier:members:cascade" --schema="amount:decimal.5,2:after.\'size\':default.8 title:string:nullable"');
$I->seeInShellOutput('tasks migration generated');
$I->seeFileFound('./database/migrations/create_tasks.php');
$I->openFile('./database/migrations/create_tasks.php');
$I->seeInThisFile('$table->foreign(\'category_type_id\')
                ->references(\'id\')
                ->on(\'category_types\');');
$I->seeInThisFile('$table->foreign(\'user_id\')
                ->references(\'identifier\')
                ->on(\'members\')
                ->onDelete(\'cascade\');');
$I->deleteFile('./database/migrations/create_tasks.php');