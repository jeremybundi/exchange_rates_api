<?php
use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

class CurrencyController extends Controller
{
    public function convertAction()
    {
        // Get input data (base_currency, target_currency, amount)
        $data = $this->request->getJsonRawBody();

        // Validate input data (ensure required fields are present)
        if (!isset($data->base_currency, $data->target_currency, $data->amount)) {
            $response = new Response();
            $response->setStatusCode(400, 'Bad Request');
            $response->setJsonContent([
                'status' => 'error',
                'message' => 'Invalid input data',
            ]);
            return $response;
        }

        // Fetch exchange rates from the external API
        $exchangeRates = $this->fetchExchangeRates($data->base_currency, $data->target_currency);

        // Calculate the converted amount
        $convertedAmount = $this->calculateConvertedAmount(
            $data->amount,
            $exchangeRates['conversion_rate'] ?? null
        );

        // Create a JSON response
        $response = new Response();
        $response->setJsonContent([
            'status' => 'OK',
            'converted_amount' => $convertedAmount,
        ]);

        return $response;
    }

    private function fetchExchangeRates(string $baseCurrency, string $targetCurrency): array
    {
        // Build the API URL
        $apiKey = '8061c7b02b3db2912aa1f09d'; 
        $apiUrl = "https://v6.exchangerate-api.com/v6/{$apiKey}/pair/{$baseCurrency}/{$targetCurrency}/{$amount}";

        // Initialize cURL session
        $ch = curl_init($apiUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            // Handle error (log, return default rates, etc.)
            return [];
        }

        // Parse the JSON response
        $data = json_decode($response, true);

        // Close cURL session
        curl_close($ch);

        return $data;
    }

    private function calculateConvertedAmount(float $amount, ?float $exchangeRate): float
    {
        if ($exchangeRate !== null) {
            return $amount * $exchangeRate * 1.05;
        } else {
            return 0.0;
        }
    }
}
