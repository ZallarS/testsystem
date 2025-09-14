<?php

    namespace App\Console\Commands;

    use App\Console\Command;
    use App\Core\Database\Migrator;


    class PluginInstallCommand extends Command
    {
        protected $name = 'plugin:install';
        protected $description = 'Install a plugin from a package';

        public function execute($input, $output)
        {
            $package = $input[2] ?? null;
            if (!$package) {
                $this->write("Usage: php console plugin:install <package>", $output);
                return 1;
            }

            $container = Application::getContainer();
            $pluginManager = $container->get('plugin_manager');

            try {
                // Логика установки плагина из пакета
                $this->write("Installing plugin from $package...", $output);

                // Временная заглушка
                throw new \Exception("Install functionality not implemented yet");

                $this->write("Plugin installed successfully.", $output);
                return 0;
            } catch (\Exception $e) {
                $this->write("Error: " . $e->getMessage(), $output);
                return 1;
            }
        }
    }