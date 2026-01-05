<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar los tipos de espacios de español a inglés
        DB::table('spaces')
            ->where('type', 'sala-de-reuniones')
            ->update(['type' => 'meeting-room']);

        DB::table('spaces')
            ->where('type', 'auditorio')
            ->update(['type' => 'auditorium']);

        DB::table('spaces')
            ->where('type', 'sala-de-conferencias')
            ->update(['type' => 'conference-hall']);

        DB::table('spaces')
            ->where('type', 'aula')
            ->update(['type' => 'classroom']);

        DB::table('spaces')
            ->where('type', 'oficina-privada')
            ->update(['type' => 'classroom']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los cambios de inglés a español
        DB::table('spaces')
            ->where('type', 'meeting-room')
            ->update(['type' => 'sala-de-reuniones']);

        DB::table('spaces')
            ->where('type', 'auditorium')
            ->update(['type' => 'auditorio']);

        DB::table('spaces')
            ->where('type', 'conference-hall')
            ->update(['type' => 'sala-de-conferencias']);

        DB::table('spaces')
            ->where('type', 'classroom')
            ->update(['type' => 'aula']);
    }
};
