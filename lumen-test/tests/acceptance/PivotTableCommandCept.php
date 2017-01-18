<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('generate a pivot table');
$I->runShellCommand('php artisan wn:pivot-table Tag Project --add=timestamps --file=pivot_table');
$I->seeInShellOutput('project_tag migration generated');
$I->seeFileFound('./database/migrations/pivot_table.php');
$I->openFile('./database/migrations/pivot_table.php');
$I->seeFileContentsEqual('<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectTagTable extends Migration
{

    public function up()
    {
        Schema::create(\'project_tag\', function(Blueprint $table) {
            $table->increments(\'id\');
            $table->integer(\'project_id\')->unsigned()->index();
            $table->integer(\'tag_id\')->unsigned()->index();
            $table->foreign(\'project_id\')
                ->references(\'id\')
                ->on(\'projects\');
            $table->foreign(\'tag_id\')
                ->references(\'id\')
                ->on(\'tags\');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop(\'project_tag\');
    }
}
');
$I->deleteFile('./database/migrations/pivot_table.php');
