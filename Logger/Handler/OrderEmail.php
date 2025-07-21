<?php
// File: app/code/Networld/Debug/Logger/Handler/OrderEmail.php
namespace Networld\Debug\Logger\Handler;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class OrderEmail extends Base
{
    /** @var string */
    protected $fileName = 'var/log/debug_order_email.log';
    /** @var int */
    protected $loggerType = Logger::DEBUG;
}
