<?php

namespace App\Core;

class ErrorHandler
{
    public static function register()
    {
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
        http_response_code(500);

        $errorId = uniqid('err_', true);
        $errorMessage = $exception->getMessage();

        // Логируем с дополнительной информацией
        error_log("Error ID: {$errorId} - " . $exception->getMessage());
        error_log("File: " . $exception->getFile() . ":" . $exception->getLine());
        error_log("Stack trace: " . $exception->getTraceAsString());

        if ($_ENV['APP_ENV'] === 'production') {
            // НЕ показываем ID ошибки в production
            error_log("Production error: " . $exception->getMessage());
            echo "Произошла ошибка. Пожалуйста, попробуйте позже.";
        } else {
            // Детальная информация для разработки
            echo "<h1>Error: " . htmlspecialchars($errorMessage) . "</h1>";
            echo "<p>File: " . htmlspecialchars($exception->getFile()) . ":" . $exception->getLine() . "</p>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        }

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
}