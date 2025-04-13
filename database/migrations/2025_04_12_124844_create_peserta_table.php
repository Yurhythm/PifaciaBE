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
        Schema::create('peserta', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tiket_id');
            $table->string('nama');
            $table->string('email');
            $table->boolean('sudah_checkin')->default(false);
            $table->dateTime('daftar_pada');
            $table->timestamps();

            $table->foreign('tiket_id')->references('id')->on('tiket')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta');
    }
};
