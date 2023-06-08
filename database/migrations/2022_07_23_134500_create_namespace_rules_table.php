<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNamespaceRulesTable extends Migration
{
    public function up()
    {
        Schema::create('class_namespace_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('class_projects')->cascadeOnUpdate()->nullOnDelete();

            $table->string('namespace', 100); // App, \Maximum\*, \Modules\*
            $table->timestamps();
        });
    }
}
