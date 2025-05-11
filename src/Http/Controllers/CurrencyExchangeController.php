<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Unusualify\Modularity\Services\CurrencyExchangeService;

class CurrencyExchangeController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyExchangeService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Fetch and update exchange rates manually.
     */
    public function fetchRates()
    {
        try {
            $this->currencyService->fetchExchangeRates();

            return response()->json(['message' => 'Exchange rates updated successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update rates.'], 500);
        }
    }

    /**
     * Convert an amount to a specific currency.
     */
    public function convert(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'currency' => 'required|string|size:3',
        ]);
        try {
            $converted = $this->currencyService->convertTo($request->amount, mb_strtoupper($request->currency));

            return response()->json([
                'converted_amount' => $converted,
                'exchange_rate' => $this->currencyService->getExchangeRate(mb_strtoupper($request->currency)),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Conversion failed.'], 400);
        }
    }

    /**
     * Get exchange rate for a specific currency.
     */
    public function getRate(Request $request, $currency)
    {
        try {
            $rate = $this->currencyService->getExchangeRate(mb_strtoupper($currency));

            return response()->json(['currency' => mb_strtoupper($currency), 'rate' => $rate], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Rate not found.'], 404);
        }
    }
}
