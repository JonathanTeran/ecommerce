<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Wishlist;
use Livewire\Component;

class WishlistButton extends Component
{
    public Product $product;

    public bool $isWishlisted = false;

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->checkWishlistStatus();
    }

    public function checkWishlistStatus()
    {
        if (auth()->check()) {
            $this->isWishlisted = Wishlist::where('user_id', auth()->id())
                ->where('product_id', $this->product->id)
                ->exists();
        }
    }

    public function toggleWishlist()
    {
        if (! auth()->check()) {
            return redirect()->to('/admin/login');
        }

        if ($this->isWishlisted) {
            Wishlist::where('user_id', auth()->id())
                ->where('product_id', $this->product->id)
                ->delete();
            $this->isWishlisted = false;
        } else {
            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $this->product->id,
            ]);
            $this->isWishlisted = true;
        }
    }

    public function render()
    {
        return view('livewire.wishlist-button');
    }
}
