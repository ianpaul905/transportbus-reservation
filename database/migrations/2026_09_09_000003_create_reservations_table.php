<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
            $table->increments('id');
            $table->string('reference', 30)->unique();
            $table->string('client_nom', 100);
            $table->string('client_telephone', 30);
            $table->unsignedInteger('nombre_places');
            $table->enum('type_reservation', ['individuel', 'famille'])->default('individuel');
            $table->string('nom_famille', 100)->nullable();
            $table->enum('documents_statut', ['non_requis', 'en_attente', 'valide', 'refuse'])->default('non_requis');
            $table->enum('statut', ['en_attente', 'payee', 'utilisee', 'annulee'])->default('en_attente');
            $table->unsignedInteger('trajet_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('trajet_id')->references('id')->on('trajets');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
