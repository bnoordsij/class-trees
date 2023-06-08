<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassImportsTable extends Migration
{
    public function up()
    {
        Schema::create('class_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('class_classes')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('import_id')->constrained('class_classes')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('relation')->index()->nullable(); // enum (null, implemented, extended)
            $table->timestamps();

            $table->unique(['class_id', 'import_id']);
        });
    }
}
