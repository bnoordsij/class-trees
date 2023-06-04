<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassesTable extends Migration
{
    public function up()
    {
        Schema::create('class_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnUpdate()->nullOnDelete();
            $table->string('name', 100)->index(); // UserController
            $table->string('package', 100)->index(); // App or Spatie\Media
            $table->string('fqn', 250); // App\Http\Controllers\UserController
            $table->boolean('file_exists')->nullable();
            $table->boolean('is_processed')->default(false)->index();
            $table->string('type', 10)->index(); // enum [class, trait, interface]
            $table->boolean('is_final')->default(false);
            $table->boolean('is_abstract')->default(false);
            $table->timestamps();

            $table->unique(['project_id', 'fqn']);
        });
    }
}
