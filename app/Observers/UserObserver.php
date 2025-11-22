<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Log::info('User registered', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // TODO: Queue welcome email
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Re-verify email if changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            $user->saveQuietly();
            $user->sendEmailVerificationNotification();

            Log::info('User email changed, verification required', [
                'user_id' => $user->id,
                'old_email' => $user->getOriginal('email'),
                'new_email' => $user->email,
            ]);
        }

        Log::info('User updated', [
            'user_id' => $user->id,
            'changes' => $user->getChanges(),
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Log::info('User deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
