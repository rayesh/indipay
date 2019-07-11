<?php namespace Softon\Indipay;

interface PaymentGatewayHelperInterface {
    public function getDefaultPaymentGateway();
    public function getPaymentGatewayDetails();
    public function paymentGatewayTransactionLogging($parameters,$pgTransactionId);
    public function getBookingId($pgTransactionId);
}