<?php

    namespace App\Console\Commands;

    use App\Console\Command;
    use App\Core\Database\Migrator;

    class PluginUpdateCommand extends Command
    {
        protected $name = 'plugin:update';
        protected $description = 'Update a plugin';

        public function execute($input, $output)
        {
            $pluginName = $input[2] ?? null;
            if (!$pluginName) {
                $this->write("Usage: php console plugin:update <plugin-name>", $output);
                return 1;
            }

            $container = Application::getContainer();
            $pluginManager = $container->get('plugin_manager');

            try {
                if ($pluginManager->updatePlugin($pluginName)) {
                    $this->write("Plugin $pluginName updated successfully.", $output);
                    return 0;
                } else {
                    $this->write("Failed to update plugin $pluginName.", $output);
                    return 1;
                }
            } catch (\Exception $e) {
                $this->write("Error: " . $e->getMessage(), $output);
                return 1;
            }
        }
    }