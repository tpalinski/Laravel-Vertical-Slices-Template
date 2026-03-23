<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->string('token_id')->primary();
            $table->string('access_token_id');
            $table->dateTime('exipres_at');
            $table->boolean('revoked')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('refresh_tokens');
    }
};
