<?php
namespace App\Http\Helpers;

use App\Models\Advert;
use Carbon\Carbon;

use DOTNET;
use Termwind\Components\Dd;



    class Helper
        {
            /**
             * Update popular trips
             *
             * @return void
             */

             public static function runClearAdverts(){
                // Get all adverts from the database
                $adverts = Advert::all();

                // Loop through each advert and check if it's expired
                foreach ($adverts as $advert) {
                    $duration = $advert->duration; // Get the number of days from the duration column
                    $createdDate = Carbon::parse($advert->created_at); // Parse the created_at date using Carbon

                    // Check if the ad is expired
                    if ($createdDate->addDays((int) $duration)->isPast()) {
                        // If the ad is expired, delete it from the database
                        $advert->delete();
                    }
                }
             }

        }
