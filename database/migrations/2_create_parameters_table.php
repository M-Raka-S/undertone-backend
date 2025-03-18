<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parameters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('category_id')->index();
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories');
        });

        Schema::create('category_instances', function (Blueprint $table) {
            $table->id();
            $table->string('summarisation');
            $table->unsignedBigInteger('category_id')->index();
            $table->unsignedBigInteger('project_id')->index();
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('project_id')->references('id')->on('projects');
        });

        Schema::create('instance_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->unsignedBigInteger('parameter_id')->index();
            $table->unsignedBigInteger('instance_id')->index();
            $table->timestamps();
            $table->foreign('parameter_id')->references('id')->on('parameters');
            $table->foreign('instance_id')->references('id')->on('category_instances');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameters');
        Schema::dropIfExists('category_instances');
        Schema::dropIfExists('instance_parameters');
        Schema::dropIfExists('parameter_inputs');
    }
};
