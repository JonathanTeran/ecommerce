<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Review;
use Livewire\Component;
use Livewire\WithPagination;

class ProductReviews extends Component
{
    use WithPagination;

    public Product $product;

    // Form fields
    public $rating = 5;

    public $comment = '';

    public $title = '';

    protected $rules = [
        'rating' => 'required|integer|min:1|max:5',
        'title' => 'required|string|max:100',
        'comment' => 'required|string|max:1000',
    ];

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function submitReview()
    {
        if (! auth()->check()) {
            return redirect()->to('/admin/login');
        }

        $this->validate();

        // Check if user already reviewed this product
        $existingReview = Review::where('user_id', auth()->id())
            ->where('product_id', $this->product->id)
            ->first();

        if ($existingReview) {
            $this->addError('check', __('You have already submitted a review for this product.'));

            return;
        }

        // Check for verified purchase STRICTLY
        $isVerified = \App\Models\Order::where('user_id', auth()->id())
            ->whereHas('items', function ($q) {
                $q->where('product_id', $this->product->id);
            })
            ->where('status', 'delivered')
            ->exists();

        if (! $isVerified) {
            $this->addError('check', __('Only users who have purchased and received this product can write a review.'));

            return;
        }

        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $this->product->id,
            'rating' => $this->rating,
            'title' => $this->title,
            'comment' => $this->comment,
            'is_verified_purchase' => true,
            'is_approved' => false, // Requires admin approval
        ]);

        $this->reset(['rating', 'title', 'comment']);
        session()->flash('message', __('Thank you! Your review has been submitted and is pending approval.'));
    }

    public function render()
    {
        $reviews = $this->product->reviews()
            ->approved()
            ->latest()
            ->paginate(5);

        $averageRating = $this->product->average_rating;
        $reviewsCount = $this->product->reviews_count;

        // Breakdown logic — single query instead of 5 separate count queries
        $breakdown = [];
        $ratingCounts = $reviewsCount > 0
            ? $this->product->reviews()->approved()
                ->selectRaw('rating, count(*) as total')
                ->groupBy('rating')
                ->pluck('total', 'rating')
            : collect();

        for ($i = 5; $i >= 1; $i--) {
            $count = $ratingCounts->get($i, 0);
            $breakdown[$i] = [
                'count' => $count,
                'percentage' => $reviewsCount > 0 ? ($count / $reviewsCount) * 100 : 0,
            ];
        }

        return view('livewire.product-reviews', [
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'reviewsCount' => $reviewsCount,
            'breakdown' => $breakdown,
        ]);
    }
}
