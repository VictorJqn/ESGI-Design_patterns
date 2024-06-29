<?php

namespace PaymentLibrary\Payments;

use PaymentLibrary\Interfaces\PaymentInterface;
use PaymentLibrary\Exceptions\PaymentException;

class StripePayment implements PaymentInterface
    {
    private $config;
    private $paymentIntentId;

    public function initialize(array $config)
        {
        $this->config = $config;
        }

    public function createTransaction(float $amount, string $currency, string $description)
        {
        $url = 'https://api.stripe.com/v1/payment_intents';
        $data = [
            'amount' => $amount * 100,
            'currency' => $currency,
            'description' => $description,
            'payment_method' => 'pm_card_visa',
            'automatic_payment_methods[enabled]' => 'true',
            'automatic_payment_methods[allow_redirects]' => 'never'
        ];

        $response = $this->sendRequest('POST', $url, $data);

        if (isset($response['id'])) {
            $this->paymentIntentId = $response['id'];
            return http_response_code(200);
            } else {
            return http_response_code(400);
            }
        }

    public function executeTransaction()
        {
        if (!$this->paymentIntentId) {
            throw new PaymentException("No transaction initialized");
            }

        $url = 'https://api.stripe.com/v1/payment_intents/' . $this->paymentIntentId . '/confirm';
        $data = [
            'payment_method' => 'pm_card_visa',
            'return_url' => 'http://127.0.0.1:8080/'
        ];

        $response = $this->sendRequest('POST', $url, $data);

        if (isset($response['status'])) {
            if ($response['status'] === 'succeeded') {
                return http_response_code(200);
                } else {
                throw new PaymentException("Failed to confirm transaction: " . (isset($response['error']) ? $response['error']['message'] : "Unknown error"));
                }
            } else {
            throw new PaymentException("Invalid response from Stripe API");
            }
        }

    public function cancelTransaction()
        {
        if (!$this->paymentIntentId) {
            throw new PaymentException("No transaction initialized");
            }

        if ($this->getStatus() == "succeeded") {
            throw new PaymentException("Cannot cancel a completed transaction");
            }

        $url = 'https://api.stripe.com/v1/payment_intents/' . $this->paymentIntentId . '/cancel';
        $this->sendRequest('POST', $url);
        }

    public function deletePaymentIntent(string $paymentIntentId)
        {
        $url = 'https://api.stripe.com/v1/payment_intents/' . $paymentIntentId . '/cancel';
        $response = $this->sendRequest('POST', $url);
        if (isset($response['status']) && $response['status'] === 'canceled') {
            return http_response_code(200);
            } else {
            throw new PaymentException("Failed to cancel PaymentIntent: " . (isset($response['error']) ? $response['error']['message'] : "Unknown error"));
            }
        }



    public function getStatus()
        {
        $url = 'https://api.stripe.com/v1/payment_intents/' . $this->paymentIntentId;
        $response = $this->sendRequest('GET', $url);
        return $response['status'];
        }

    private function sendRequest($method, $url, $data = null)
        {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->config['api_key'],
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new PaymentException(curl_error($ch));
            }

        curl_close($ch);

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMessage = isset($decodedResponse['error']['message']) ? $decodedResponse['error']['message'] : 'Unknown error';
            throw new PaymentException('API request failed with response: ' . $errorMessage);
            }

        return $decodedResponse;
        }
    }
