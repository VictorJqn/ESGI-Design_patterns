<?php

namespace PaymentLibrary\Payments;

use PaymentLibrary\Interfaces\PaymentInterface;

class StripePayment implements PaymentInterface {
    private $config;
    private $paymentIntentId;

    public function initialize(array $config) {
        $this->config = $config;
    }

    public function createTransaction(float $amount, string $currency, string $description) {
        $url = 'https://api.stripe.com/v1/payment_intents';
        $data = [
            'amount' => $amount * 100, // Stripe requires the amount in cents
            'currency' => $currency,
            'description' => $description,
        ];

        $response = $this->sendRequest('POST', $url, $data);
        $this->paymentIntentId = $response['id'];
    }

    public function executeTransaction() {
        // In a real scenario, you would confirm the payment intent here
        // $this->confirmPaymentIntent();
        return $this->paymentIntentId;
    }

    public function cancelTransaction() {
        $url = 'https://api.stripe.com/v1/payment_intents/' . $this->paymentIntentId . '/cancel';
        $this->sendRequest('POST', $url);
    }

    public function getStatus() {
        $url = 'https://api.stripe.com/v1/payment_intents/' . $this->paymentIntentId;
        $response = $this->sendRequest('GET', $url);
        return $response['status'];
    }

    private function sendRequest($method, $url, $data = null) {
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
        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}
