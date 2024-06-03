<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Exercise;
use App\Models\Made;
use App\Models\User;
use Validator;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\ExerciseResource;
use App\Http\Resources\ExerciseIdResource;

class ExercisesController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Exercise $exercise)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exercise $exercise)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exercise $exercise)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exercise $exercise)
    {
        //
    }
	
	public function newExercise(Request $request) {
		// $input = $request->all();
        // $type = $input['type_user'];
        // $userid = $input['id_user'];
        
        // // Comprobamos que el usuario no es administrador
        // if($type=='u') {
        //     switch($input['type']) {
        //         case 'event':
        //             $event = new Event;
        //             $event->type = $input['type'];
		// 			$event->name = "";
        //             $event->user_id = $userid;
        //             $event->description = $input['description'];
		// 			$event->date = $input['date'];
        //             $event->image = "";
        //             $event->created_at= Now();
        //             $event->save();
        //             break;
        //         case 'emotion':
        //             $feel = new Feel;
        //             $feel->user_id = $userid;
		// 			$feel->emotion_id = $input['emotion_id'];
        //             $feel->created_at= Now();
        //             $feel->save();
        //             break;
        //         case 'mood':
        //             $has = new Has;
        //             $has->user_id = $userid;
		// 			$has->mood_id = $input['mood_id'];
        //             $has->created_at= Now();
        //             $has->save();
        //             break;
        //         default:
        //             break;
        //     }

        //     $success['id_user'] = $userid;
        //     $success['type'] = $input['type'];

        //     return $this->sendResponse($success, 'Element created successfully.');
        // }
        // else {
        //     $success['Error'] = 'Unauthorized';
        //     return $this->sendResponse($success, 'Element not created.');
        // }
    }
	
	public function newExerciseMade(Request $request) {
		$input = $request->all();
        //$userid = $input['id_user'];
        
        $made = new Made;
        $made->user_id = $input['user_id'];
        $made->exercise_id = $input['exercise_id'];
        $made->created_at= Now();
        $made->save();
		
        $success['user_id'] = $input['user_id'];
        $success['exercise_id'] = $input['exercise_id'];

        return $this->sendResponse($success, 'Exercise-Made created successfully.');
    }
	
	public function deleteExerciseMade(Request $request) {
		$input = $request->all();
        
		$userid = $input['id_user'];
		$exercise_id = $input['exercise_id'];
		
		$exercise = Made::where('user_id', $userid)->where('exercise_id', $exercise_id)->first();
        if($exercise) {
            // Eliminamos el usuario
            $exercise->delete();
		}
		
		$success['userid'] = $userid;
		$success['exerciseid'] = $exercise_id;
		
        return $this->sendResponse($success, 'Exercise-Delete created successfully.');
    }
		
	public function exercisesByAlum(Request $request) {
        $userid = User::find($request->id)->id;
        
        $exercises = DB::table('mades')
            ->join('exercises', 'exercises.id', '=', 'mades.exercise_id')
            ->select('mades.id', 'mades.created_at', 'mades.exercise_id', 'exercises.name', 'exercises.improvement',
            'exercises.type', 'exercises.explanation', 'exercises.image', 'exercises.audio', 'exercises.video')
            ->where('mades.user_id', '=', $userid)
            ->get();
	
		// $exercisessorted = $exercises->sortByDesc('created_at');

		return $this->sendResponse(ExerciseIdResource::collection($exercises), 'Exercises retrieved successfully.');
	}

    public function exercises() {
        $exercises = Exercise::where('deleted', '=', 0)->get();
	
		return $this->sendResponse(ExerciseResource::collection($exercises), 'Exercises retrieved successfully.');
	}
	
	public function exerciseById(Request $request) {
		$exercise = Exercise::find($request->id);
		//$exercise = Exercise::where('id', $request->id)->get();
		$user = Auth::user();
		// Para obtener el ID:
		$user->id;
        $userid = $user->id;
		
        $made = Made::where('user_id', $userid)->where('exercise_id', $exercise->id)->get();

		$success['id'] = $exercise->id;
        $success['name'] = $exercise->name;
        $success['improvement'] = $exercise->improvement;
        $success['type'] = $exercise->type;
        $success['explanation'] = $exercise->explanation;
        $success['image'] = $exercise->image;
        $success['audio'] = $exercise->audio;
        $success['video'] = $exercise->video;
		if(count($made)>0){
			$success['made'] = 1;
		}
		else {
			$success['made'] = 0;
		}

        return $this->sendResponse($success, 'Exercise-Made created successfully.');

		//return $this->sendResponse(ExerciseResource::collection($exercise), 'Exercise retrieved successfully.');
	}
}
