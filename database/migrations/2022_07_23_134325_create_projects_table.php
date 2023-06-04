<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    public function up()
    {
        Schema::create('class_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index()->unique();
            $table->string('path', 250);
            $table->timestamps();
        });
    }
}
