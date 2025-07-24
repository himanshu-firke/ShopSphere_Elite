<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanExpiredCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired guest carts';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $expirationHours = config('cart.guest_cart_expiration', 72);
        $expirationDate = now()->subHours($expirationHours);

        // Get expired cart IDs
        $expiredCartIds = DB::table('carts')
            ->whereNull('user_id')
            ->where('updated_at', '<', $expirationDate)
            ->pluck('id');

        if ($expiredCartIds->isEmpty()) {
            $this->info('No expired carts found.');
            return;
        }

        // Delete cart items first
        $deletedItems = DB::table('cart_items')
            ->whereIn('cart_id', $expiredCartIds)
            ->delete();

        // Delete carts
        $deletedCarts = DB::table('carts')
            ->whereIn('id', $expiredCartIds)
            ->delete();

        $this->info("Cleaned up {$deletedCarts} expired carts with {$deletedItems} items.");
    }
} 