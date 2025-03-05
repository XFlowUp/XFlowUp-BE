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
        Schema::create('environment_variables_list_mapping_project', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("environment_list_id");
            $table->bigInteger("project_id");

            $table->foreign('environment_list_id')->references('id')->on('environment_variables_list_of_project')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environment_variables_list_mapping_project');
    }
};
