<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight rotating file logger. No external deps.
 * Keeps 30 daily files under ROOT_PATH/logs/.
 */
class Logger
{
    private static ?self $instance = null;
    private string $dir;

    private function __construct()
    {
        $this->dir = defined('ROOT_PATH') ? ROOT_PATH . '/logs' : dirname(__DIR__, 2) . '/logs';
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
        $this->prune();
    }

    public static function get(): self
    {
        return self::$instance ??= new self();
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    private function write(string $level, string $message, array $context): void
    {
        $date    = date('Y-m-d');
        $time    = date('Y-m-d H:i:s');
        $file    = "{$this->dir}/app-{$date}.log";
        $ctx     = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line    = "[{$time}] {$level}: {$message}{$ctx}" . PHP_EOL;

        error_log($line, 3, $file);
    }

    /** Remove log files older than 30 days. */
    private function prune(): void
    {
        $cutoff = strtotime('-30 days');
        foreach (glob("{$this->dir}/app-*.log") ?: [] as $f) {
            if (filemtime($f) < $cutoff) {
                @unlink($f);
            }
        }
    }
}
