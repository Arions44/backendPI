<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Business;
use Validator;
use App\Http\Resources\BusinessResource;
use Illuminate\Http\JsonResponse;

class BusinessController extends BaseController
{
    public function getBusinesses(): JsonResponse
    {
        $businesses = Business::where('deleted', '<>', '1')->get();

        return $this->sendResponse(BusinessResource::collection($businesses), 'Businesss retrieved successfully.');
    }

    public function newBusiness(Request $request) {
        // HAY QUE COMPROBAR QUE SEA ADMINISTRADOR
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'address' => 'required',
            'city' => 'nullable',
            'logo' => 'required',
            'lat' => 'nullable',
            'lon' => 'nullable',
            'web' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $business = new Business;
        $business->name = $input['name'];
        $business->address = $input['address'];
        $business->city = $input['city']?? null;
        $business->lat = $input['lat']?? null;
        $business->lon = $input['lon']?? null;
        $business->web = $input['web']?? null;
        $business->created_at = Now();

        // // Guardar la imagen si se ha enviado
        if ($request->hasFile('logo')) {
            // $pathTarget = 'logos';
            // $name = $request->file('logo')->getClientOriginalName();
            // $options = 'local';
            // $path = $request->file('logo')->storeAs($pathTarget, $name, $options);
            // $business->logo = "https://tarjetafanky.com.es/fankyapiapp/storage/app/logos/" . $name;



            $imagen = $request->file('logo');
            $nameImagen = $_FILES['logo']['name']; // . '.' . $imagen->getClientOriginalExtension();
            // $pathPhoto = $photo->storeAs('resources/images/photos/', $namePhoto);
            $pathImagen = public_path('images/') . $nameImagen;
            copy($imagen->getRealPath(),$pathImagen);
            $business->logo = "https://tarjetafanky.com.es/fankyapiapp/public/images/" . $imagen->getClientOriginalName();

        }

        $business->save();

        $success['id'] = $business->id;
        $success['name'] = $business->name;

        return $this->sendResponse($success, 'Business saved successfully.');
    }
}
