<?php
namespace App\Traits;

use Cake\Log\Log;
use Psr\Log\LogLevel;

trait LogTrait
{
    /**
     * Convenience method to write an error message to Log. See Log::write()
     * for more information on writing to logs.
     *
     * @param mixed $msg Log message.
     * @param string|array $context Additional log data relevant to this message.
     * @return bool Success of log write.
     */
    protected function error($msg, $context = [])
    {
        return Log::write(LogLevel::ERROR, $msg, $context);
    }

    /**
     * Convenience method to write an debug message to Log. See Log::write()
     * for more information on writing to logs.
     *
     * @param mixed $msg Log message.
     * @param string|array $context Additional log data relevant to this message.
     * @return bool Success of log write.
     */
    protected function debug($msg, $context = [])
    {
        return Log::write(LogLevel::DEBUG, $msg, $context);
    }

    /**
     * Convenience method to write an info message to Log. See Log::write()
     * for more information on writing to logs.
     *
     * @param mixed $msg Log message.
     * @param string|array $context Additional log data relevant to this message.
     * @return bool Success of log write.
     */
    protected function info($msg, $context = [])
    {
        return Log::write(LogLevel::INFO, $msg, $context);
    }
}
