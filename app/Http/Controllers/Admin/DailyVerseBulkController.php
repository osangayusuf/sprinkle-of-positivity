<?php

namespace App\Http\Controllers\Admin;

use App\Actions\BulkSetDailyVerses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkSetDailyVersesRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class DailyVerseBulkController extends Controller
{
    /**
     * Show the bulk verse-setting form (grid + CSV import).
     */
    public function edit(): Response
    {
        $this->authorize('access-admin');

        return Inertia::render('admin/daily-verse/bulk');
    }

    /**
     * Set the global daily verse for many dates at once, either from a
     * submitted grid of rows or an uploaded CSV file.
     */
    public function store(BulkSetDailyVersesRequest $request, BulkSetDailyVerses $action): RedirectResponse
    {
        $rows = $request->hasFile('file')
            ? $this->parseCsv($request->file('file')->getRealPath())
            : $request->verseRows();

        $count = $action->handle($rows, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => trans_choice('{1} :count verse saved.|[2,*] :count verses saved.', $count, ['count' => $count])]);

        return to_route('admin.daily-verse.bulk.edit');
    }

    /**
     * Parse an uploaded CSV of date,reference,text rows.
     *
     * @return array<int, array{date: string, reference: string, text: string}>
     */
    private function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('Could not open the uploaded CSV file.');
        }

        $header = fgetcsv($handle);
        $isHeader = $header && strtolower((string) ($header[0] ?? '')) === 'date';

        if (! $isHeader) {
            rewind($handle);
        }

        while (($line = fgetcsv($handle)) !== false) {
            if (count($line) < 3) {
                continue;
            }

            $rows[] = [
                'date' => trim((string) $line[0]),
                'reference' => trim((string) $line[1]),
                'text' => trim((string) $line[2]),
            ];
        }

        fclose($handle);

        return $rows;
    }
}
