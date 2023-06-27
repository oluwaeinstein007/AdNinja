<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ExchangeHistory;
use App\Models\ExchangeRate;
use App\Services\GeneralService;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $generalService;
    public function __construct(GeneralService $generalService)
    {
        // $this->middleware('auth');
        $this->generalService = $generalService;
    }


    //create or update exchange rate
    public function currencyChangr(Request $request){
        //validate user info
        $this->validate($request, [
            "currency_code" => 'required|string|max:255',
            "rate" => 'required|numeric',
        ]);
        //create exchange rate
        $exchange_rate = ExchangeRate::where('currency_code', $request->currency_code)->first();
        if($exchange_rate){
            $exchange_rate = $exchange_rate->update([
                'rate' => $request->rate,
            ]);
        } else {
            $exchange_rate = new ExchangeRate();
            $exchange_rate->user_id = auth()->user()->id;
            $exchange_rate->currency_code = $request->currency_code;
            $exchange_rate->rate = $request->rate;
            $exchange_rate->save();
        }

        $exchange_history = new ExchangeHistory();
        $exchange_history->user_id = auth()->user()->id;
        $exchange_history->currency_code = $request->currency_code;
        $exchange_history->rate = $request->rate;
        $exchange_history->save();

        return response()->json([
            'message' => 'success',
            'data' => $exchange_rate
        ], 201);
    }

    //index exchange rate
    public function currentExchangeRate(){
        //get current exchange rate
        $exchange_rate = ExchangeRate::latest()->with('user')->get();
        return response()->json([
            'message' => 'success',
            'data' => $exchange_rate
        ], 200);
    }

    public function exchangeRateHistory(){
        //get exchange rate history
        $exchange_rate_history = ExchangeHistory::latest()->with('user')->get();
        return response()->json([
            'message' => 'success',
            'data' => $exchange_rate_history
        ], 200);
    }

    //get exchange rate by currency code
    public function getExchangeRateByCurrencyCode(Request $request){
        //get exchange rate by currency code
        $currency_code = $request->currency_code;
        // dd($currency_code);
        $exchange_rate = ExchangeRate::where('currency_code', $currency_code)->first();
        return response()->json([
            'message' => 'success',
            'data' => $exchange_rate->rate
        ], 200);
    }


    public function valueMoney(Request $request){
        $baseCode = $request->baseCode;
        $amount = $request->amount;
        $returnBaseCode = $request->returnBaseCode ?? 'USD';
        $result = $this->generalService->convertMoney($baseCode, $amount, $returnBaseCode);
        return response()->json([
            'message' => 'success',
            'data' => $result
        ], 200);
    }



    public function index()
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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
