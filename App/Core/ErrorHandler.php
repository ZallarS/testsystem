<?php

    namespace App\Core;

    class ErrorHandler
    {
        private static $logPath;
        private static $environment;

        public static function register()
        {
            self::$logPath = STORAGE_PATH . '/logs';
            self::$environment = $_ENV['APP_ENV'] ?? 'production';

            // Create logs directory if it doesn't exist
            if (!is_dir(self::$logPath)) {
                mkdir(self::$logPath, 0755, true);
            }

            set_error_handler([self::class, 'handleError']);
            set_exception_handler([self::class, 'handleException']);
            register_shutdown_function([self::class, 'handleShutdown']);
        }

        public static function handleError($level, $message, $file = '', $line = 0)
        {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        }

        public static function handleException($exception)
        {
            $errorId = uniqid('err_', true);

            // Log the error
            self::logException($exception, $errorId);

            // Send appropriate response
            self::sendErrorResponse($exception, $errorId);

            exit(1);
        }

        public static function handleShutdown()
        {
            $error = error_get_last();

            if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
                self::handleException(new \ErrorException(
                    $error['message'], 0, $error['type'], $error['file'], $error['line']
                ));
            }
        }

        private static function logException($exception, $errorId)
        {
            $logMessage = sprintf(
                "[%s] Error ID: %s - %s in %s:%d\nStack trace:\n%s\n\n",
                date('Y-m-d H:i:s'),
                $errorId,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString()
            );

            $logFile = self::$logPath . '/error-' . date('Y-m-d') . '.log';

            error_log($logMessage, 3, $logFile);

            // Also log to system log for critical errors
            if ($exception instanceof \ErrorException && in_array($exception->getSeverity(), [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
                error_log("Critical error [{$errorId}]: " . $exception->getMessage());
            }
        }

        private static function sendErrorResponse($exception, $errorId)
        {
            http_response_code(500);

            if (self::$environment === 'development') {
                // Detailed error for development
                self::renderDebugError($exception, $errorId);
            } else {
                // Generic error for production
                self::renderProductionError($errorId);
            }
        }

        private static function renderDebugError($exception, $errorId)
        {
            $message = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
            $file = htmlspecialchars($exception->getFile(), ENT_QUOTES, 'UTF-8');
            $line = $exception->getLine();
            $trace = htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8');

            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>Error</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; }
                    .error-container { border: 1px solid #e74c3c; padding: 20px; border-radius: 5px; }
                    .error-title { color: #e74c3c; margin-top: 0; }
                    .error-id { background: #f8f9fa; padding: 5px; border-radius: 3px; font-family: monospace; }
                    .stack-trace { background: #f8f9fa; padding: 15px; border-radius: 3px; white-space: pre-wrap; font-family: monospace; }
                </style>
            </head>
            <body>
                <div class='error-container'>
                    <h1 class='error-title'>Error: {$message}</h1>
                    <p><strong>File:</strong> {$file}:{$line}</p>
                    <p><strong>Error ID:</strong> <span class='error-id'>{$errorId}</span></p>
                    <div class='stack-trace'>{$trace}</div>
                </div>
            </body>
            </html>";
        }

        private static function renderProductionError($errorId)
        {
            // Log the error ID for admin reference
            error_log("Production error ID: {$errorId}");

            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>Error</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; text-align: center; }
                    .error-container { max-width: 500px; margin: 0 auto; }
                    .error-title { color: #e74c3c; }
                </style>
            </head>
            <body>
                <div class='error-container'>
                    <h1 class='error-title'>Something went wrong</h1>
                    <p>We apologize for the inconvenience. Our team has been notified and is working to fix the issue.</p>
                    <p>Please try again later.</p>
                </div>
            </body>
            </html>";
        }

        public static function log($message, $level = 'info', $context = [])
        {
            $logFile = self::$logPath . '/app-' . date('Y-m-d') . '.log';

            $logMessage = sprintf(
                "[%s] %s: %s %s\n",
                date('Y-m-d H:i:s'),
                strtoupper($level),
                $message,
                !empty($context) ? json_encode($context) : ''
            );

            error_log($logMessage, 3, $logFile);
        }
    }