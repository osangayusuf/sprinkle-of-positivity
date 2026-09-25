<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketplaceListingResource;
use App\Models\MarketplaceListing;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceController extends Controller
{
    /**
     * List every active marketplace listing.
     */
    public function index(): Response
    {
        return Inertia::render('marketplace/index', [
            'listings' => MarketplaceListingResource::collection(
                MarketplaceListing::active()->get()
            ),
        ]);
    }
}
