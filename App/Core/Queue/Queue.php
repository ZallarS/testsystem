<?php

    namespace App\Core\Queue;

    use App\Core\Cache;

    class Queue
    {
        private $name;
        private $storage;

        public function __construct($name = 'default')
        {
            $this->name = $name;
            $this->storage = new Cache();
        }

        public function push($job, $data = [], $delay = 0)
        {
            $queueItem = [
                'job' => $job,
                'data' => $data,
                'created_at' => time(),
                'delay' => $delay
            ];

            $queue = $this->getQueue();
            $queue[] = $queueItem;

            $this->storage->set("queue_{$this->name}", $queue, 86400); // 24 hours
        }

        public function pop()
        {
            $queue = $this->getQueue();

            foreach ($queue as $key => $item) {
                if ($item['delay'] <= 0) {
                    unset($queue[$key]);
                    $this->storage->set("queue_{$this->name}", array_values($queue), 86400);
                    return $item;
                } else {
                    $queue[$key]['delay']--;
                }
            }

            $this->storage->set("queue_{$this->name}", $queue, 86400);
            return null;
        }

        public function size()
        {
            return count($this->getQueue());
        }

        public function clear()
        {
            $this->storage->delete("queue_{$this->name}");
        }

        private function getQueue()
        {
            return $this->storage->get("queue_{$this->name}", []);
        }
    }