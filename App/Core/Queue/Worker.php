<?php

    namespace App\Core\Queue;

    class Worker
    {
        private $queue;
        private $shouldStop = false;

        public function __construct(Queue $queue)
        {
            $this->queue = $queue;
        }

        public function work()
        {
            while (!$this->shouldStop) {
                $job = $this->queue->pop();

                if ($job) {
                    $this->process($job);
                } else {
                    sleep(1); // Wait for new jobs
                }
            }
        }

        public function stop()
        {
            $this->shouldStop = true;
        }

        private function process($job)
        {
            try {
                $jobClass = $job['job'];
                $data = $job['data'];

                if (is_string($jobClass) && class_exists($jobClass)) {
                    $instance = new $jobClass();
                    if (method_exists($instance, 'handle')) {
                        $instance->handle($data);
                    }
                } elseif (is_callable($jobClass)) {
                    call_user_func($jobClass, $data);
                }
            } catch (\Exception $e) {
                error_log("Queue job failed: " . $e->getMessage());
            }
        }
    }