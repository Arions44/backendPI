<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Row;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class UsersImport implements ToCollection, ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }

    public function model(array $row)
    {
		$school_year_tutor = Auth::user()->school_year;
        $email = $row['correo'];

        srand (time());
        do {

            $secretCode = rand(1000,9999);
            $existe = User::where("secret_code", "=", $secretCode)->first();
        } while ($existe);

        $user = User::where("email", "=", $email)->first();
        if(!$user) {
            // Creamos el código para cambiar la contraseña
            //    $resetPasswordCode = mt_rand(100000,999999);
			$resetPasswordCode = 'Ii3ps40928c$';
            // Define cómo se debe mapear cada fila del archivo Excel
            $newUser = new User([
                'name' => $row['nombre'],
                'email' => $row['correo'],
				'email_confirmed' => 1,
                'email_verified_at' => Now(),
				'actived' => 1,
                'password' => bcrypt($resetPasswordCode),
                // Creamos el código para validar el email
                'code' => mt_rand(1,999999),
				'secret_code' => $secretCode,
				'school_year' => $school_year_tutor,
            ]);

            // Enviamos el email de confirmación
            //    Mail::send('register_ok', $newUser->toArray(), function($message) use ($newUser) {
            //        $message->from('info@tarjetafanky.com.es');
            //        $message->to($newUser->email, $newUser->id, $newUser->name, $newUser->reset_pass_code)->subject('Confirmación email de registro');
            //    });

            return $newUser;
        }
    }

    public function cardPayed() {
        // Creamos el número de la tarjeta fanky (no debe existir en la BD)
        do {
            $numCard = mt_rand(10000,99999);
            $find = User::where('deleted', 0)
                ->where('num_fanky', $numCard)->get();
        }while(!$find);

        return $numCard;
    }
}
