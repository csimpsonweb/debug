<?php
namespace Networld\Debug\Plugin;

use Networld\Debug\Logger\OrderEmailLogger;

class TransportTemplateVarsLogger
{
    private OrderEmailLogger $logger;
    private array $latestVars = [];

    public function __construct(OrderEmailLogger $logger)
    {
        $this->logger = $logger;
    }

    public function beforeSetTemplateVars(
        \Magento\Framework\Mail\Template\TransportBuilder $subject,
        array $templateVars
    ) {
        try {
            $this->logger->debug('TransportTemplateVarsLogger: plugin invoked');

            $logData = [
                'template_var_keys' => array_keys($templateVars),
            ];

            if (isset($templateVars['order']) && is_object($templateVars['order'])) {
                $order = $templateVars['order'];
                $logData['order_id'] = $order->getIncrementId();
                $logData['shipping_method'] = $order->getShippingDescription();
                $logData['grand_total'] = $order->getGrandTotal();
            }

            if (isset($templateVars['delivery_date'])) {
                $logData['delivery_date'] = $templateVars['delivery_date'];
            }

            $this->logger->debug('Email template vars (TransportBuilder)', $logData);
        } catch (\Throwable $e) {
            $this->logger->error("Error in TransportTemplateVarsLogger: " . $e->getMessage());
        }

        return [$templateVars];
    }
}
