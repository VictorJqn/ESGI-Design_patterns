<?php

require 'vendor/autoload.php';

use PaymentLibrary\Payments\StripePayment;

$stripePayment = new StripePayment();
$stripePayment->initialize(['api_key' => 'sk_test_51PWm9hRsDT6DNnBSgElAtIC4ycmk0zPTUZiyYPBTILwkfIZGsz8cf0lMz16v93Nee67JBUrJfjyZZCSzR5UggWAZ005oiF1Izb']);

$stripePayment->createTransaction(50.00, 'usd', 'Description de la transaction');
$transactionId = $stripePayment->executeTransaction();
echo "Transaction ID: " . $transactionId . "\n";
echo "Transaction Status: " . $stripePayment->getStatus() . "\n";