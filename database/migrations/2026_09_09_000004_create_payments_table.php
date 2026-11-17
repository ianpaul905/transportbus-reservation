<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
            $table->increments('id');
            $table->unsignedInteger('reservation_id');
            $table->enum('operateur', ['airtel_money', 'moov_money']);
            $table->string('telephone', 30);
            $table->decimal('montant', 10, 2);
            $table->string('reference', 40)->unique();
            $table->enum('statut', ['reussi', 'echoue'])->default('reussi');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
