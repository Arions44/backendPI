<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Answer;
use App\Models\Game;
use App\Models\User;
use App\Models\Play;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\TestIdResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GameController extends BaseController
{
    public function getPlayByUser(Request $request) {
        $userid = User::find($request->id)->id;
        $playes = Play::where('deleted', '<>', '1')
            ->where('user_id', '=', $userid)
            ->get();

        $success['playes'] = $playes;

        return $this->sendResponse($success, 'Playes retrieved successfully.');
    }

    // public function getTestByUser(Request $request) {
    //     $userid = User::find($request->id)->id;

    //     $questions = DB::table('answers')
    //         ->join('questions', 'questions.id', '=', 'answers.question_id')
    //         ->select('answers.id', 'answers.question_id', 'answers.selected',
    //         'answers.answer', 'answers.created_at','questions.type',
    //         'questions.text','questions.answer_1', 'questions.answer_2',
    //         'questions.answer_3', 'questions.answer_4')
    //         ->where('answers.user_id', '=', $userid)
    //         ->get();

    //     $success['questions'] = $questions;

    //     return $this->sendResponse($success, 'Questions retrieved successfully.');

	// 	// $exercisessorted = $exercises->sortByDesc('created_at');
    //     return $questions;
	// 	return $this->sendResponse(TestIdResource::collection($exercises), 'Exercises retrieved successfully.');

    // }


    public function newGameMade(Request $request) {
		$input = $request->all();
        $user_id = $input['user_id'];
        $game_id = $input['game_id'];

        // Creamos el nuevo test realizado
        $play = new Play;
        $play->user_id = $user_id;
        $play->game_id = $game_id;
        $play->created_at= Now();
        $play->save();

        $success['user_id'] = $user_id;
        $success['game_id'] = $input['game_id'];

        return $this->sendResponse($success, 'Play created successfully.');
    }

    public function games() {
    $games = Game::where('deleted', '<>', '1')
        ->with(['mood1:id,image', 'mood2:id,image', 'mood3:id,image', 'mood4:id,image'])
        ->get();

    $success['games'] = $games;

    return $this->sendResponse($success, 'Games retrieved successfully.');

    // $games = Game::where('deleted', 0)
	// 		->get(['message', 'opt_1', 'opt_2', 'opt_3', 'opt_4', 'right_opt']);

	// return $this->sendResponse(GameResource::collection($games), 'Games retrieved successfully.');
	}
}
