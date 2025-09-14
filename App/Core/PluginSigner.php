<?php

    namespace App\Core;

    class PluginSigner
    {
        private $privateKey;
        private $publicKey;

        public function __construct()
        {
            $this->loadKeys();
        }

        private function loadKeys()
        {
            $privateKeyPath = STORAGE_PATH . 'plugin_private.key';
            $publicKeyPath = STORAGE_PATH . 'plugin_public.key';

            if (!file_exists($privateKeyPath) || !file_exists($publicKeyPath)) {
                $this->generateKeys();
            }

            $this->privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
            $this->publicKey = openssl_pkey_get_public(file_get_contents($publicKeyPath));
        }

        private function generateKeys()
        {
            $config = [
                "digest_alg" => "sha256",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            ];

            $keyPair = openssl_pkey_new($config);

            // Экспортируем приватный ключ
            openssl_pkey_export($keyPair, $privateKey);

            // Получаем публичный ключ
            $publicKey = openssl_pkey_get_details($keyPair);
            $publicKey = $publicKey["key"];

            file_put_contents(STORAGE_PATH . 'plugin_private.key', $privateKey);
            file_put_contents(STORAGE_PATH . 'plugin_public.key', $publicKey);

            $this->privateKey = $keyPair;
            $this->publicKey = openssl_pkey_get_public($publicKey);
        }

        public function signPlugin($pluginPath)
        {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pluginPath));
            $signatures = [];

            foreach ($files as $file) {
                if ($file->isDir() || $file->getExtension() !== 'php') {
                    continue;
                }

                $relativePath = str_replace($pluginPath . '/', '', $file->getPathname());
                $fileContent = file_get_contents($file->getPathname());
                $fileHash = hash('sha256', $fileContent);

                openssl_sign($fileHash, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
                $signatures[$relativePath] = base64_encode($signature);
            }

            file_put_contents($pluginPath . '/signature.sha256', json_encode($signatures, JSON_PRETTY_PRINT));
        }

        public function verifyPlugin($pluginPath)
        {
            $signatureFile = $pluginPath . '/signature.sha256';
            if (!file_exists($signatureFile)) {
                return false;
            }

            $signatures = json_decode(file_get_contents($signatureFile), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pluginPath));
            foreach ($files as $file) {
                if ($file->isDir() || $file->getExtension() !== 'php') {
                    continue;
                }

                $relativePath = str_replace($pluginPath . '/', '', $file->getPathname());
                if (!isset($signatures[$relativePath])) {
                    return false;
                }

                $fileContent = file_get_contents($file->getPathname());
                $fileHash = hash('sha256', $fileContent);
                $signature = base64_decode($signatures[$relativePath]);

                $result = openssl_verify($fileHash, $signature, $this->publicKey, OPENSSL_ALGO_SHA256);
                if ($result !== 1) {
                    return false;
                }
            }

            return true;
        }
    }