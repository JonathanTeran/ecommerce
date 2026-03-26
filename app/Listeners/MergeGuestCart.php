<?php

namespace App\Listeners;

use App\Models\Cart;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;

class MergeGuestCart
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // Get the current session ID
        $sessionId = Session::getId();

        // Find the guest cart associated with this session
        $guestCart = Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->first();

        // If a guest cart exists, assign it to the logged-in user
        // This method in the Cart model handles merging with any existing user cart
        if ($guestCart) {
            $guestCart->assignToUser($event->user);
        }
    }
}
