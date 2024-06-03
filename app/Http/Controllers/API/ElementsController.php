<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Cause;
// use App\Models\Emotion;
use App\Models\Mood;
use App\Models\User;
// use App\Models\Feel;
use App\Models\Has;
use Validator;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

use App\Http\Resources\ElementResource;
use App\Http\Resources\MoodResource;
use App\Http\Resources\ColorResource;

class ElementsController extends BaseController
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
    public function show(element $element)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(element $element)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, element $element)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(element $element)
    {
        //
    }

	public function newElement(Request $request) {
		$input = $request->all();
        $type = $input['type_user'];
        $userid = $input['id_user'];
        $color = $input['color'];

        // Comprobamos que el usuario no es administrador
        if($type=='u') {
            switch($input['type']) {
                case 'event':
                    // Obtenemos la imagen asociada al estado de ánimo
                    $moodImage = Mood::find($input['mood_id'])->image;

					// Creamos el nuevo evento
                    $event = new Event;
                    $event->type = $input['type'];
					$event->name = "";
                    $event->user_id = $userid;
                    $event->image = $moodImage;
                    $event->color = $color;
                    $event->description = $input['description'];

					// LAS SIGUIENTES LÍNEAS HAY QUE DESCOMENTARLAS
                    // $event->image = $input['image'];
                    // $event->color = $input['color'];

                    $event->date = $input['date'];
                    $event->created_at= Now();
                    $event->save();

					// Creamos la nueva causa
					$cause = new Cause;
					$cause->mood_id = $input['mood_id'];
					$cause->event_id = $event->id;
					$cause->created_at= Now();
					$cause->save();
                    break;
                // case 'emotion':
                //     $feel = new Feel;
                //     $feel->user_id = $userid;
				// 	$feel->emotion_id = $input['emotion_id'];
				// 	$feel->date= Now();
                //     $feel->created_at= Now();
                //     $feel->save();
                //     break;
                case 'mood':
                    $has = new Has;
                    $has->user_id = $userid;
					$has->mood_id = $input['mood_id'];
					$has->date= Now();
                    $has->created_at= Now();
                    $has->save();
                    break;
                default:
                    break;
            }

            $success['id_user'] = $userid;
            $success['type'] = $input['type'];
			if($input['mood_id']) {
				$success['mood_id'] = $input['mood_id'];
			}
            if($moodImage) {
				$success['moodImage'] = $moodImage;
			}

            return $this->sendResponse($success, 'Element created successfully.');
        }
        else {
            $success['Error'] = 'Unauthorized';
            return $this->sendResponse($success, 'Element not created.');
        }
    }

    public function getColors() {
        $colors = Mood::where('deleted', 0)
			->get(['id', 'color']);

        return $this->sendResponse(ColorResource::collection($colors), 'Colors retrieved successfully.');
    }

    public function elements(Request $request) {
        $userid = User::find($request->id)->id;

        $events = Event::where('deleted', 0)
			->where('user_id','=', $userid)
			->get(['id', 'type', 'name', 'description', 'date', 'image', 'color', 'created_at']);

		$moods = DB::table('has')
            ->join('moods', 'moods.id', '=', 'has.mood_id')
            ->select('has.id', 'has.date', 'has.created_at', 'moods.type', 'moods.name',
            'moods.description', 'moods.image', 'moods.color')
            ->where('has.user_id', '=', $userid)
            ->get();

        // $emotions = DB::table('feels')
        //     ->join('emotions', 'emotions.id', '=', 'feels.emotion_id')
        //     ->select('feels.id', 'feels.date', 'feels.created_at', 'emotions.type', 'emotions.name',
        //     'emotions.description', 'emotions.image')
        //     ->where('feels.user_id', '=', $userid)
        //     ->get();

        // return $emotions;


        //  $moods = Mood::where('deleted', 0)
		//  	->whereIn('id', function (Builder $query) use ($userid) {
		//  		$query->select('mood_id')
		//  			->from('has')
        //              ->where('user_id', $userid);
		//  })->get();

        //  $emotions = Emotion::where('deleted', 0)
		//  	->whereIn('id', function (Builder $query) use ($userid) {
		//  		$query->select('emotion_id')
		//  			->from('feels')
        //              ->where('user_id', $userid);
		//  })->get();

		// $elements = $events->concat($moods)->concat($emotions);
		$elements = $events->concat($moods);
		$elementssorted = $elements->sortByDesc('date');

		return $this->sendResponse(ElementResource::collection($elementssorted), 'Elements retrieved successfully.');
	}

    public function getMoods(Request $request) {
        $userid = User::find($request->id)->id;

		$moods = DB::table('has')
            ->join('moods', 'moods.id', '=', 'has.mood_id')
            ->select('has.id', 'has.date', 'has.created_at', 'moods.type', 'moods.name',
            'moods.description', 'moods.image', 'moods.color')
            ->where('has.user_id', '=', $userid)
			->where('has.deleted', '=', 0)
            ->get();

		// $elements = $events->concat($moods);
		// $elementssorted = $elements->sortByDesc('date');
		$moodssorted = $moods->sortByDesc('date');

		// return $this->sendResponse(ElementResource::collection($elementssorted), 'Elements retrieved successfully.');
		return $this->sendResponse(ElementResource::collection($moodssorted), 'Moods retrieved successfully.');
	}

    public function moods() {
        $moods = Mood::where('deleted', 0)
			->get(['id', 'name', 'description', 'image', 'color']);

		return $this->sendResponse(MoodResource::collection($moods), 'Moods retrieved successfully.');
	}

    // public function emotions() {
    //     $emotions = Emotion::where('deleted', 0)
	// 		->get(['id', 'name', 'description', 'image']);

	// 	return $this->sendResponse(EmotionResource::collection($emotions), 'Emotions retrieved successfully.');
	// }
}
