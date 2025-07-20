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
        Schema::create('jenis_kas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jenis_kas', 100)->unique();
            $table->decimal('target_lunas', 12, 2)->nullable()->comment('Target pelunasan penuh, misal 2500000');
            $table->enum('tipe_iuran', ['wajib', 'sukarela'])->default('sukarela');
            $table->decimal('nominal_wajib', 12, 2)->nullable();
            $table->enum('default_tipe', ['pemasukan', 'pengeluaran']);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_kas');
    }
};
