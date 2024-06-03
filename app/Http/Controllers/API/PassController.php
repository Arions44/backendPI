<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PassController extends BaseController
{
    // codeModifyPass recibe el email del usuario y le envía un
    //    correo electrónico con el código de cambio de password
    public function codeModifyPass(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();

        $user = User::where('email', '=', $input['email'])->first();

        if(!is_null($user)) {
            // Creamos el código de recuperación de la contraseña
            $input['reset_pass_code'] = mt_rand(100000,999999);

            // Guardamos el valor en el registro del usuario
            $user->reset_pass_code = $input['reset_pass_code'];
            $user->save();
            $input['reset_pass_code']=$user->reset_pass_code;
            $input['name']=$user->name;
            // Enviamos el email con el código
            Mail::send('pass_reset_code', $input, function($message) use ($input) {
                $message->from('info@tarjetafanky.com.es');
                $message->to($input['email'], $input['reset_pass_code'], $input['name'])->subject('Cambio contraseña');
            });

            $success['data'] =  'Código enviado';
        } else {
            $success['data'] =  'El email no existe';
            return $this->sendResponse($success, 'Error at Send Password_Reset_Code.');
        }
        return $this->sendResponse($success, 'Send Password_Reset_Code successfully.');
    }

    // ModifyPass recibe el email del usuario, el código de cambio de password
    //   y la nueva Password y la modifica en la base de datos
    public function ModifyPass(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'reset_pass_code' => 'required',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();

        // Localizamos el usuario a través del email (campo único)
        $user = User::where('email', '=', $input['email'])->first();

        if(!is_null($user)) {
            // Comprobamos que el código de recuperación es correcto
            if($input['reset_pass_code'] == $user->reset_pass_code) {
                // Guardamos el nuevo valor de la contraseña
                $user->password = bcrypt($input['password']);
                // Y generamos el nuevo valor para el código de reseteo
                $user->reset_pass_code = mt_rand(100000,999999);
                // Modificamos el campo first_time a 0
                $user->first_time = 0;
                // Guardamos el valor en el registro del usuario
                $user->save();

                $success['data'] =  'Contraseña modificada';
            } else {
                $success['data'] =  'El código no coincide o el usuario no existe';
                return $this->sendResponse($success, 'Error at Change the Password.');
            }
        }
        return $this->sendResponse($success, 'Password Change successfully.');
    }
}
