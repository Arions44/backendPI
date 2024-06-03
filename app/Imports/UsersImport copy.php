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

// HeadingRowFormatter::default('none');

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
        $email = $row['correo'];

        $user = User::where("email", "=", $email)->first();
        if(!$user) {
            // Creamos el código para cambiar la contraseña
            $resetPasswordCode = mt_rand(100000,999999);
            // Define cómo se debe mapear cada fila del archivo Excel
            // return new User([
            $newUser = new User([
                'name' => $row['nombre'],
                'email' => $row['correo'],
                // 'password' => bcrypt(123456),
                // 'reset_pass_code' => mt_rand(100000,999999),
                'password' => bcrypt($resetPasswordCode),
                'reset_pass_code' => $resetPasswordCode,
                // Creamos el número de la tarjeta fanky
                'num_fanky' => self::cardPayed(),
                // Ponemos que ha sido pagada
                'card_pay' => 1,
                // Creamos el código para validar el email
                'code' => mt_rand(1,999999),
                'email_verified_at' => Now()
            ]);

            //Obtenemos el último usuario insertado
            // $lastUser = User::latest('id')->first();

            // Enviamos el email de confirmación
            Mail::send('register_ok', $newUser->toArray(), function($message) use ($newUser) {
                $message->from('info@tarjetafanky.com.es');
                $message->to($newUser->email, $newUser->id, $newUser->name, $newUser->reset_pass_code)->subject('Confirmación email de registro');
            });

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
