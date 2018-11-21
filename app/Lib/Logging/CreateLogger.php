<?php

namespace App\Lib\Logging;

use MattermostHandler\MattermostHandler;
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

        $format = "[%datetime%][UID:%extra.uid%][%level_name%]: %message% %extra.file%:%extra.line%".PHP_EOL;
        $formatter = new \Monolog\Formatter\LineFormatter($format, 'H:i:s', true);

        $uidProcessor = new \Monolog\Processor\UidProcessor();
        $logger->pushProcessor($uidProcessor);
        $logger->pushProcessor(new IntrospectionProcessor(Logger::DEBUG, array('Illuminate\\')));


        $streamHandler = new StreamHandler(storage_path('logs/lumen.log'), Logger::DEBUG);
        $streamHandler->setFormatter($formatter);
        #$logger->pushHandler($streamHandler);
        if (getenv("MATTERMOST_URL", false)) {
            $mattermostHandler = new MattermostHandler(
                getenv("MATTERMOST_URL"),
                Logger::WARNING
            );
            $mattermostHandler->setFormatter($formatter);
            $logger->pushHandler($mattermostHandler);
        }

        return $logger;
    }
}
