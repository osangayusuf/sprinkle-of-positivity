<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMarketplaceListingRequest;
use App\Http\Requests\Admin\UpdateMarketplaceListingRequest;
use App\Http\Resources\MarketplaceListingResource;
use App\Models\MarketplaceListing;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceController extends Controller
{
    /**
     * List every marketplace listing.
     */
    public function index(): Response
    {
        $this->authorize('access-admin');

        return Inertia::render('admin/marketplace/index', [
            'listings' => MarketplaceListingResource::collection(
                MarketplaceListing::query()->orderBy('position')->latest()->get()
            ),
        ]);
    }

    /**
     * Show the listing-creation form.
     */
    public function create(): Response
    {
        $this->authorize('access-admin');

        return Inertia::render('admin/marketplace/create');
    }

    /**
     * Create a marketplace listing.
     */
    public function store(StoreMarketplaceListingRequest $request): RedirectResponse
    {
        $listing = new MarketplaceListing($request->safe()->only([
            'title', 'description', 'cta_label', 'cta_url', 'position',
        ]));
        $listing->created_by = $request->user()->id;

        if ($request->hasFile('image')) {
            $listing->image_path = $request->file('image')->store('marketplace', 'public');
        }

        $listing->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Listing created.')]);

        return to_route('admin.marketplace.index');
    }

    /**
     * Show the listing-edit form.
     */
    public function edit(MarketplaceListing $listing): Response
    {
        $this->authorize('access-admin');

        return Inertia::render('admin/marketplace/edit', [
            'listing' => new MarketplaceListingResource($listing),
        ]);
    }

    /**
     * Update a marketplace listing.
     */
    public function update(UpdateMarketplaceListingRequest $request, MarketplaceListing $listing): RedirectResponse
    {
        $listing->fill($request->safe()->only([
            'title', 'description', 'cta_label', 'cta_url', 'position', 'starts_at', 'ends_at',
        ]));
        $listing->is_active = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $listing->image_path = $request->file('image')->store('marketplace', 'public');
        }

        $listing->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Listing updated.')]);

        return to_route('admin.marketplace.index');
    }

    /**
     * Remove a marketplace listing.
     */
    public function destroy(MarketplaceListing $listing): RedirectResponse
    {
        $this->authorize('access-admin');

        $listing->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Listing removed.')]);

        return back();
    }
}
