<?php
namespace Networld\Debug\Plugin;

use Psr\Log\LoggerInterface;
use Magento\Email\Model\Transport;
use Magento\Framework\Mail\EmailMessage;

class CaptureFinalEmail
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function beforeSendMessage(Transport $subject)
    {
        try {
            $message = $subject->getMessage();
            if ($message instanceof EmailMessage) {
                $to = array_map(function ($addr) {
                    return method_exists($addr, 'getEmail') ? $addr->getEmail() : (string)$addr;
                }, $message->getTo());

                $logData = [
                    'subject' => $message->getSubject(),
                    'to' => $to,
                    'message_class' => get_class($message)
                ];

                $this->logger->debug('EmailMessage object', $logData);
            } else {
                $this->logger->debug('Unknown message object class: ' . get_class($message));
            }
        } catch (\Throwable $e) {
            $this->logger->error("Error in CaptureFinalEmail: " . $e->getMessage());
        }
    }
}
