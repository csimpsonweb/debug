<?php
namespace Networld\Debug\Plugin;

use Networld\Debug\Logger\OrderEmailLogger;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Email\SenderBuilder;

class LogOrderEmailData
{
    private OrderEmailLogger $orderEmailLogger;
    private LoggerInterface $logger;

    public function __construct(
        OrderEmailLogger $orderEmailLogger,
        LoggerInterface $logger
    ) {
        $this->orderEmailLogger = $orderEmailLogger;
        $this->logger = $logger;
    }

    public function aroundSend(SenderBuilder $subject, callable $proceed)
    {
        try {
            $this->orderEmailLogger->debug('aroundSend called — skipping internal inspection');
        } catch (\Throwable $e) {
            $this->logger->error("aroundSend error: " . $e->getMessage());
        }

        return $proceed();
    }
}
