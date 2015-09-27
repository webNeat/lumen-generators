<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskCategoriesMigration extends Migration
{
    
    public function up()
    {
        Schema::create('task_categories', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->text('descr')->nullable();
            $table->integer('project_id');
            $table->timestamp('due');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('task_categories');
    }
}
