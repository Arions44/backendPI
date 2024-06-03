<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\TestIdResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TestController extends BaseController
{
    public function getTestByUser(Request $request) {
        $input = $request->all();
        $userid = $input['id'];
        // return $userid;
        // $userid = User::find($request->id)->id;

        $questions = DB::table('answers')
            ->join('questions', 'questions.id', '=', 'answers.question_id')
            ->select('answers.id', 'answers.question_id', 'answers.selected',
            'answers.answer', 'answers.created_at','questions.type',
            'questions.text','questions.answer_1', 'questions.answer_2',
            'questions.answer_3', 'questions.answer_4')
            ->where('answers.user_id', '=', $userid)
            ->get();

        $success['questions'] = $questions;

        return $this->sendResponse($success, 'Questions retrieved successfully.');

		// $exercisessorted = $exercises->sortByDesc('created_at');
        return $questions;
		return $this->sendResponse(TestIdResource::collection($exercises), 'Exercises retrieved successfully.');

    }


    public function newTestMade(Request $request) {
		$input = $request->all();
        $user_id = $input['user_id'];
        $question_id = $input['question_id'];

        $selected = $input['selected'];
        $answered = $input['answered'];

        // Creamos el nuevo test realizado
        $answer = new Answer;
        $answer->user_id = $user_id;
        $answer->question_id = $question_id;
        if(!is_null($selected)) {
            $answer->selected = $selected;
        }
        if(!is_null($answered)) {
            $answer->answer = $answered;
        }

        $answer->created_at= Now();
        $answer->save();

        $success['user_id'] = $user_id;
        $success['question_id'] = $input['question_id'];
        $success['selected'] = $input['selected'];
        $success['answered'] = $answered;

        return $this->sendResponse($success, 'Answer created successfully.');
    }

    public function questions() {
        $questions = Question::where('deleted', 0)
			->get(['id', 'text', 'answer_1', 'answer_2', 'answer_3', 'answer_4']);

		return $this->sendResponse(QuestionResource::collection($questions), 'Questions retrieved successfully.');
	}
}
