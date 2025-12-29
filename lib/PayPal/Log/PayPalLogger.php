<?php

namespace PayPal\Log;

use PayPal\Core\PayPalConfigManager;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Stringable;

class PayPalLogger extends AbstractLogger
{

    /**
     * @var array Indexed list of all log levels.
     */
    private $loggingLevels = array(
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG
    );

    /**
     * Configured Logging Level
     *
     * @var LogLevel $loggingLevel
     */
    private $loggingLevel;

    /**
     * Configured Logging File
     *
     * @var string
     */
    private $loggerFile;

    /**
     * Log Enabled
     *
     * @var bool
     */
    private $isLoggingEnabled;

    /**
     * Logger Name. Generally corresponds to class name
     *
     * @var string
     */
    private $loggerName;

    public function __construct($className)
    {
        $this->loggerName = $className;
        $this->initialize();
    }

   public function initialize(): void
    {
        $config = PayPalConfigManager::getInstance()->getConfigHashmap();
        if (!empty($config)) {
            $this->isLoggingEnabled = (isset($config['log.LogEnabled']) && $config['log.LogEnabled'] === '1');
            if ($this->isLoggingEnabled) {
                $this->loggerFile = !empty($config['log.FileName']) ? $config['log.FileName'] : ini_get('error_log');
                $loggingLevel = strtoupper($config['log.LogLevel'] ?? '');
                $this->loggingLevel = defined("\\Psr\\Log\\LogLevel::$loggingLevel") ?
                    constant("\\Psr\\Log\\LogLevel::$loggingLevel") :
                    LogLevel::INFO;
            }
        }
    }

    /**
     * PSR-3 compatible log method
     */
     public function log($level, Stringable|string $message, array $context = []): void
    {
        // If logging disabled, do nothing
        if (!$this->isLoggingEnabled) {
            return;
        }

        // Ensure the level is within configured levels
        if (array_search($level, $this->loggingLevels) <= array_search($this->loggingLevel, $this->loggingLevels)) {
            $messageString = $message instanceof Stringable ? (string)$message : $message;
            error_log(
                "[" . date('d-m-Y H:i:s') . "] " . $this->loggerName . " : " . strtoupper($level) . ": $messageString\n",
                3,
                $this->loggerFile
            );
        }
    }
}
