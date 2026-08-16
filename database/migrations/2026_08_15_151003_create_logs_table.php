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
        Schema::create('logs', function (Blueprint $table) {
            $table->integer('id_log')->autoIncrement();
            $table->string('id_pengajuan', 15);
            $table->string('posisi');
            $table->string('jabatan');
            $table->text('catatan')->nullable();
            $table->string('catatanurgen', 200)->nullable();
            $table->dateTime('tanggal_posisi');
            $table->string('file1')->nullable();
            $table->string('file2')->nullable();
            $table->string('file_revisi')->nullable();
            $table->string('status')->nullable();
            
            $table->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuans')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
