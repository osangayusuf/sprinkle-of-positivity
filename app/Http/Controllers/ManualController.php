<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Normalizer\TextNormalizerInterface;

class ManualController extends Controller
{
    private const SOURCE = 'markdown/user-manual.md';

    /**
     * The public user manual, rendered from resources/markdown/user-manual.md
     * so the page and the file never drift apart.
     */
    public function __invoke(): Response
    {
        $markdown = File::get(resource_path(self::SOURCE));

        return Inertia::render('manual', [
            'html' => $this->render($markdown),
            'contents' => $this->contents($markdown),
        ]);
    }

    private function render(string $markdown): string
    {
        // The page header already carries the document title.
        $markdown = preg_replace('/\A#\s+.*\R/', '', $markdown);

        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'apply_id_to_heading' => true,
                'insert' => 'none',
                'id_prefix' => '',
                'fragment_prefix' => '',
            ],
            'slug_normalizer' => ['instance' => $this->slugger()],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new TableExtension);
        $environment->addExtension(new HeadingPermalinkExtension);

        return (string) (new MarkdownConverter($environment))->convert($markdown);
    }

    /**
     * Part headings (h1) with the sections (h2) under them, for the side
     * navigation. Sections before the first part are grouped as "Overview".
     *
     * @return array<int, array{title: string, id: string, sections: array<int, array{title: string, id: string}>}>
     */
    private function contents(string $markdown): array
    {
        $slugger = $this->slugger();
        $contents = [['title' => 'Overview', 'id' => '', 'sections' => []]];
        $sawDocumentTitle = false;

        foreach (preg_split('/\R/', $markdown) as $line) {
            if (! preg_match('/^(#{1,2})\s+(.+)$/', $line, $match)) {
                continue;
            }

            $title = trim(str_replace(['*', '`'], '', $match[2]));
            $entry = ['title' => $title, 'id' => $slugger->normalize($title)];

            if ($match[1] === '#' && ! $sawDocumentTitle) {
                $sawDocumentTitle = true;

                continue;
            }

            if ($match[1] === '#') {
                $contents[] = [...$entry, 'sections' => []];

                continue;
            }

            $contents[array_key_last($contents)]['sections'][] = $entry;
        }

        return $contents;
    }

    /**
     * Anchors the way GitHub writes them ("Part 1 — Intro" becomes
     * "part-1--intro"), so links inside the markdown work in both places.
     */
    private function slugger(): TextNormalizerInterface
    {
        return new class implements TextNormalizerInterface
        {
            public function normalize(string $text, $context = null): string
            {
                $text = mb_strtolower($text);
                $text = preg_replace('/[^\p{L}\p{N}_\s-]/u', '', $text);

                return preg_replace('/\s/u', '-', trim($text));
            }
        };
    }
}
