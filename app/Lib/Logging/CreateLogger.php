<?php

namespace App\Lib\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;

class CreateLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param array $config
     * @return Logger
     * @throws \Exception
     */
    public function __invoke(array $config)
    {
        $this->level = env('LOG_MINIMUM_LEVEL', Logger::DEBUG);
        $logger = new Logger("TaGeSo");

        $format = "[%datetime%][UID:%extra.uid%]: %message% %extra.file%:%extra.line%".PHP_EOL;
        $formatter = new \Monolog\Formatter\LineFormatter($format, 'H:i:s', true);


        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushProcessor(new IntrospectionProcessor(Logger::DEBUG, array('Illuminate\\')));

        $streamHandler = new StreamHandler(storage_path('logs/lumen.log', Logger::DEBUG));
        $streamHandler->setFormatter($formatter);
        $logger->pushHandler($streamHandler);

        return $logger;
    }
}
