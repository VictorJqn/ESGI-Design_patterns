<?php

require 'vendor/autoload.php';

use PaymentLibrary\Payments\PaymentFactory;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$paymentIntent = PaymentFactory::createPayment("stripe", ['api_key' => $_ENV["STRIPE_API_KEY"]]);


$status = $paymentIntent->createTransaction(50.00, 'usd', 'Description de la transaction');

$paymentIntent->executeTransaction(); // Commenter l'exécution pour annuler la transaction si besoin !

$paymentIntent->cancelTransaction(); // Ne peut pas annuler une transaction si déjà exécutée 

$paymentIntentId = 'pi_XXXX';
$paymentIntent->deletePaymentIntent($paymentIntentId); // Uniquement si 1 : Pas en même temps que l'execute ni le cancel 2 : Donner un ID d'intent avec status incomplete

echo "Transaction Status: " . $paymentIntent->getStatus() . "\n";