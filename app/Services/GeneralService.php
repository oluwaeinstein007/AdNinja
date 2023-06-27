<?php

namespace App\Services;

use App\Models\ExchangeHistory;
use App\Models\ExchangeRate;
use Exception;
use GuzzleHttp\Client;

class GeneralService
{
    /**
     * Perform some general service logic.
     *
     * @param mixed $data
     * @return mixed
     */
    public function convertMoney($baseCode, $amount, $returnBaseCode){
        $incomingExchangeRate = $this->getExchangeRate($baseCode);
        $outgoingExchangeRate = $this->getExchangeRate($returnBaseCode);

        if ($incomingExchangeRate === null || $outgoingExchangeRate === null) {
            throw new Exception("Invalid currency code(s).");
        }

        $result = $amount * ($outgoingExchangeRate / $incomingExchangeRate);
        $result = $this->roundNum($result);
        return $result;
    }


    public function getExchangeRate($currencyCode){
        $exchangeRate = ExchangeRate::where('currency_code', $currencyCode)->value('rate');

        if ($exchangeRate === null) {
            $exchangeRate = $this->newCurrency($currencyCode);
            $this->addNewCurrency($currencyCode,$exchangeRate);
        }
        return $exchangeRate;
        // return $exchangeRate !== null ? $exchangeRate : 1;
    }


    public function roundNum($num) {
        return number_format($num, 2, '.', '');
    }


    public function newCurrency($currencyCode){
        $client = new Client();
        $url = 'https://api.apilayer.com/fixer/convert';
        $apikey = env('FOREX_API_KEY');

        $toCode = $currencyCode;
        $fromCode = 'USD';
        $headers = [
        'Content-Type' => 'text/plain',
        'apikey' => $apikey
        ];
        $params = [
            'to' => $toCode,
            'from' => $fromCode,
            'amount' => 1
        ];
        $response = $client->request('GET', $url, [
            'query' => $params,
            'headers' => $headers
        ]);

            $responseBody = $response->getBody()->getContents();
            $jsonData = json_decode($responseBody, true);

            $rate = $jsonData['info']['rate'];
            $rate = $this->roundNum($rate);
            return $rate;

    }


    public function addNewCurrency($currencyCode,$rate){
        $exchangeRate = new ExchangeRate();
        $exchangeRate->user_id = auth()->user()->id??1;
        $exchangeRate->currency_code = $currencyCode;
        $exchangeRate->rate = $rate;
        $exchangeRate->save();

        $exchange_history = new ExchangeHistory();
        $exchange_history->user_id = auth()->user()->id??1;
        $exchange_history->currency_code = $currencyCode;
        $exchange_history->rate = $rate;
        $exchange_history->save();
    }

}
