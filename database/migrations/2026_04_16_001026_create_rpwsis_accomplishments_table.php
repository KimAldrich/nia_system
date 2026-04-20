<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('rpwsis_accomplishments', function (Blueprint $table) {
        $table->id();

        $table->string('region')->nullable();
        $table->string('batch')->nullable();
        $table->string('allocation')->nullable();
        $table->string('nis')->nullable();
        $table->string('activity')->nullable();
        $table->text('remarks')->nullable();
        $table->decimal('amount', 15, 2)->nullable();

        // Implementation (c1–c12)
        for ($i = 1; $i <= 12; $i++) {
            $table->string("c$i")->nullable();
        }

        // Metrics
        $table->string('phy')->nullable();
        $table->string('fin')->nullable();
        $table->string('exp')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpwsis_accomplishments');
    }
};
