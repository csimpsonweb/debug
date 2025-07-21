<?php
namespace Networld\Debug\Plugin;

use Psr\Log\LoggerInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;

class CatchOrderPlaceException
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        callable $proceed,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        try {
            $this->logger->debug("DEBUG: entering savePaymentInformationAndPlaceOrder");
            return $proceed($cartId, $paymentMethod, $billingAddress);
        } catch (\Throwable $e) {
            $this->logger->critical('Place Order ERROR: ' . $e->getMessage());
            throw $e;
        }
    }
}
