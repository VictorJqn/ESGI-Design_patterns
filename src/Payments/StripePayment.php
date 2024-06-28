<?php

namespace PaymentLibrary\Payments;

use PaymentLibrary\Interfaces\PaymentInterface;

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
            throw new \Exception("No transaction initialized");
            }

        $url = 'https://api.stripe.com/v1/payment_intents/' . $this->paymentIntentId . '/confirm';
        $data = [
            'payment_method' => "pm_card_visa",
        ];

        $response = $this->sendRequest('POST', $url, $data);
        var_dump($response);
        if (isset($response['status']) && $response['status'] === 'succeeded') {
            return http_response_code(200);
            } else {
            throw new \Exception("Failed to confirm transaction");
            }
        }



    public function cancelTransaction()
        {
        $url = 'https://api.stripe.com/v1/payment_intents/' . $this->paymentIntentId . '/cancel';
        $this->sendRequest('POST', $url);
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
        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
            }

        curl_close($ch);

        return json_decode($response, true);
        }
    }
