<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agences', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
            $table->increments('id');
            $table->string('nom', 100);
            $table->string('ville', 100);
            $table->string('telephone', 30);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};
