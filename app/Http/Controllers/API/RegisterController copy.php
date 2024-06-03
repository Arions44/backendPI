<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class RegisterController extends BaseController {
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
    */

    // public function register(Request $request): JsonResponse {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required',
    //         'email' => 'required|email|unique:users',
    //         'password' => 'required',
    //         'c_password' => 'required|same:password',
    //     ]);

    //     if($validator->fails()){
    //         return $this->sendError('Validation Error.', $validator->errors());
    //     }

    //     $input = $request->all();
    //     $input['password'] = bcrypt($input['password']);

    //     // Creamos el código para validar el email
    //     $input['code'] = mt_rand(1,999999);

    //     // Creamos el usuario en la base de datos
    //     $user = User::create($input);

    //     //Obtenemos el último usuario insertado
    //     $lastUser = User::latest('id')->first();

    //     // Y cogemos su id
    //     $input['id'] = $lastUser->id;

    //     // Enviamos el email de confirmación
    //     Mail::send('confirmation_code', $input, function($message) use ($input) {
    //         $message->from('info@tarjetafanky.com.es');
    //         $message->to($input['email'], $input['id'], $input['name'])->subject('Confirmación email de registro');
    //     });

    //     $success['name'] =  $user->name;
    //     $success['email'] =  $user->email;
    //     $success['id'] =  $user->id;

    //     return $this->sendResponse($success, 'User register successfully.');
    // }
    /**
      * Login api
      *
      * @return \Illuminate\Http\Response
    */

    public function login(Request $request): JsonResponse {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();

            if(!$user->email_verified_at) {
                return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
            }
            else {
                $user->num_logins = $user->num_logins + 1;
                $user->save();

                // Creating a token without scopes...
                $success['token'] =  $user->createToken('Token')->accessToken;

                $success['id'] =  $user->id;
                $success['name'] =  $user->name;
                $success['type'] =  $user->type;
                $success['photo'] = $user->photo?? null;
                $success['photo_confirmed_at'] =  $user->photo_confirmed_at?? null;
                $success['email_verified_at'] =  $user->email_verified_at;
                $success['num_fanky'] =  $user->num_fanky;
                $success['card_pay'] =  $user->card_pay;
                $success['first_time'] =  $user->first_time;
                // $success['num_logins'] =  $user->num_logins;
                $success['deleted'] =  $user->deleted;
                $success['reset_pass_code'] = $user->reset_pass_code?? null;

                return $this->sendResponse($success, 'User login successfully.');
            }
        }
        else{
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function logout(){
        $token = Auth::user()->token();
        $token->revoke();

        $success['id'] =  Auth::user()->id;

        return $this->sendResponse($success, 'User logout successfully.');
    }

    // La siguiente función devuelve los datos de un usuario a través
    //   de su id y el token
    public function renew(Request $request): JsonResponse {
        $user = User::find($request->id);

        if($user){
            $user = Auth::user();

            $user->num_logins = $user->num_logins + 1;
            $user->save();


            $success['id'] =  $user->id;
            $success['token'] =  $user->createToken('Token')->accessToken;
            $success['name'] =  $user->name;
            $success['type'] =  $user->type;
            $success['photo'] = $user->photo?? null;
            $success['photo_confirmed_at'] =  $user->photo_confirmed_at?? null;
            $success['email_verified_at'] =  $user->email_verified_at;
            $success['num_fanky'] =  $user->num_fanky;
            $success['card_pay'] =  $user->card_pay;
            $success['first_time'] =  $user->first_time;
            // $success['num_logins'] =  $user->num_logins;
            $success['deleted'] =  $user->deleted;

            return $this->sendResponse($success, 'User renew successfully.');
        }
        else{
            return $this->sendError('Not exist.', ['error'=>'User not exist']);
        }
    }

    public function confirm(Request $request)
    {
        $user = User::find($request->id);

        if($user){
            $user->code = null;
            $user->email_verified_at = Now();

            ///////////////////////////////////////////////////////
            //  OJO!!!! LAS SIGUIENTES LÍNEAS HAY QUE QUITARLAS  //
            // Creamos el número de la tarjeta fanky
            $user->num_fanky = self::cardPayed();

            // Ponemos que ha sido pagada
            $user->card_pay = 1;
            ///////////////////////////////////////////////////////




            $user->save();

            return view('email_confirmed',compact(user));
        }
        // return $this->sendResponse([], 'User confirmed successfully.');
        // return $this->sendResponse(new UserResource($user), 'User updated successfully.');
    }

    // public function cardPayed(): JsonResponse {
    //     $user = User::find($id);

    //     if($user && (Auth::user()->id == $id)){
    //         // Creamos el número de la tarjeta fanky (no debe existir en la BD)
    //         do {
    //             $numCard = mt_rand(10000,99999);
    //             $find = User::where('deleted', 0)
    //                 ->where('num_fanky', $numCard)->get();
    //         }while(!$find);

    //         $user->num_fanky = $numCard;

    //         // Ponemos que ha sido pagada
    //         $user->card_pay = 1;

    //         // Y guardamos
    //         $user->save();

    //         $success['id'] =  $user->id;

    //         return $this->sendResponse($success, 'Card payed successfully.');
    //     }
    //     else{
    //         return $this->sendError('Not exist.', ['error'=>'User not exist']);
    //     }
    // }

    // ESTA FUNCIÓN ES PROVISIONAL, HAY QUE QUITARLA Y DESCOMENTAR LA ANTERIOR
    public function cardPayed() {
        // Creamos el número de la tarjeta fanky (no debe existir en la BD)
        do {
            $numCard = mt_rand(10000,99999);
            $find = User::where('deleted', 0)
                ->where('num_fanky', $numCard)->get();
        }while(!$find);

        // if(Auth::user()->id == $id){
            return $numCard;
        // } else {
            // return 'Usuario no válido';
        // }
    }

    public function addPhoto(Request $request) {
        $validator = Validator::make($request->all(), [
            'photo' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Buscamos el usuario
        $user = User::find($request->id);

        // Guardar la Foto si se ha enviado
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $namePhoto = $user->id . '.' . $photo->getClientOriginalExtension();
            // $pathPhoto = $photo->storeAs('resources/images/photos/', $namePhoto);
            $pathPhoto = public_path('photos/') . $namePhoto;

            copy($photo->getRealPath(),$pathPhoto);

            $user->photo = $pathPhoto;
            // $pathPhoto = $photo->storeAs('resources/images/photos/', $namePhoto);
            // $user->photo = 'photos/' . $namePhoto; // Almacenar la ruta relativa a la imagen
            $user->photo_confirmed_at = Now();
        }

        $user->save();

        $success['id'] =  $user->id;

        return $this->sendResponse($success, 'Photo register successfully.');
    }

    public function deleteUser(Request $request){
        $user = User::find($request->id);

        if($user && (Auth::user()->id == $request->id)) {
            // Eliminamos el usuario
            $user->delete();

            $success['id'] =  $user->id;

            // Enviamos un email al usuario
            Mail::send('delete_ok', $user->toArray(), function($message) use ($user) {
                $message->from('info@tarjetafanky.com.es');
                $message->to($user->email, $user->id, $user->name)->subject('Email de confirmación de baja');
            });

            return $this->sendResponse($success, 'User deleted successfully.');
        }
        else {
            return 'Usuario no válido';
        }
    }


    /**
     * addUsersExcel api
     *
     * Añade a la tabla usuarios los registros que recibe desde el excel
     *
     * @return \Illuminate\Http\Response
    */
    public function addUsersExcel(Request $request)
    {
        // Comprobamos que se ha mandado el fichero
        $validator = Validator::make($request->all(), [
            'fileUsers' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Vamos a recuperar cual fue el último usuario que se creó
        //   para poder saber cuántos hemos insertado con el Excel
        $lastUser = User::latest('id')->first();
        $time = $lastUser->created_at;

        // Obtenemos el fichero del formulario
        $fileUsers = $request->file('fileUsers');

        // Y llamamos a UsersImport para que añada los usuarios del Excel
        Excel::Import(new UsersImport, $fileUsers);

        $cuantosUsuarios = User::where('created_at','>', $time)->get();

        $success['numUsuarios'] =  $cuantosUsuarios->count();

        return $this->sendResponse($success, $cuantosUsuarios->count() . ' Users register successfully.');
    }

    // public function registerExcel(Request $request): JsonResponse
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required',
    //         'email' => 'required|email|unique:users',
    //     ]);

    //     if($validator->fails()){
    //         return $this->sendError('Validation Error.', $validator->errors());
    //     }

    //     $input = $request->all();

    //     // Creamos el código para cambiar la contraseña
    //     $resetPasswordCode = mt_rand(100000,999999);
    //     $input['reset_pass_code'] = $resetPasswordCode;

    //     $input['password'] = bcrypt($resetPasswordCode);

    //     // Creamos el número de la tarjeta fanky
    //     $input['num_fanky'] = self::cardPayed();

    //     // Ponemos que ha sido pagada
    //     $input['card_pay'] = 1;

    //     // Creamos el código para validar el email
    //     $input['code'] = mt_rand(1,999999);

    //     $input['email_verified_at'] = Now();

    //     // Creamos el usuario en la base de datos
    //     $user = User::create($input);

    //     //Obtenemos el último usuario insertado
    //     $lastUser = User::latest('id')->first();

    //     // Y cogemos su id
    //     $input['id'] = $lastUser->id;

    //     // Enviamos el email de confirmación
    //     Mail::send('register_ok', $input, function($message) use ($input) {
    //         $message->from('info@tarjetafanky.com.es');
    //         $message->to($input['email'], $input['id'], $input['name'], $input['reset_pass_code'])->subject('Confirmación email de registro');
    //     });

    //     $success['name'] =  $user->name;
    //     $success['email'] =  $user->email;
    //     $success['id'] =  $user->id;
    //     $success['reset_pass_code'] =  $user->reset_pass_code;

    //     return $this->sendResponse($success, 'User register successfully.');
    // }
}
