<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueuedClassesTable extends Migration
{
    public function up()
    {
        // could be moved to `classes` table later
        Schema::create('class_queued_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnUpdate()->nullOnDelete();
            $table->string('fqn', 250)->index();
            $table->boolean('file_exists')->nullable();
            {
                $table->foreignId('imported_by')->nullable()->constrained('classes')->cascadeOnUpdate()->nullOnDelete();
                $table->string('relation')->index()->nullable(); // enum (null, implemented, extended)

                // as replacement for these fields
                $table->foreignId('extended_by')->nullable()->constrained('classes')->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('implemented_by')->nullable()->constrained('classes')->cascadeOnUpdate()->nullOnDelete();
            }
            $table->timestamps();

            $table->unique(['project_id', 'fqn']);
        });
    }
}
