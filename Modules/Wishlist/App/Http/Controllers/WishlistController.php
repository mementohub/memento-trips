<?php

namespace Modules\Wishlist\App\Http\Controllers;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

// ── Module Dependencies ─────────────────────────────────────────────────────
use Modules\TourBooking\App\Models\Service;
use Modules\Wishlist\App\Models\Wishlist;

/**
 * WishlistController
 *
 * Manages user wishlists — add/remove services, display saved items.
 *
 * @package Modules\Wishlist\App\Http\Controllers
 */
class WishlistController extends Controller
{
    /**
     * Display a listing of wishlisted items.
     *
     * Since the Ecommerce module was removed, this now
     * redirects to the service wishlist.
     */
    public function index()
    {
        return $this->serviceWishlist();
    }

    /**
     * Display wishlisted tour services.
     */
    public function serviceWishlist()
    {
        $wishlistIds = Wishlist::where('user_id', Auth::user()->id)
            ->where('wishable_type', Service::class)
            ->pluck('wishable_id')
            ->toArray();

        $services = Service::select('id', 'price_per_person', 'slug', 'location', 'is_featured', 'full_price', 'discount_price', 'is_new', 'duration', 'group_size')
            ->whereIn('id', $wishlistIds)
            ->withExists('myWishlist')
            ->where('status', true)
            ->with(['thumbnail:id,service_id,caption,file_path', 'translation:id,service_id,locale,title,short_description'])
            ->withCount('activeReviews')
            ->withAvg('activeReviews', 'rating')
            ->get();

        return view('wishlist::services-list', ['services' => $services]);
    }

    /**
     * Toggle a service in/out of the user's wishlist.
     */
    public function store(Request $request)
    {
        $user = Auth::guard('web')->user();

        $itemId = $request->item_id;

        // Only services are supported (Ecommerce module was removed)
        $modelClass = Service::class;

        // Check if item already in wishlist
        $existing = Wishlist::where('user_id', $user->id)
            ->where('wishable_id', $itemId)
            ->where('wishable_type', $modelClass)
            ->first();

        if (!$existing) {
            // Add to wishlist
            $wishlist = new Wishlist();
            $wishlist->user_id = $user->id;
            $wishlist->item_id = $itemId;
            $wishlist->wishable_id = $itemId;
            $wishlist->wishable_type = $modelClass;
            $wishlist->save();

            $notify_message = trans('translate.Item added to wishlist');
            return response()->json(['message' => $notify_message, 'type' => 'added']);
        } else {
            // Remove from wishlist
            $existing->delete();

            $notify_message = trans('translate.Item removed from wishlist');
            return response()->json(['message' => $notify_message, 'type' => 'removed']);
        }
    }

    /**
     * Remove the specified item from the user's wishlist.
     */
    public function destroy($id)
    {
        $user = Auth::guard('web')->user();

        Wishlist::where('user_id', $user->id)->where('item_id', $id)->delete();

        $notify_message = trans('translate.Item removed to wishlist');
        $notify_message = ['message' => $notify_message, 'alert-type' => 'success'];
        return redirect()->back()->with($notify_message);
    }
}