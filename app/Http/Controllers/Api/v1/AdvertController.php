<?php
namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Models\Advert;
use App\Models\AdvertRate;
use App\Models\Booked;
use Carbon\Carbon;
use Illuminate\Validation\Validator;
use App\Services\GeneralService;



class AdvertController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $generalService;
    public function __construct(GeneralService $generalService)
    {
        // $this->middleware('auth');
        $this->generalService = $generalService;
    }

    //
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'nullable|string',
            'page' => 'required|string',
            // 'media' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'media' => 'required|string',
            'duration' => 'required|integer',
            'width' => 'required|integer',
            'height' => 'required|integer',
            'country' => 'required|string',
            'platform' => 'required|string',
            'section' => 'required|string',
            'mediaType' => 'required|string',
            'redirect_url' => 'required|string',
        ]);
        $media_url = '';

        if(strtolower($request->mediaType) == 'picture'){
            $image = $request->media;
            $cloudinary = Cloudinary::uploadApi()->upload($image, [
                'transformation' => [
                    [
                        'width' => $request->width,
                        'height' => $request->height,
                        'crop' => 'fit'
                    ]
                ]
            ]);
            $returned = [...$cloudinary];
            $media_url = $returned['secure_url'];
        }
        elseif(strtolower($request->mediaType) == 'gif'){
            $gif = $request->media;
            $cloudinary = Cloudinary::uploadApi()->upload($gif, [
                "resource_type" => "auto",
                "format" => "gif",
                "transformation" => [
                    "width" => $request->width,
                    "height" => $request->height,
                    "crop" => "scale"
                ]
            ]);
            $returned = [...$cloudinary];
            $media_url = $returned['secure_url'];
        }

        // save ad to database
        $ad = Advert::create([
            'user_id' => auth()->user()->id,
            'name' => $request->name,
            'media_url' => $media_url,
            'mediaType' => strtolower($request->mediaType),
            'duration' => $request->duration,
            'width' => $request->width,
            'height' => $request->height,
            'page' => strtolower($request->page),
            'country' => strtolower($request->country),
            'platform' => strtolower($request->platform),
            'section' => strtolower($request->section),
            'redirect_url' => $request->redirect_url,
        ]);

        return response()->json([
            'message' => 'success',
            'data' => $ad,
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $ad = Advert::find($id);
        if(!$ad){
            return response()->json([
                'message' => 'not found',
            ], 404);
        }
        $validatedData = $this->validate($request, [
            'name' => 'nullable',
            'page' => 'nullable',
            'duration' => 'nullable',
            'width' => 'nullable',
            'height' => 'nullable',
            'country' => 'nullable',
            'platform' => 'nullable',
            'section' => 'nullable',
            'media' => 'nullable',
            'mediaType' => 'nullable',
            'redirect_url' => 'nullable',
        ]);

        // Upload new media to Cloudinary if it exists
        if($request->media){
            $media_url = '';
            // upload image to cloudinary
            if($request->mediaType == 'picture'){
                $image = $request->media;
                $cloudinary = Cloudinary::uploadApi()->upload($image, [
                    'transformation' => [
                        [
                            'width' => $request->width,
                            'height' => $request->height,
                            'crop' => 'fit'
                        ]
                    ]
                ]);
                $returned = [...$cloudinary];
                $media_url = $returned['secure_url'];
            }elseif($request->mediaType == 'gif'){
                $gif = $request->media;
                $cloudinary = Cloudinary::uploadApi()->upload($gif, [
                    "resource_type" => "auto",
                    "format" => "gif"
                ]);
                $returned = [...$cloudinary];
                $media_url = $returned['secure_url'];
            }
        }

        // Update the ad with the new data
        $ad->fill($validatedData);
        $ad->save();

        // Return the updated ad
        return response()->json([
            'message' => 'success',
            'data' => $ad,
        ], 200);
    }


    public function destroy($id)
    {
        $ad = Advert::find($id);
        if(!$ad){
            return response()->json([
                'message' => 'not found',
            ], 404);
        }

        $ad->delete();
        return response()->json([
            'message' => 'success',
            'data' => $ad,
        ], 200);
    }


    public function index(Request $request)
    {
        $id = $request->id;
        if($id) {
            $ads = Advert::where('id', $id)->get();
            if ($ads->isEmpty()) {
                return response()->json([
                    'message' => 'not found',
                    'data' => null
                ], 404);
            }
        } else {
            $ads = Advert::all();
        }
        return response()->json([
            'message' => 'success',
            'data' => $ads
        ], 200);
    }


    public function filter(Request $request)
    {
        $page = strtolower($request->page);
        $country = strtolower($request->country);
        $platform = strtolower($request->platform);
        $section = strtolower($request->section);

        $ads = Advert::where('platform', $platform)->where('country', $country)->where('page', $page)->where('section', $section)->get();

        if ($ads->isEmpty()) {
            return response()->json([
                'message' => 'not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'success',
            'data' => $ads
        ], 200);
    }


    public function filterPage(Request $request)
    {
        $page = strtolower($request->page);
        $country = strtolower($request->country);
        $platform = strtolower($request->platform);

        $ads = Advert::where('platform', $platform)->where('country', $country)->where('page', $page)->get();

        if ($ads->isEmpty()) {
            return response()->json([
                'message' => 'not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'success',
            'data' => $ads
        ], 200);
    }


    public function filterPlatform(Request $request)
    {
        $country = strtolower($request->country);
        $platform = strtolower($request->platform);

        $adsQuery = Advert::where('platform', $platform);

        if ($country !== 'all') {
            $adsQuery->where('country', $country);
        }

        $ads = $adsQuery->get();

        if ($ads->isEmpty()) {
            return response()->json([
                'message' => 'not found',
                'data' => null
            ], 404);
        }


        return response()->json([
            'message' => 'success',
            'data' => $ads
        ], 200);
    }


    public function confirmPrice(Request $request){
        $this->validate($request, [
            'currency_code' => 'required|string',
            'duration' => 'required|string',
        ]);

        $rate = AdvertRate::first();

        $duration = $request->duration;
        $currency_code = $request->currency_code;

        $basePrice = $rate->dailyRate??1;
        $baseCode = $rate->currency_code??'USD';
        $price = $this->generalService->convertMoney($baseCode, $basePrice, $currency_code);

        $totalPrice = $price * $duration;

        return response()->json([
            'message' => 'success',
            'data' => $totalPrice,
        ], 200);
    }


    public function bookAdvert(Request $request){
        $this->validate($request, [
            'start_date' => 'required|date',
            'currency_code' => 'required|string',
            'duration' => 'required|integer',
            'price' => 'required|numeric',
            'type' => 'required|string',
        ]);

        $start_date = Carbon::parse($request->start_date);
        $duration = $request->duration;
        $currency_code = $request->currency_code;
        $type = $request->type;
        $totalPrice = $request->price;
        $bookingId = 'AdNinja' . uniqid();

        $booking = Booked::create([
            'user_id' => auth()->user()->id,
            'start_date' => $start_date,
            'end_date' => $start_date->addDays($duration),
            'duration' => $duration,
            'currency_code' => $currency_code,
            'type' => $type,
            'price' => $totalPrice,
            'booking_id' => $bookingId,
        ]);

        return response()->json([
            'message' => 'success',
            'data' => $booking,
        ], 200);
    }


    public function getUserBookings(){
        $bookings = Booked::where('user_id', auth()->user()->id)->get();
        return response()->json([
            'message' => 'success',
            'data' => $bookings,
        ], 200);
    }


    //get all booked by status
    public function getBookingsByStatus(Request $request){
        $status = $request->status;
        $bookings = Booked::where('status', $status)->get();
        return response()->json([
            'message' => 'success',
            'data' => $bookings,
        ], 200);
    }


    //get all booked advert
    public function getBookedAdvert(){
        $bookings = Booked::all();
        return response()->json([
            'message' => 'success',
            'data' => $bookings,
        ], 200);
    }


    //change booking status
    public function changeBookingStatus(Request $request){
        $this->validate($request, [
            'booking_id' => 'required|string',
            'status' => 'required|string',
        ]);

        $booking = Booked::where('booking_id', $request->booking_id)->first();
        if(!$booking){
            return response()->json([
                'message' => 'not found',
            ], 404);
        }

        $booking->status = $request->status;
        $booking->save();

        return response()->json([
            'message' => 'success',
            'data' => $booking,
        ], 200);
    }


    public function setAdvertRate(Request $request){
        $this->validate($request, [
            'dailyRate' => 'required|numeric',
        ]);

        $dailyRate = $request->dailyRate;

        $rate = AdvertRate::first();

        if ($rate) {
            $rate->update(['dailyRate' => $dailyRate]);
        } else {
            $rate = AdvertRate::create([
                'user_id' => auth()->user()->id,
                'currency_code' => 'USD',
                'dailyRate' => $dailyRate,
            ]);
        }

        return response()->json([
            'message' => 'success',
            'data' => $rate,
        ], 200);
    }








}
