<?php

namespace App\Console\Commands;

use App\Services\SessionService;
use Illuminate\Console\Command;

class CleanupExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:cleanup-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired cart sessions from Redis and database';

    /**
     * Execute the console command.
     */
    public function handle(SessionService $sessionService): void
    {
        $this->info('Cleaning up expired cart sessions...');

        try {
            $sessionService->cleanupExpiredSessions();
            $this->info('Cart sessions cleaned up successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to clean up cart sessions: ' . $e->getMessage());
        }
    }
} 