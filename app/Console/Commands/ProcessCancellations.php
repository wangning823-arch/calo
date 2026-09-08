<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AccountService;
use Illuminate\Console\Command;

class ProcessCancellations extends Command
{
    protected $signature = 'cancellations:process';

    protected $description = 'Process expired cancellation requests and delete user data';

    public function handle(AccountService $accountService): int
    {
        $expiredUsers = User::where('cancelled_at', '!=', null)
            ->where('cancellation_deadline', '<=', now())
            ->get();

        $this->info("Found {$expiredUsers->count()} expired cancellation requests.");

        foreach ($expiredUsers as $user) {
            try {
                $accountService->executeCancellation($user);
                $this->info("  Deleted user: {$user->phone}");
            } catch (\Exception $e) {
                $this->error("  Failed to delete user {$user->phone}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
