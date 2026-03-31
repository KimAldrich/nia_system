<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('administrative_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('document_type'); // 'memorandum' or 'minutes'
            $table->string('file_path');
            $table->string('original_name');
            $table->unsignedBigInteger('user_id'); // Tracks WHO uploaded it
            $table->string('team_role'); // Tracks WHICH TEAM uploaded it
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administrative_documents');
    }
};
