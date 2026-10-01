<?php

namespace App\Actions;

use App\Models\DailyVerse;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

class BulkSetDailyVerses
{
    public function __construct(private StorePublicUpload $storePublicUpload) {}

    /**
     * Set (or update) the global daily verse for each of the given dates.
     *
     * @param  array<int, array{date: string, reference: string, text: string, image?: ?UploadedFile}>  $rows
     */
    public function handle(array $rows, User $author): int
    {
        foreach ($rows as $row) {
            $date = Carbon::parse($row['date']);

            $verse = DailyVerse::query()
                ->whereDate('date', $date)
                ->first() ?? new DailyVerse;

            $verse->date = $date;
            $verse->reference = $row['reference'];
            $verse->text = $row['text'];
            $verse->created_by = $author->id;

            if (! empty($row['image'])) {
                $verse->image_path = $this->storePublicUpload->handle($row['image'], 'daily-verses');
            }

            $verse->save();
        }

        return count($rows);
    }
}
