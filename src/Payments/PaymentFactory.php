<?php

namespace PaymentLibrary\Payments;

use PaymentLibrary\Exceptions\PaymentException;

class PaymentFactory
    {
    public static function createPayment(string $type, array $config)
        {
        switch ($type) {
            case 'paypal':
                $payment = new PayPalPayment();
                break;
            case 'stripe':
                $payment = new StripePayment();
                break;
            default:
                throw new PaymentException("Type de paiement non supporté");
            }
        $payment->initialize($config);
        return $payment;
        }
    }
