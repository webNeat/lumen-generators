<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectTagMigration extends Migration
{
    
    public function up()
    {
        Schema::create('project_tag', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned()->index();
            $table->integer('tag_id')->unsigned()->index();
            $table->foreign('project_id')
                ->references('id')
                ->on('projects');
            $table->foreign('tag_id')
                ->references('id')
                ->on('tags');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('project_tag');
    }
}
