<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Space;
use App\Models\Booking;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los espacios
        $spaces = Space::all();
        
        // Obtener o crear usuarios
        $users = [
            // 👤 ADMINISTRADOR
            [
                'name' => 'Admin Sistema',
                'email' => 'admin@spacebooking.com',
                'password' => bcrypt('admin123'),
                'is_admin' => true,
            ],
            // 👥 USUARIOS NORMALES
            [
                'name' => 'María García',
                'email' => 'maria.garcia@example.com',
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
            [
                'name' => 'Carlos Rodríguez',
                'email' => 'carlos.rodriguez@example.com',
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
            [
                'name' => 'Ana Martínez',
                'email' => 'ana.martinez@example.com',
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
            [
                'name' => 'Luis Fernández',
                'email' => 'luis.fernandez@example.com',
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
            [
                'name' => 'Carmen López',
                'email' => 'carmen.lopez@example.com',
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $createdUsers[] = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        // Crear reservas variadas
        $bookings = [
            // Reservas confirmadas (pasadas)
            [
                'user_id' => $createdUsers[0]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones A')->first()->id,
                'booking_date' => Carbon::now()->subDays(5)->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'attendees' => 8,
                'purpose' => 'Reunión de planificación trimestral',
                'status' => 'completed',
                'notes' => 'Todo salió perfecto, excelente sala',
            ],
            [
                'user_id' => $createdUsers[1]->id,
                'space_id' => $spaces->where('name', 'Auditorio Principal')->first()->id,
                'booking_date' => Carbon::now()->subDays(10)->toDateString(),
                'start_time' => '14:00:00',
                'end_time' => '18:00:00',
                'attendees' => 150,
                'purpose' => 'Conferencia anual de la empresa',
                'status' => 'completed',
                'notes' => 'Evento exitoso, audio y video impecables',
            ],
            [
                'user_id' => $createdUsers[2]->id,
                'space_id' => $spaces->where('name', 'Espacio Coworking')->first()->id,
                'booking_date' => Carbon::now()->subDays(3)->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'attendees' => 15,
                'purpose' => 'Hackathon interno',
                'status' => 'completed',
            ],
            
            // Reservas confirmadas (futuras)
            [
                'user_id' => $createdUsers[0]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones Ejecutiva')->first()->id,
                'booking_date' => Carbon::now()->addDays(2)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'attendees' => 6,
                'purpose' => 'Reunión con inversores',
                'status' => 'confirmed',
                'notes' => 'Solicitar servicio de café gourmet',
            ],
            [
                'user_id' => $createdUsers[1]->id,
                'space_id' => $spaces->where('name', 'Sala de Conferencias B')->first()->id,
                'booking_date' => Carbon::now()->addDays(5)->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
                'attendees' => 25,
                'purpose' => 'Workshop de metodologías ágiles',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[3]->id,
                'space_id' => $spaces->where('name', 'Sala de Capacitación Tech')->first()->id,
                'booking_date' => Carbon::now()->addDays(7)->toDateString(),
                'start_time' => '14:00:00',
                'end_time' => '18:00:00',
                'attendees' => 20,
                'purpose' => 'Curso de React y Angular',
                'status' => 'confirmed',
                'notes' => 'Necesitamos acceso a internet de alta velocidad',
            ],
            [
                'user_id' => $createdUsers[2]->id,
                'space_id' => $spaces->where('name', 'Teatro Corporativo')->first()->id,
                'booking_date' => Carbon::now()->addDays(10)->toDateString(),
                'start_time' => '16:00:00',
                'end_time' => '20:00:00',
                'attendees' => 120,
                'purpose' => 'Presentación de producto nuevo',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[4]->id,
                'space_id' => $spaces->where('name', 'Hub Creativo')->first()->id,
                'booking_date' => Carbon::now()->addDays(3)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '15:00:00',
                'attendees' => 12,
                'purpose' => 'Sesión de brainstorming para campaña',
                'status' => 'confirmed',
            ],
            
            // Reservas pendientes
            [
                'user_id' => $createdUsers[3]->id,
                'space_id' => $spaces->where('name', 'Sala de Juntas Premium')->first()->id,
                'booking_date' => Carbon::now()->addDays(15)->toDateString(),
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'attendees' => 10,
                'purpose' => 'Revisión de presupuesto anual',
                'status' => 'pending',
            ],
            [
                'user_id' => $createdUsers[4]->id,
                'space_id' => $spaces->where('name', 'Oficina Privada 101')->first()->id,
                'booking_date' => Carbon::now()->addDays(1)->toDateString(),
                'start_time' => '13:00:00',
                'end_time' => '17:00:00',
                'attendees' => 3,
                'purpose' => 'Reunión confidencial de recursos humanos',
                'status' => 'pending',
            ],
            
            // Reservas canceladas
            [
                'user_id' => $createdUsers[0]->id,
                'space_id' => $spaces->where('name', 'Mini Auditorio')->first()->id,
                'booking_date' => Carbon::now()->addDays(8)->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'attendees' => 60,
                'purpose' => 'Capacitación de seguridad',
                'status' => 'cancelled',
                'notes' => 'Cancelado por cambio de fecha del instructor',
            ],
            [
                'user_id' => $createdUsers[1]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones Express')->first()->id,
                'booking_date' => Carbon::now()->subDays(2)->toDateString(),
                'start_time' => '15:00:00',
                'end_time' => '16:00:00',
                'attendees' => 4,
                'purpose' => 'Entrevista de trabajo',
                'status' => 'cancelled',
                'notes' => 'Candidato no pudo asistir',
            ],

            // Más reservas para hoy
            [
                'user_id' => $createdUsers[2]->id,
                'space_id' => $spaces->where('name', 'Sala de Innovación')->first()->id,
                'booking_date' => Carbon::now()->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'attendees' => 8,
                'purpose' => 'Sprint planning',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[3]->id,
                'space_id' => $spaces->where('name', 'Espacio Coworking')->first()->id,
                'booking_date' => Carbon::now()->toDateString(),
                'start_time' => '14:00:00',
                'end_time' => '18:00:00',
                'attendees' => 20,
                'purpose' => 'Trabajo colaborativo del equipo de desarrollo',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[4]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones A')->first()->id,
                'booking_date' => Carbon::now()->toDateString(),
                'start_time' => '16:00:00',
                'end_time' => '17:30:00',
                'attendees' => 7,
                'purpose' => 'Revisión de proyecto',
                'status' => 'confirmed',
            ],
        ];

        // Agregar MUCHAS reservas para finales de enero 2026
        $lateJanuaryBookings = [
            // 20 de enero
            [
                'user_id' => $createdUsers[0]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones A')->first()->id,
                'booking_date' => '2026-01-20',
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'attendees' => 8,
                'purpose' => 'Reunión de coordinación',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[1]->id,
                'space_id' => $spaces->where('name', 'Sala de Conferencias B')->first()->id,
                'booking_date' => '2026-01-20',
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'attendees' => 15,
                'purpose' => 'Workshop de innovación',
                'status' => 'confirmed',
            ],
            
            // 21 de enero
            [
                'user_id' => $createdUsers[2]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones Ejecutiva')->first()->id,
                'booking_date' => '2026-01-21',
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'attendees' => 5,
                'purpose' => 'Revisión financiera Q1',
                'status' => 'pending',
            ],
            [
                'user_id' => $createdUsers[3]->id,
                'space_id' => $spaces->where('name', 'Hub Creativo')->first()->id,
                'booking_date' => '2026-01-21',
                'start_time' => '13:00:00',
                'end_time' => '17:00:00',
                'attendees' => 10,
                'purpose' => 'Sesión de diseño UX/UI',
                'status' => 'confirmed',
            ],
            
            // 22 de enero
            [
                'user_id' => $createdUsers[4]->id,
                'space_id' => $spaces->where('name', 'Sala de Capacitación Tech')->first()->id,
                'booking_date' => '2026-01-22',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
                'attendees' => 20,
                'purpose' => 'Capacitación en Azure Cloud',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[0]->id,
                'space_id' => $spaces->where('name', 'Teatro Corporativo')->first()->id,
                'booking_date' => '2026-01-22',
                'start_time' => '15:00:00',
                'end_time' => '18:00:00',
                'attendees' => 100,
                'purpose' => 'All Hands Meeting',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[1]->id,
                'space_id' => $spaces->where('name', 'Sala de Innovación')->first()->id,
                'booking_date' => '2026-01-22',
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'attendees' => 8,
                'purpose' => 'Daily standup extendido',
                'status' => 'pending',
            ],
            
            // 23 de enero
            [
                'user_id' => $createdUsers[2]->id,
                'space_id' => $spaces->where('name', 'Auditorio Principal')->first()->id,
                'booking_date' => '2026-01-23',
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'attendees' => 150,
                'purpose' => 'Presentación de resultados anuales',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[3]->id,
                'space_id' => $spaces->where('name', 'Sala de Juntas Premium')->first()->id,
                'booking_date' => '2026-01-23',
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'attendees' => 10,
                'purpose' => 'Junta directiva',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[4]->id,
                'space_id' => $spaces->where('name', 'Espacio Coworking')->first()->id,
                'booking_date' => '2026-01-23',
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
                'attendees' => 25,
                'purpose' => 'Día de innovación',
                'status' => 'confirmed',
            ],
            
            // 24 de enero
            [
                'user_id' => $createdUsers[0]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones A')->first()->id,
                'booking_date' => '2026-01-24',
                'start_time' => '09:00:00',
                'end_time' => '10:30:00',
                'attendees' => 6,
                'purpose' => 'Retrospectiva de sprint',
                'status' => 'pending',
            ],
            [
                'user_id' => $createdUsers[1]->id,
                'space_id' => $spaces->where('name', 'Sala de Conferencias B')->first()->id,
                'booking_date' => '2026-01-24',
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'attendees' => 12,
                'purpose' => 'Reunión de ventas',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[2]->id,
                'space_id' => $spaces->where('name', 'Mini Auditorio')->first()->id,
                'booking_date' => '2026-01-24',
                'start_time' => '14:00:00',
                'end_time' => '17:00:00',
                'attendees' => 50,
                'purpose' => 'Training de producto',
                'status' => 'confirmed',
            ],
            
            // 27 de enero - MUCHAS reservas
            [
                'user_id' => $createdUsers[3]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones Ejecutiva')->first()->id,
                'booking_date' => '2026-01-27',
                'start_time' => '08:00:00',
                'end_time' => '10:00:00',
                'attendees' => 5,
                'purpose' => 'Planeación estratégica',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[4]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones A')->first()->id,
                'booking_date' => '2026-01-27',
                'start_time' => '10:30:00',
                'end_time' => '12:00:00',
                'attendees' => 8,
                'purpose' => 'Reunión de equipo de marketing',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[0]->id,
                'space_id' => $spaces->where('name', 'Hub Creativo')->first()->id,
                'booking_date' => '2026-01-27',
                'start_time' => '13:00:00',
                'end_time' => '15:00:00',
                'attendees' => 12,
                'purpose' => 'Brainstorming campaña Q1',
                'status' => 'pending',
            ],
            [
                'user_id' => $createdUsers[1]->id,
                'space_id' => $spaces->where('name', 'Sala de Innovación')->first()->id,
                'booking_date' => '2026-01-27',
                'start_time' => '15:30:00',
                'end_time' => '17:00:00',
                'attendees' => 6,
                'purpose' => 'Demo de prototipos',
                'status' => 'confirmed',
            ],
            
            // 28 de enero - DÍA MÁS OCUPADO
            [
                'user_id' => $createdUsers[2]->id,
                'space_id' => $spaces->where('name', 'Auditorio Principal')->first()->id,
                'booking_date' => '2026-01-28',
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'attendees' => 150,
                'purpose' => 'Keynote: Tendencias 2026',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[3]->id,
                'space_id' => $spaces->where('name', 'Sala de Capacitación Tech')->first()->id,
                'booking_date' => '2026-01-28',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
                'attendees' => 20,
                'purpose' => 'Workshop de Machine Learning',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[4]->id,
                'space_id' => $spaces->where('name', 'Teatro Corporativo')->first()->id,
                'booking_date' => '2026-01-28',
                'start_time' => '11:30:00',
                'end_time' => '13:30:00',
                'attendees' => 120,
                'purpose' => 'Lanzamiento de producto',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[0]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones A')->first()->id,
                'booking_date' => '2026-01-28',
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'attendees' => 10,
                'purpose' => 'Reunión con clientes VIP',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[1]->id,
                'space_id' => $spaces->where('name', 'Sala de Conferencias B')->first()->id,
                'booking_date' => '2026-01-28',
                'start_time' => '14:00:00',
                'end_time' => '17:00:00',
                'attendees' => 25,
                'purpose' => 'Capacitación en ventas',
                'status' => 'pending',
            ],
            [
                'user_id' => $createdUsers[2]->id,
                'space_id' => $spaces->where('name', 'Espacio Coworking')->first()->id,
                'booking_date' => '2026-01-28',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'attendees' => 30,
                'purpose' => 'Hackathon mensual',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[3]->id,
                'space_id' => $spaces->where('name', 'Sala de Juntas Premium')->first()->id,
                'booking_date' => '2026-01-28',
                'start_time' => '16:30:00',
                'end_time' => '18:00:00',
                'attendees' => 8,
                'purpose' => 'Revisión de OKRs',
                'status' => 'confirmed',
            ],
            
            // 29 de enero
            [
                'user_id' => $createdUsers[4]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones Ejecutiva')->first()->id,
                'booking_date' => '2026-01-29',
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'attendees' => 6,
                'purpose' => 'Board meeting',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[0]->id,
                'space_id' => $spaces->where('name', 'Hub Creativo')->first()->id,
                'booking_date' => '2026-01-29',
                'start_time' => '14:00:00',
                'end_time' => '17:00:00',
                'attendees' => 15,
                'purpose' => 'Sesión de creatividad',
                'status' => 'pending',
            ],
            [
                'user_id' => $createdUsers[1]->id,
                'space_id' => $spaces->where('name', 'Mini Auditorio')->first()->id,
                'booking_date' => '2026-01-29',
                'start_time' => '15:00:00',
                'end_time' => '17:00:00',
                'attendees' => 60,
                'purpose' => 'Presentación de propuestas',
                'status' => 'confirmed',
            ],
            
            // 30 de enero
            [
                'user_id' => $createdUsers[2]->id,
                'space_id' => $spaces->where('name', 'Sala de Innovación')->first()->id,
                'booking_date' => '2026-01-30',
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'attendees' => 8,
                'purpose' => 'Sprint planning',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[3]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones A')->first()->id,
                'booking_date' => '2026-01-30',
                'start_time' => '11:30:00',
                'end_time' => '13:00:00',
                'attendees' => 10,
                'purpose' => 'Cierre de mes',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[4]->id,
                'space_id' => $spaces->where('name', 'Teatro Corporativo')->first()->id,
                'booking_date' => '2026-01-30',
                'start_time' => '14:00:00',
                'end_time' => '18:00:00',
                'attendees' => 100,
                'purpose' => 'Town Hall Meeting',
                'status' => 'pending',
            ],
            
            // 31 de enero - ÚLTIMO DÍA DEL MES
            [
                'user_id' => $createdUsers[0]->id,
                'space_id' => $spaces->where('name', 'Sala de Reuniones Ejecutiva')->first()->id,
                'booking_date' => '2026-01-31',
                'start_time' => '08:00:00',
                'end_time' => '10:00:00',
                'attendees' => 5,
                'purpose' => 'Cierre financiero enero',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[1]->id,
                'space_id' => $spaces->where('name', 'Auditorio Principal')->first()->id,
                'booking_date' => '2026-01-31',
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'attendees' => 150,
                'purpose' => 'Celebración fin de mes',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[2]->id,
                'space_id' => $spaces->where('name', 'Sala de Conferencias B')->first()->id,
                'booking_date' => '2026-01-31',
                'start_time' => '13:00:00',
                'end_time' => '15:00:00',
                'attendees' => 20,
                'purpose' => 'Retrospectiva mensual',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[3]->id,
                'space_id' => $spaces->where('name', 'Espacio Coworking')->first()->id,
                'booking_date' => '2026-01-31',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'attendees' => 35,
                'purpose' => 'Día de integración de equipos',
                'status' => 'confirmed',
            ],
            [
                'user_id' => $createdUsers[4]->id,
                'space_id' => $spaces->where('name', 'Sala de Juntas Premium')->first()->id,
                'booking_date' => '2026-01-31',
                'start_time' => '15:30:00',
                'end_time' => '17:00:00',
                'attendees' => 10,
                'purpose' => 'Planificación febrero',
                'status' => 'pending',
            ],
        ];

        // Combinar todas las reservas
        $allBookings = array_merge($bookings, $lateJanuaryBookings);

        foreach ($allBookings as $booking) {
            Booking::create($booking);
        }
    }
}
