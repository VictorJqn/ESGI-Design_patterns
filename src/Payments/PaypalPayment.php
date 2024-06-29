<?php

namespace PaymentLibrary\Payments;

use PaymentLibrary\Interfaces\PaymentInterface;
use PaymentLibrary\Exceptions\PaymentException;

class PaypalPayment implements PaymentInterface
    {
    private $config;
    private $paymentId;

    public function initialize(array $config)
        {
        $this->config = $config;
        }

    public function createTransaction(float $amount, string $currency, string $description)
        {
        $url = 'https://api.paypal.com/v2/checkout/orders';
        $data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => $amount
                    ],
                    'description' => $description
                ]
            ]
        ];

        $response = $this->sendRequest('POST', $url, $data);

        if (isset($response['id'])) {
            $this->paymentId = $response['id'];
            return http_response_code(200);
            } else {
            return http_response_code(400);
            }
        }

    public function executeTransaction()
        {
        if (!$this->paymentId) {
            throw new PaymentException("No transaction initialized");
            }

        $url = 'https://api.paypal.com/v2/checkout/orders/' . $this->paymentId . '/capture';
        $data = [];

        $response = $this->sendRequest('POST', $url, $data);

        if (isset($response['status']) && $response['status'] === 'COMPLETED') {
            return http_response_code(200);
            } else {
            throw new PaymentException("Failed to capture transaction: " . (isset($response['error']) ? $response['error']['message'] : "Unknown error"));
            }
        }

    public function cancelTransaction()
        {
        if (!$this->paymentId) {
            throw new PaymentException("No transaction initialized");
            }

        $url = 'https://api.paypal.com/v2/checkout/orders/' . $this->paymentId . '/cancel';
        $this->sendRequest('POST', $url);
        }

    public function deletePaymentIntent(string $paymentId)
        {
        $url = 'https://api.paypal.com/v2/checkout/orders/' . $paymentId . '/cancel';
        $response = $this->sendRequest('POST', $url);
        if (isset($response['status']) && $response['status'] === 'CANCELLED') {
            return http_response_code(200);
            } else {
            throw new PaymentException("Failed to cancel PaymentOrder: " . (isset($response['error']) ? $response['error']['message'] : "Unknown error"));
            }
        }

    public function getStatus()
        {
        $url = 'https://api.paypal.com/v2/checkout/orders/' . $this->paymentId;
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
            'Content-Type: application/json'
        ]);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new PaymentException(curl_error($ch));
            }

        curl_close($ch);

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMessage = isset($decodedResponse['message']) ? $decodedResponse['message'] : 'Unknown error';
            throw new PaymentException('API request failed with response: ' . $errorMessage);
            }

        return $decodedResponse;
        }
    }
