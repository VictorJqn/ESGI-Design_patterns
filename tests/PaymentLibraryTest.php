<?php 

use PHPUnit\Framework\TestCase;
use PaymentLibrary\Payments\PaymentFactory;
use PaymentLibrary\Transactions\TransactionManager;

class PaymentLibraryTest extends TestCase {
    public function testStripePayment() {
        $config = [
            'api_key' => 'test-api-key',
            // Autres configurations nécessaires pour Stripe
        ];

        $payment = PaymentFactory::createPayment('stripe', $config);
        $transactionManager = new TransactionManager($payment);
        $result = $transactionManager->processTransaction(100.00, 'USD', 'Test transaction');
        $this->assertEquals('success', $result); // En supposant que la transaction retourne 'success'
    }
}
