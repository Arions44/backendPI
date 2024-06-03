<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Offer;
use App\Models\Enjoy;
use App\Models\User;
use App\Models\Business;
use Validator;
use PDF;
use App\Http\Resources\BusinessResource;
use App\Http\Resources\OfferResource;
use App\Http\Resources\OfferCountResource;
use App\Http\Resources\OfferFreeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class OfferController extends BaseController
{
    public function getoffers(): JsonResponse {
        $offers = Offer::where('deleted', '<>', '1')->with(['business','enjoys'])->get();

        return $this->sendResponse(OfferResource::collection($offers), 'Offers retrieved successfully.');
    }

    public function sendStadistics() {
        $usersCount = User::where('deleted', '<>', '1')->sum('num_logins');
        $offers = Offer::where('deleted', '<>', '1')->with(['business'])->orderBy('counter', 'DESC')->get();
        $pdf = \PDF::loadView('pdf',compact('offers', 'usersCount'));

        // ENVIARLO POR EMAIL
        $data["email"] = "yasmin.akni@gmail.com";
        $data["title"] = "PDF de estadísticas";
        $data["body"] = "Puedes descargar tu pdf";

        Mail::send('emails.stadisticsMail', $data, function($message)use($data, $pdf) {
            $message->to($data["email"], $data["email"])
                    ->subject($data["title"])
                    ->attachData($pdf->output(), "Estadísticas.pdf");
        });

        $success['ok'] = 'ok';

        return $this->sendResponse($success, 'Stadistics sended successfully.');
    }

    public function newOffersFree(Request $request): JsonResponses {
        $userid = User::find($request->id)->id;

        $offersfree = Offer::where('deleted', 0)
            ->where('type', 'profit')
            ->whereNotIn('id', function (Builder $query) use ($userid) {
                $query->select('id_offer')
                    ->from('enjoys')
                    ->where('id_user', $userid);
        })->get();

        return $this->sendResponse(OfferResource::collection($offersfree), 'OffersFree retrieved successfully.');
    }

    public function offersFree(Request $request) {
        $userid = User::find($request->id)->id;

        $offersfree = Offer::where('deleted', 0)
            ->where('type', 'profit')->get();

        $offersfreeused = Offer::where('deleted', 0)
            ->where('type', 'profit')
            ->whereNotIn('id', function (Builder $query) use ($userid) {
                $query->select('id_offer')
                    ->from('enjoys')
                    ->where('id_user', $userid);
        })->get();

        $used = [];
        for ($i=0; $i < count($offersfreeused); $i++) {
            $used[$i] = $offersfreeused[$i]->id;
        }

        for ($i=0; $i < count($offersfree); $i++) {
            if(in_array($offersfree[$i]->id, $used)) {
                $offersfree[$i]->enjoyed = '0';
            }
            else {
                $offersfree[$i]->enjoyed = '1';
            }
        }

        return $this->sendResponse(OfferFreeResource::collection($offersfree), 'OffersFree retrieved successfully.');
    }

    public function userOfferFree(Request $request) {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required',
            'id_offer' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();

        $input['enjoyed'] = 1;
        $input['enjoyed_at'] = Now();

        $enjoy = Enjoy::create($input);

        $success['id_user'] =  $enjoy->id_user;
        $success['id_offer'] = $enjoy->id_offer;

        return $this->sendResponse($success, 'OffersFree saved successfully.');
    }

    public function newOffer(Request $request) {
        // Comprobamos que el usuario es administrador
        $type = Auth::user()->type;

        if($type=='a') {
            $input = $request->all();
            $validator = Validator::make($input, [
                'id_business' => 'required',
                'type' => 'required',
                'name' => 'required',
                'discount' => 'nullable',
                'description' => 'nullable',
                'last_description' => 'nullable',
                'completeDescription' => 'nullable',
                'image' => 'required',
                'category' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $offer = new Offer;
            $offer->id_business = $input['id_business'];
            $offer->type = $input['type'];
            $offer->name = $input['name'];
            $offer->discount = $input['discount']?? null;
            $offer->description = $input['description']?? null;
            $offer->last_description = $input['last_description']?? null;
            $offer->completeDescription = $input['completeDescription']?? null;
            $offer->category = $input['category'];
            $offer->created_at = Now();

            // Guardar la imagen si se ha enviado
            if ($request->hasFile('image')) {
                $pathTarget = 'offers';
                $name = $request->file('image')->getClientOriginalName();
                $options = 'local';
                $path = $request->file('image')->storeAs($pathTarget, $name, $options);
                $offer->image = "https://tarjetafanky.com.es/fankyapiapp/storage/app/offers/" . $name;
            }

            $offer->save();

            $success['id_offer'] = $offer->id;
            $success['name'] = $name;

            return $this->sendResponse($success, 'Offer saved successfully.');
        }
        else {
            $success['Error'] = 'Unauthorized';
            return $this->sendResponse($success, 'Offer not created.');
        }
    }

    public function addOffersCount(Request $request) {
        $offer = offer::find($request->id);

        if($offer){
            $offer->counter = $offer->counter + 1;
            $offer->save();

            $success['id'] = $offer->id;

            return $this->sendResponse($success, 'Offer Counter aumented successfully.');
        }
    }
}
