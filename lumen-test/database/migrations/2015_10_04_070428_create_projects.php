<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsMigration extends Migration
{
    
    public function up()
    {
        Schema::create('projects', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('descr')->nullable();
            // Constraints declaration
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('projects');
    }
}
