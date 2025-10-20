<?php

    namespace App\Jobs;

    use App\Core\Queue\Job;

    class SendWelcomeEmail extends Job
    {
        public function handle($data)
        {
            $userId = $data['user_id'];
            // Logic to send welcome email
            error_log("Sending welcome email to user: {$userId}");

            // Simulate email sending
            sleep(2);
            error_log("Welcome email sent to user: {$userId}");
        }
    }