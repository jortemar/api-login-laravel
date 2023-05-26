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
    // definimos los campos que queremos añadir a la tabla users
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    // permitimos que, llegado el caso, podamos revertir la creación de los nuevos campos
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address', 'phone']);
        });
    }
};
