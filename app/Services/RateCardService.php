<?php

namespace App\Services;
use App\Models\RatesCard;
class RateCardService
{
    // Implement your logic here

    public function createRate(array $data)
    {
        return RatesCard::create($data);
    }

    public function updateRate($rateId, array $data)
    {
        $rate = RatesCard::findOrFail($rateId);
        $rate->update($data);

        return $rate;
    }

    public function deleteRate($rateId)
    {
        $rate = RatesCard::findOrFail($rateId);
        $rate->delete();
        return $rate;
    }

    public function getAllRates()
    {
        return RatesCard::all();
    }
}
