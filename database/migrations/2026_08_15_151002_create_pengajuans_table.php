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
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->string('id_pengajuan', 15)->primary();
            $table->string('nomor_surat', 100);
            $table->string('perihal');
            $table->string('tujuan', 20);
            $table->string('jenis_surat');
            $table->text('ket')->nullable();
            $table->dateTime('tgl_upload');
            $table->integer('id_tahun');
            $table->string('pencairan', 5);
            $table->integer('lpj');
            $table->string('id_lembaga', 10);
            $table->integer('user_id'); // Match the id_user type (integer) in users table
            
            $table->foreign('user_id')->references('id_user')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
