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
use App\Http\Resources\UserResource;
use Dompdf\Dompdf;

use Illuminate\Support\Facades\Process;

class RegisterController extends BaseController {
    /**
      * firstTime api
      *
      * @return \Illuminate\Http\Response
    */

	public function firstTime(Request $request): JsonResponse {
		$user = User::where('secret_code', '=', $request->code)->first();

		if($user) {
			$success['first_time'] =  $user->first_time;
                    $success['num_image'] =  $user->num_image;
                    $success['actived'] =  $user->actived;

            return $this->sendResponse($success, 'User retrieved successfully.');
		} else {
			return $this->sendError('Unauthorized', ['error'=>"User not found"]);
		};
	}
    /**
      * LoginStudent api
      *
      * @return \Illuminate\Http\Response
    */

	//public function loginStudent(Request $request) {
	public function loginStudent(Request $request): JsonResponse {
		// Comprovamos si el código existe
		$user = User::where('secret_code', '=', $request->code)->first();
		if($user) {
			if($user->first_time == 1) {
				$user->num_image = $request->image;
				$user->first_time = 0;
				$user->save();
			}

			$user = User::where('secret_code', '=', $request->code)
						->where('num_image', '=', $request->image)
						->first();
			if($user) {
				// Se crea variable de request para enviársela a la función "login()"
				$usuario = new Request(array('email' => $user->email, 'password' => 'Ii3ps40928c$'));

				// Y llamamos a la función "login()"
				return $this->login($usuario);
			}
			else {
				return $this->sendError('Unauthorized.', ['error'=>"Code not found"]);
			}
		} else {
			return $this->sendError('Unauthorized.', ['error'=>"Code not found"]);
		}

		// $user = User::where('secret_code', '=', $request->code)
		// 		->where('num_image', '=', $request->image)
		// 		->first();

		// if($user) {
		// 	// Se crea variable de request para enviársela a la función login
    	// 	$usuario = new Request(array('email' => $user->email, 'password' => 'Ii3ps40928c$'));

		// 	return $this->login($usuario);
		// };
	}

    /**
      * Login api
      *
      * @return \Illuminate\Http\Response
    */

	//public function login(Request $request) {
	public function login(Request $request): JsonResponse {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();

            if(!$user->email_confirmed) {
                return $this->sendError('Unauthorized.', ['error'=>"Email don't confirmed"]);
            }
            else {
                if (!$user->actived) {
                    return $this->sendError('Unauthorized.', ['error'=>"User don't activated"]);
                }
                else {
                    // $user->num_logins = $user->num_logins + 1;
                    // $user->save();

                    // Creating a token without scopes...
                    $success['token'] =  $user->createToken('Token')->accessToken;

                    $success['id'] =  $user->id;
                    $success['name'] =  $user->name;
                    $success['type'] =  $user->type;
                    // $success['first_time'] =  $user->first_time;
                    $success['notification_limit'] =  $user->notification_limit;
                    $success['deleted'] =  $user->deleted;
                    // $success['reset_pass_code'] = $user->reset_pass_code?? null;

                    return $this->sendResponse($success, 'User login successfully.');
                }
            }
        }
        else{
            return $this->sendError('Unauthorized.', ['error'=>'Unauthorized']);
        }
    }

	public function register(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', ['error'=>'The email has already been taken']);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        // Creamos el código para validar el email
        $input['code'] = mt_rand(1,999999);

        // Creamos el usuario en la base de datos
        $user = User::create($input);

        //Obtenemos el último usuario insertado
        $lastUser = User::latest('id')->first();

        // Y cogemos su id
        $input['id'] = $lastUser->id;

        // Enviamos el email de confirmación
        Mail::send('confirmation_code', $input, function($message) use ($input) {
            $message->from('raul.reyes@allsites.es');
            $message->to($input['email'], $input['id'], $input['name'])->subject('Confirmación email de registro');
        });

        $success['name'] =  $user->name;
        $success['email'] =  $user->email;
        $success['id'] =  $user->id;

        return $this->sendResponse($success, 'User register successfully.');
    }

    public function logout(Request $request) {
        $user = Auth::user();
        $success['id'] = $user->id;

        $request->user()->token()->revoke();

        return $this->sendResponse($success, 'User logout Successfully.');

        //     $token = Auth::user()->token();
        //     $token->revoke();

        //     $success['id'] =  Auth::user()->id;

        //     return $this->sendResponse($success, 'User logout successfully.');
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
            // $success['first_time'] =  $user->first_time;
            $success['deleted'] =  $user->deleted;

            return $this->sendResponse($success, 'User renew successfully.');
        }
        else{
            return $this->sendError('Not exist.', ['error'=>'User not exist']);
        }
    }

    public function confirm(Request $request) {
        $user = User::find($request->id);

        if($user){
            $user->code = null;
            $user->email_verified_at = Now();
            $user->email_confirmed = 1;

            $user->save();

            // return view('email_confirmed',compact(user));
            return view('email_confirmed');
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

    // Mejorar en algún momento
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

    // Esta función permite a un usuario eliminarse a sí mismo (eliminar todos sus datos)
    public function deleteUserSelf(Request $request){
        $user = User::find($request->id);

        if($user && (Auth::user()->id == $request->id)) {
            // Eliminamos el usuario
            $user->delete();

            $success['id'] =  $user->id;

            // Enviamos un email al usuario
            Mail::send('delete_ok', $user->toArray(), function($message) use ($user) {
                $message->from('tarjetafanky@tarjetafanky.com.es');
                $message->to($user->email, $user->id, $user->name)->subject('Email de confirmación de baja');
            });

            return $this->sendResponse($success, 'User deleted successfully.');
        }
        else {
            return 'Usuario no válido';
        }
    }

    // Esta función permite a un Administrador hacer un SoftDelete de un usuario
    public function deleteUser(Request $request): JsonResponse {
        $user = User::find($request->id);

        if($user){

            $user->deleted = 1;
            $user->save();

            return $this->sendResponse([], 'User deleted successfully.');
        }
        // return $this->sendResponse(new UserResource($user), 'User updated successfully.');
    }

    public function activate(Request $request): JsonResponse {
        $user = User::find($request->id);

        if($user) {
            $success['id'] = $user->id;

            $user->actived = 1;
            $user->save();

            return $this->sendResponse($success, 'User activated successfully.');
        // return $this->sendResponse(new UserResource($user), 'User updated successfully.');
        }
        else{
            return $this->sendError('Not exist.', ['error'=>'User not exist']);
        }
    }

    public function deactivate(Request $request) {
        $user = User::find($request->id);

        if($user) {
            $success['id'] = $user->id;

            $user->actived = 0;
            $user->save();

            return $this->sendResponse($success, 'User deactivated successfully.');
        // return $this->sendResponse(new UserResource($user), 'User updated successfully.');
        }
        else{
            return $this->sendError('Not exist.', ['error'=>'User not exist']);
        }
    }

    public function users(): JsonResponse{
		$schoolYear = Auth::user()->school_year;
		$typeUser = Auth::user()->type;
        switch ($typeUser) {
            case 'a':
                $users = User::where('deleted', '<>', '1')
                    ->where('type', '=', 't')
                    ->orwhere('type', '=', 'o')
                    ->orderBy('school_year', 'DESC')
                    ->orderBy('name', 'ASC')->get();
                break;
            case 't':
                $users = User::where('deleted', '<>', '1')
                    ->where('school_year', '=', $schoolYear)
                    ->where('type', '=', 'u')
                    ->orderBy('school_year', 'DESC')
                    ->orderBy('name', 'ASC')->get();
                break;
            default:
                $users = User::where('deleted', '<>', '1')
                ->where('type', '=', 'u')
                ->orderBy('school_year', 'DESC')
                ->orderBy('name', 'ASC')->get();
        }

		// if($typeUser == 't') {
		// 	$users = User::where('deleted', '<>', '1')
		// 		->where('school_year', '=', $schoolYear)
		// 		->where('type', '=', 'u')
		// 		->orderBy('created_at', 'DESC')->get();
		// } else {
		// 	$users = User::where('deleted', '<>', '1')
		// 		->where('type', '=', 'u')
		// 		->orderBy('created_at', 'DESC')->get();
		// }

        return $this->sendResponse(UserResource::collection($users), 'Users retrieved successfully.');
    }

    public function show($id) {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        return $this->sendResponse(new UserResource($user), 'User retrieved successfully.');
        // return $this->sendResponse(UserResource::collection($user), 'Users retrieved successfully.');
    }

    public function idStudentByCode (Request $request) {
        $input = $request->all();
        $code = $input['code'];

        $user = User::where('secret_code','=', $code)->first();

        $success['id'] = $user->id;
        $success['actived'] =  $user->actived;

        return $this->sendResponse($success, 'User retrieved successfully.');
    }

    public function updateUser(Request $request): JsonResponse {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find($request->id);

        if($user){
            $user->name = $input['name'];

            $user->save();

            return $this->sendResponse([], 'User updated successfully.');
            // return $this->sendResponse(new UserResource($user), 'User updated successfully.');
        }
    }


    /**
     * addUsersExcel api
     *
     * Añade a la tabla usuarios los registros que recibe desde el excel
     *
     * @return \Illuminate\Http\Response
    */
    public function addUsersExcel(Request $request) {
        // Comprobamos que el usuario es tutor
        $user = Auth::user();

        $type = $user->type;
		// $email = $user->email;

        if($type=='t' || $type=='o') {
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
            // $time = $lastUser->created_at;

			// Recogemos el ID del último usuario añadido
			$lastId = $lastUser->id;

            // Obtenemos el fichero del formulario
            $fileUsers = $request->file('fileUsers');

            // Y llamamos a UsersImport para que añada los usuarios del Excel
            Excel::Import(new UsersImport, $fileUsers);

            $nuevosUsuarios = User::where('id','>', $lastId)->get();
			// $nuevosUsuariosArray = $nuevosUsuarios->toArray();
            $success['numUsuarios'] =  $nuevosUsuarios->count();

			// Creaos el HTML

            $html = '
                <!DOCTYPE html>
                <html>
                    <head>
                        <meta charset="utf-8">
                        <title>List of Emotions</title>
                        <style>
                            /* Estilos CSS para el PDF */
                            /* Puedes personalizar estos estilos según tus necesidades */
                            body {
                                text-align: center; /* Centra todo el contenido en el cuerpo del documento */
                            }
                            h1 {
                                text-align: center; /* Centra el primer encabezado (h1) */
                            }
                            table {
                                width: 100%; /* La tabla ocupa todo el ancho disponible */
                                border-collapse: collapse; /* Colapsa los bordes de la tabla */
                            }
                            th, td {
                                border: 1px solid #dddddd; /* Añade bordes a las celdas de la tabla */
                                text-align: left; /* Alinea el texto a la izquierda dentro de las celdas */
                                padding: 8px; /* Espaciado interno para las celdas */
                            }
                            tr:nth-child(even) {
                                background-color: #f2f2f2; /* Color de fondo para filas pares */
                            }
                        </style>
                    </head>

                    <body>
                        <h1>Claves para el curso ' . Auth::user()->school_year . '</h1>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre del alumno</th>
                                    <th>Clave</th>
                                </tr>
                            </thead>
                            <tbody>';
                                foreach ($nuevosUsuarios as $nuevoUsuario) {
                                    $html .= '
                                        <tr>
                                            <td>' . ($nuevoUsuario->name ?? 'Name not available') . '</td>
                                            <td>' . ($nuevoUsuario->secret_code ?? 'Date not available') . '</td>
                                        </tr>';
                                }

                                $html .= '
                            </tbody>
                        </table>
                    </body>
                </html>';



			// $html = '<h1 style="text-align:center;">Claves para el curso ' . Auth::user()->school_year . '</h1>';
        	// foreach ($nuevosUsuarios as $nuevoUsuario) {
			// 	$html .= '<table style="width:100%; margin-bottom:10px;">';
			// 	$html .= '<tr>';
			// 	$html .= '<td style="width:40%; text-align:center;"><p>' . $nuevoUsuario->name . '</p></td>';
			// 	$html .= '<td style="width:30%; text-align:center;"><p>' . $nuevoUsuario->secret_code . '</p></td>';
			// 	$html .= '</tr>';
			// 	$html .= '</table>';
			// }

            $dompdf = new Dompdf();

            // Definimos la codificación de caracteres como UTF-8
            $dompdf->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

            $dompdf->render();
            $pdfContent = $dompdf->output();
            Mail::send([], [], function ($message) use ($pdfContent, $user) {
                $message->to($user->email)
                    ->subject('Confirmación códigos de registro')
                    ->attachData($pdfContent, 'codesStudents.pdf', [
                        'mime' => 'application/pdf',
                    ]);
            });

			// Mail::send([], [], function($message) use ($nuevosUsuariosArray) {
			// 	$message->from('raul.reyes@allsites.es');
            //     $message->to(Auth::user()->email)->subject('Confirmación email de registro');
			// 	$message->embedData($data, $nuevosUsuariosArray);
			// });


            // Enviamos el email de confirmación
            // Mail::send('register_ok', $nuevosUsuariosArray, function($message) use ($nuevosUsuariosArray) {
            //    $message->from('raul.reyes@allsites.es');
            //    $message->to(Auth::user()->email)->subject('Confirmación email de registro');
            // });

            return $this->sendResponse($success, $nuevosUsuarios->count() . ' Users register successfully.');
        }
        else {
            $success['Error'] = 'Unauthorized';
            return $this->sendResponse($success, 'Users not created.');
        }
    }


	public function python() {
        $ruta_script = public_path('ejemplo.py');

		$result = Process::run(['python', $ruta_script]);

		$success['ok'] = 'CORRECTO';
		return $this->sendResponse($success, 'CORRECTO.');
    }
}
