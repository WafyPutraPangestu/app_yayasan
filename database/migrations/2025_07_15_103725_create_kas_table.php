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
        Schema::create('kas', function (Blueprint $table) {
            $table->id();

            $table->enum('tipe', ['pemasukan', 'pengeluaran']);

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('jenis_kas_id')->nullable();

            $table->decimal('jumlah', 12, 2);
            $table->text('keterangan')->nullable();
            $table->date('tanggal');

            $table->unsignedTinyInteger('bulan_iuran')->nullable(); // 1 - 12
            $table->unsignedSmallInteger('tahun_iuran')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('jenis_kas_id')->references('id')->on('jenis_kas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas');
    }
};
