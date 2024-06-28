<?php
namespace PaymentLibrary\Interfaces;

interface PaymentInterface {
    public function initialize(array $config);
    public function createTransaction(float $amount, string $currency, string $description);
    public function executeTransaction();
    public function cancelTransaction();
    public function getStatus();
}
