<?php

namespace App\Actions;

use App\Enums\PushCategory;
use App\Models\GroupVerse;
use App\Models\Insight;
use App\Models\User;
use App\Notifications\NewGroupInsight;
use Illuminate\Http\UploadedFile;

class SubmitInsight
{
    public function __construct(private StorePublicUpload $storePublicUpload, private NotifyGroupMembers $notifyMembers) {}

    /**
     * Share a member's reflection on a group's verse and push it to the
     * group's other members.
     */
    public function handle(GroupVerse $verse, User $author, string $body, ?UploadedFile $image = null): Insight
    {
        $insight = new Insight(['body' => $body]);
        $insight->user_id = $author->id;
        $insight->verseable_id = $verse->id;
        $insight->verseable_type = $verse->getMorphClass();

        if ($image) {
            $insight->image_path = $this->storePublicUpload->handle($image, 'insights');
        }

        $insight->save();

        $this->notifyMembers->handle(
            $verse->group,
            $author,
            PushCategory::Insights,
            new NewGroupInsight($author, $verse->group, $insight),
        );

        return $insight;
    }
}
