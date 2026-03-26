<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function orders()
    {
        $orders = auth()->user()->orders()
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('account.orders', compact('orders'));
    }

    public function orderShow(\App\Models\Order $order)
    {
        Gate::authorize('view', $order);

        $order->load('items');

        return view('account.order-show', compact('order'));
    }

    public function profile()
    {
        return view('account.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        auth()->user()->update($request->validated());

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        auth()->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }

    public function addresses()
    {
        $addresses = auth()->user()->addresses()->latest()->get();

        return view('account.addresses', compact('addresses'));
    }

    public function storeAddress(StoreAddressRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        if (! empty($validated['is_default'])) {
            auth()->user()->addresses()
                ->where('type', $validated['type'])
                ->update(['is_default' => false]);
        }

        Address::create($validated);

        return back()->with('success', 'Dirección agregada correctamente.');
    }

    public function destroyAddress(Address $address)
    {
        abort_unless($address->user_id === auth()->id(), 403);

        $address->delete();

        return back()->with('success', 'Dirección eliminada.');
    }

    public function wishlist()
    {
        $wishlistItems = auth()->user()->wishlists()
            ->with('product')
            ->latest()
            ->get();

        return view('account.wishlist', compact('wishlistItems'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
