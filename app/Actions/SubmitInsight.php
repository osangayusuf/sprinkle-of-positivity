<?php

namespace App\Actions;

use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class SubmitInsight
{
    /**
     * Share a member's reflection on a group's verse.
     */
    public function handle(GroupVerse $verse, User $author, string $body, ?UploadedFile $image = null): Insight
    {
        $insight = new Insight(['body' => $body]);
        $insight->user_id = $author->id;
        $insight->verseable_id = $verse->id;
        $insight->verseable_type = $verse->getMorphClass();

        if ($image) {
            $insight->image_path = $image->store('insights', 'public');
        }

        $insight->save();

        return $insight;
    }
}
