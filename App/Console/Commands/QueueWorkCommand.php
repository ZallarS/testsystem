<?php

    namespace App\Console\Commands;

    use App\Core\Console\Command;
    use App\Core\Queue\Queue;
    use App\Core\Queue\Worker;

    class QueueWorkCommand extends Command
    {
        protected $name = 'queue:work';
        protected $description = 'Start processing jobs from the queue';

        public function handle()
        {
            $this->info('Queue worker started. Press Ctrl+C to stop.');

            $queue = new Queue();
            $worker = new Worker($queue);

            // Handle graceful shutdown
            pcntl_signal(SIGINT, function() use ($worker) {
                $this->info('Shutting down queue worker...');
                $worker->stop();
            });

            $worker->work();

            $this->info('Queue worker stopped.');
        }
    }