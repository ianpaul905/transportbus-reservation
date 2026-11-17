<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trajets', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
            $table->increments('id');
            $table->string('ville_depart', 100);
            $table->string('ville_arrivee', 100);
            $table->date('date_depart');
            $table->time('heure_depart');
            $table->decimal('prix', 10, 2);
            $table->unsignedInteger('bus_id');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('bus_id')->references('id')->on('bus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trajets');
    }
};
