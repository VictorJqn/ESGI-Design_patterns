<?php

require 'vendor/autoload.php';

use PaymentLibrary\Payments\PaymentFactory;

$paymentIntent = PaymentFactory::createPayment("paypal", ['api_key' => 'sk_test_51NVse9HAfEtghEXw7eNEgTmNn2R3ImTAseFb9nYw8oN69pdM72u20FY7IzCp9OGC0OF35Dx5rLlV75zJkMJ2WpV6001MMP4hYD']);


$status = $paymentIntent->createTransaction(50.00, 'usd', 'Description de la transaction');
$paymentIntent->executeTransaction();
$paymentIntent->cancelTransaction();

$paymentIntentId = 'pi_3PX95XHAfEtghEXw1Pm3ofgH';
$paymentIntent->deletePaymentIntent($paymentIntentId);

echo "Transaction Status: " . $paymentIntent->getStatus() . "\n";