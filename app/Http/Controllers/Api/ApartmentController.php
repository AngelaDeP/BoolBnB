<?php

namespace App\Http\Controllers\Api;

use App\Apartment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $apartments = Apartment::with(['facilities'])->get();

        $apartments->each(function($apartment) {
            $apartment->image = url('storage/' . $apartment->image);
        });


        return response()->json(
            [
                'data' => $apartments,
                'success' => true
            ]
            );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $apartment = Apartment::where('slug', '=', $slug)->with(['facilities'])->first();

        $apartment->image = url('storage/' . $apartment->image);

        if($apartment) {
            return response()->json(
                [
                    'data' => $apartment,
                    'success' => true
                ]
            );
        } else {
            return response()->json(
                [
                    'data' => 'Nessun risultato',
                    'success' => false
                ]
            );
        }

    }

    /**
     *
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request 
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $location = $request->input('location');

        $geocoded = Http::withoutVerifying()->get('https://api.tomtom.com/search/2/geocode/'. $location .'.json', [
            'key' => config('services.tomtom.key'),
            'limit' => '1'
        ]);

       
        $lat = $geocoded['results'][0]['position']['lat'];
        $lon = $geocoded['results'][0]['position']['lon'];
        
        $apartments = Apartment::all();

        $filtered = ['array()'];
        foreach($apartments as $apartment) {
            $distance = self::getDistance($lat, $lon, $apartment->latitude, $apartment->longitude);
            if($distance <= 20000) {
                array_push($filtered, $apartment);
            };
        }

        return compact('filtered', 'lat', 'lon');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    //Calcolo distanza dal punto inserito
    protected function getDistance($lat1, $lon1, $lat2, $lon2) {

        $R = 6371000;

        $lat1 = round($lat1 * (M_PI / 180), 14);
        $lat2 = round($lat2 * (M_PI / 180), 14);

        $deltaLat = round(($lat2-$lat1) * (M_PI / 180), 14);
        $deltaLon = round(($lon2-$lon1) * (M_PI / 180), 14);
        
        //$d = asin( sin($lat1)*sin($lat2) + cos($lat1)*cos($lat2) * cos($deltaLon) ) * $R;
        $a = pow(sin($deltaLat/2), 2) + cos($lat1) * cos($lat2) * pow(sin($deltaLon/2), 2);
        $c = 2 * atan2(sqrt($a),sqrt(1-$a));

        $d = $R * $c;

        return $d;
    }
}
