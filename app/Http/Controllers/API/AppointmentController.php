<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Appointment;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use DateTime;
// use App\Http\Resources\AppointmentResource;

class AppointmentController extends BaseController
{
    // appointmentsFuture devuelve las citas de las próximas 2 semanas

    public function appointmentsFuture() //: JsonResponse
    {
        $date_to = date("Y-m-d G:i:s",strtotime(now()."+ 2 week"));

        // $appointments = Appointment::where('deleted', '<>', '1')
        //     ->whereBetween('time', [now(), $date_to])
        //     ->orderBy('time', 'DESC')->get();

        $appointments = DB::table('users')
            ->join('appointments', 'appointments.student_id', '=', 'users.id')
            ->select('users.name', 'users.school_year', 'appointments.student_id',
            'appointments.tutor_id', 'appointments.time', 'appointments.description')
            ->where('appointments.deleted', '<>', '1')
            ->whereBetween('appointments.time', [now(), $date_to])
            ->orderBy('appointments.time', 'ASC')
            ->get();

        $success['appointments'] = $appointments;

        return $this->sendResponse($success, 'Appointment retrieved successfully.');
    }

    public function getCalendarCounselor()
    {
        // Obtenemos las fechas de inicio y de fin (dentro de 1 mes)
        $date_init = strtotime(date("Y-m-d G:i:s",strtotime(now()."+ 1 day")));
        $date_from = $date_init;
        $date_to = date("Y-m-d G:i:s",strtotime(now()."+ 1 month"));

        // Obtenemos las citas de la BD y las convertimos a Array
        $appointmentsMonthFree = DB::table('appointments')
        //     ->select('time')
            ->where('deleted', '<>', '1')
            ->whereBetween('time', [now(), $date_to])
            ->orderBy('time', 'ASC')
            ->pluck('time')->toArray();
            //->get()->toArray();

        // Luego creamos el array con todas las posibles citas que no estén cogidas
        $dates_array = array();
        // $dates_array = [];
        $horas = array('09:00:00', '10:00:00', '11:30:00', '12:30:00', '13:30:00');
        $diasLectivos = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');
        $count = 0;

        // Recorremos todos los días desde hoy hasta dentro de un mes
        while ($date_from <= strtotime($date_to)) {
            // Comprobamos que el día no sea fin de semana
            $diaSemana = date('l', $date_from);
            if (in_array($diaSemana, $diasLectivos, true)) {
                // Obtenemos primero solo la fecha (sin la hora)
                $fecha = date("Y-m-d", $date_from);

                // Y luego recorremos el array de horas para generar todas las combinaciones
                foreach($horas as $hora) {
                    // Combina la fecha y la hora para formar una cadena completa
                    $fechaCompleta = date("Y-m-d H:i:s",strtotime($fecha . $hora));

                    if (!in_array($fechaCompleta, $appointmentsMonthFree, true)) {
                        // $dates_array[$count] = $fechaCompleta;
                        $dates_array[] = $fechaCompleta;
                    }

                    $count++;
                }

            }
            $date_from = strtotime("+1 day", $date_from);
        }

        // return $dates_array;

        $success['date_from'] = date("Y-m-d G:i:s", $date_init);
        $success['date_to'] = $date_to;
        $success['dates'] = $dates_array;

        return $this->sendResponse($success, 'Dates retrieved successfully.');
    }

    public function getAppointmentsByTeacher(Request $request)
    {
        $input = $request->all();
        $tutorId = $input['tutor_id'];

        $date_to = date("Y-m-d G:i:s",strtotime(now()."+ 1 month"));

        $appointmentsMonth = DB::table('users')
            ->join('appointments', 'appointments.student_id', '=', 'users.id')
            ->select('users.name', 'users.school_year', 'appointments.student_id',
            'appointments.tutor_id', 'appointments.time', 'appointments.description')
            ->where('appointments.deleted', '<>', '1')
            ->where('appointments.tutor_id', '=', $tutorId)
            ->whereBetween('appointments.time', [now(), $date_to])
            ->orderBy('appointments.time', 'ASC')
            ->get();

            $success['appointmentsMonth'] = $appointmentsMonth;

            return $this->sendResponse($success, 'Appointments retrieved successfully.');
    }

    // newAppointment genera una nueva cita
    public function newAppointment(Request $request) //: JsonResponse
    {
		$input = $request->all();
        $student_id = $input['student_id'];
        $tutor_id = $input['tutor_id'];
        $time = $input['time'];
        $description = $input['description'];

        // Comprobamos que no haya cita ese día a esa hora
        $hayCita = Appointment::where('deleted', '<>', '1')
            ->where('time', '=', $time)->get();

        if(!$hayCita->count()) {
			// Creamos la nueva cita
            $appointment = new Appointment;
            $appointment->student_id = $student_id;
            $appointment->tutor_id = $tutor_id;
            $appointment->time = $time;
            $appointment->description = $description;
            $appointment->save();

            $success['student_id'] = $student_id;
            $success['tutor_id'] = $tutor_id;
            $success['time'] = $time;
            $success['description'] = $description;

            return $this->sendResponse($success, 'Appointment created successfully.');
        }
        else {
            $success['Error'] = 'Error';
            return $this->sendResponse($success, 'Time Appointment not free.');
        }
    }
}
