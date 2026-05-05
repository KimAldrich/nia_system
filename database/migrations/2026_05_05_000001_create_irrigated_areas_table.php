<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('irrigated_areas', function (Blueprint $table) {
            $table->id();
            $table->string('source_path', 1024);
            $table->string('source_file')->nullable();
            $table->string('source_hash', 64)->unique();
            $table->unsignedInteger('feature_index')->default(0);
            $table->decimal('min_lat', 10, 6)->index();
            $table->decimal('max_lat', 10, 6)->index();
            $table->decimal('min_lng', 10, 6)->index();
            $table->decimal('max_lng', 10, 6)->index();
            $table->longText('properties_json')->nullable();
            $table->longText('geometry_json');
            $table->timestamps();

            $table->index(['min_lng', 'max_lng', 'min_lat', 'max_lat'], 'irrigated_areas_bbox_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigated_areas');
    }
};
