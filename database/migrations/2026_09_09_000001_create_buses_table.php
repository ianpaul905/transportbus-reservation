<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
            $table->increments('id');
            $table->string('immatriculation', 20)->unique();
            $table->unsignedInteger('nombre_places');
            $table->string('classe', 20)->nullable();
            $table->enum('statut', ['actif', 'en_maintenance', 'inactif'])->default('actif');
            $table->unsignedInteger('agence_id');
            $table->foreign('agence_id')->references('id')->on('agences');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus');
    }
};
