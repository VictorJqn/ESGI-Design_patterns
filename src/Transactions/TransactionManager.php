<?php 

namespace PaymentLibrary\Transactions;

use PaymentLibrary\Interfaces\PaymentInterface;

class TransactionManager {
    private $payment;

    public function __construct(PaymentInterface $payment) {
        $this->payment = $payment;
    }

    public function processTransaction(float $amount, string $currency, string $description) {
        $this->payment->createTransaction($amount, $currency, $description);
        return $this->payment->executeTransaction();
    }

    public function cancelTransaction() {
        return $this->payment->cancelTransaction();
    }

    public function getTransactionStatus() {
        return $this->payment->getStatus();
    }
}
