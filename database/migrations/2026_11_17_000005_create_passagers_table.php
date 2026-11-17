<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passagers', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
            $table->increments('id');
            $table->unsignedInteger('reservation_id');
            $table->string('nom_complet', 100);
            $table->enum('type_piece', ['cni', 'carte_scolaire', 'recepisse', 'nip']);
            $table->string('numero_piece', 60);
            $table->string('fichier', 255)->nullable();
            $table->string('fichier_nom', 255)->nullable();
            $table->string('fichier_type', 100)->nullable();
            $table->unsignedInteger('fichier_taille')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passagers');
    }
};
