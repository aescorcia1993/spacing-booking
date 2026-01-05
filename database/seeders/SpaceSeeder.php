<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Space;

class SpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spaces = [
            [
                'name' => 'Sala de Reuniones A',
                'description' => 'Sala ejecutiva con capacidad para reuniones pequeñas. Equipada con proyector, pizarra digital y videoconferencia.',
                'type' => 'sala-de-reuniones',
                'capacity' => 10,
                'photos' => [
                    'https://images.unsplash.com/photo-1497366216548-37526070297c',
                    'https://images.unsplash.com/photo-1497366811353-6870744d04b2'
                ],
                'available_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '20:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '20:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '20:00'],
                    'thursday' => ['start' => '08:00', 'end' => '20:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Auditorio Principal',
                'description' => 'Auditorio moderno con capacidad para eventos grandes. Sistema de sonido profesional, iluminación LED y pantalla gigante.',
                'type' => 'auditorio',
                'capacity' => 200,
                'photos' => [
                    'https://images.unsplash.com/photo-1505373877841-8d25f7d46678',
                    'https://images.unsplash.com/photo-1475721027785-f74eccf877e2'
                ],
                'available_hours' => [
                    'monday' => ['start' => '09:00', 'end' => '22:00'],
                    'tuesday' => ['start' => '09:00', 'end' => '22:00'],
                    'wednesday' => ['start' => '09:00', 'end' => '22:00'],
                    'thursday' => ['start' => '09:00', 'end' => '22:00'],
                    'friday' => ['start' => '09:00', 'end' => '22:00'],
                    'saturday' => ['start' => '10:00', 'end' => '20:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Sala de Conferencias B',
                'description' => 'Espacio versátil ideal para talleres y capacitaciones. Incluye mesas móviles, sillas ergonómicas y equipo multimedia.',
                'type' => 'sala-de-conferencias',
                'capacity' => 30,
                'photos' => [
                    'https://images.unsplash.com/photo-1431540015161-0bf868a2d407',
                ],
                'available_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '19:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '19:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '19:00'],
                    'thursday' => ['start' => '08:00', 'end' => '19:00'],
                    'friday' => ['start' => '08:00', 'end' => '17:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Sala de Reuniones Ejecutiva',
                'description' => 'Sala premium para reuniones de alto nivel. Mobiliario de lujo, sistema de videoconferencia 4K y servicio de catering disponible.',
                'type' => 'sala-de-reuniones',
                'capacity' => 8,
                'photos' => [
                    'https://images.unsplash.com/photo-1497366754035-f200968a6e72',
                ],
                'available_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '20:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '20:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '20:00'],
                    'thursday' => ['start' => '08:00', 'end' => '20:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Espacio Coworking',
                'description' => 'Área abierta para trabajo colaborativo. Wi-Fi de alta velocidad, estaciones de trabajo compartidas y zona de café.',
                'type' => 'coworking',
                'capacity' => 50,
                'photos' => [
                    'https://images.unsplash.com/photo-1497366412874-3415097a27e7',
                ],
                'available_hours' => [
                    'monday' => ['start' => '07:00', 'end' => '22:00'],
                    'tuesday' => ['start' => '07:00', 'end' => '22:00'],
                    'wednesday' => ['start' => '07:00', 'end' => '22:00'],
                    'thursday' => ['start' => '07:00', 'end' => '22:00'],
                    'friday' => ['start' => '07:00', 'end' => '20:00'],
                    'saturday' => ['start' => '09:00', 'end' => '18:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Sala de Juntas Premium',
                'description' => 'Sala de juntas de lujo con vista panorámica. Ideal para reuniones ejecutivas con clientes importantes. Incluye catering y servicio de café gourmet.',
                'type' => 'sala-de-reuniones',
                'capacity' => 12,
                'photos' => [
                    'https://images.unsplash.com/photo-1497366754035-f200968a6e72',
                    'https://images.unsplash.com/photo-1497366811353-6870744d04b2',
                ],
                'available_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '20:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '20:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '20:00'],
                    'thursday' => ['start' => '08:00', 'end' => '20:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Teatro Corporativo',
                'description' => 'Espacio estilo teatro para presentaciones y capacitaciones. Butacas cómodas, sistema de audio envolvente y cabina de control.',
                'type' => 'auditorio',
                'capacity' => 150,
                'photos' => [
                    'https://images.unsplash.com/photo-1505373877841-8d25f7d46678',
                ],
                'available_hours' => [
                    'monday' => ['start' => '09:00', 'end' => '21:00'],
                    'tuesday' => ['start' => '09:00', 'end' => '21:00'],
                    'wednesday' => ['start' => '09:00', 'end' => '21:00'],
                    'thursday' => ['start' => '09:00', 'end' => '21:00'],
                    'friday' => ['start' => '09:00', 'end' => '21:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Sala de Innovación',
                'description' => 'Espacio creativo con pizarras blancas en todas las paredes, mobiliario flexible y zona de brainstorming. Perfecto para equipos ágiles.',
                'type' => 'sala-de-conferencias',
                'capacity' => 25,
                'photos' => [
                    'https://images.unsplash.com/photo-1431540015161-0bf868a2d407',
                ],
                'available_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '20:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '20:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '20:00'],
                    'thursday' => ['start' => '08:00', 'end' => '20:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Oficina Privada 101',
                'description' => 'Oficina privada completamente amueblada. Ideal para trabajo concentrado o reuniones confidenciales pequeñas.',
                'type' => 'aula',
                'capacity' => 4,
                'photos' => [
                    'https://images.unsplash.com/photo-1497366216548-37526070297c',
                ],
                'available_hours' => [
                    'monday' => ['start' => '07:00', 'end' => '22:00'],
                    'tuesday' => ['start' => '07:00', 'end' => '22:00'],
                    'wednesday' => ['start' => '07:00', 'end' => '22:00'],
                    'thursday' => ['start' => '07:00', 'end' => '22:00'],
                    'friday' => ['start' => '07:00', 'end' => '20:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Sala de Capacitación Tech',
                'description' => 'Sala equipada con 20 computadoras de última generación. Perfecta para workshops de programación, cursos y bootcamps.',
                'type' => 'sala-de-conferencias',
                'capacity' => 20,
                'photos' => [
                    'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4',
                ],
                'available_hours' => [
                    'monday' => ['start' => '09:00', 'end' => '21:00'],
                    'tuesday' => ['start' => '09:00', 'end' => '21:00'],
                    'wednesday' => ['start' => '09:00', 'end' => '21:00'],
                    'thursday' => ['start' => '09:00', 'end' => '21:00'],
                    'friday' => ['start' => '09:00', 'end' => '18:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Hub Creativo',
                'description' => 'Espacio multiuso con iluminación natural, plantas y decoración inspiradora. Ideal para equipos creativos y sesiones de diseño.',
                'type' => 'coworking',
                'capacity' => 30,
                'photos' => [
                    'https://images.unsplash.com/photo-1497366858526-0766cadbe8fa',
                ],
                'available_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '20:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '20:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '20:00'],
                    'thursday' => ['start' => '08:00', 'end' => '20:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Mini Auditorio',
                'description' => 'Auditorio compacto ideal para presentaciones de productos, conferencias de prensa y eventos corporativos medianos.',
                'type' => 'auditorio',
                'capacity' => 80,
                'photos' => [
                    'https://images.unsplash.com/photo-1475721027785-f74eccf877e2',
                ],
                'available_hours' => [
                    'monday' => ['start' => '09:00', 'end' => '22:00'],
                    'tuesday' => ['start' => '09:00', 'end' => '22:00'],
                    'wednesday' => ['start' => '09:00', 'end' => '22:00'],
                    'thursday' => ['start' => '09:00', 'end' => '22:00'],
                    'friday' => ['start' => '09:00', 'end' => '22:00'],
                    'saturday' => ['start' => '10:00', 'end' => '20:00'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Sala de Reuniones Express',
                'description' => 'Sala pequeña perfecta para reuniones rápidas y videollamadas. Equipada con pantalla y webcam profesional.',
                'type' => 'sala-de-reuniones',
                'capacity' => 6,
                'photos' => [
                    'https://images.unsplash.com/photo-1497366858526-0766cadbe8fa',
                ],
                'available_hours' => [
                    'monday' => ['start' => '07:00', 'end' => '21:00'],
                    'tuesday' => ['start' => '07:00', 'end' => '21:00'],
                    'wednesday' => ['start' => '07:00', 'end' => '21:00'],
                    'thursday' => ['start' => '07:00', 'end' => '21:00'],
                    'friday' => ['start' => '07:00', 'end' => '19:00'],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($spaces as $space) {
            Space::create($space);
        }
    }
}
