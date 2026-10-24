<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * Public, crawlable pages listed in the sitemap, as route name => [changefreq, priority].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const PUBLIC_ROUTES = [
        'root' => ['weekly', '1.0'],
        'accountability-partners' => ['monthly', '0.7'],
        'testimonials' => ['monthly', '0.7'],
        'hangout' => ['monthly', '0.6'],
        'support' => ['monthly', '0.6'],
        'manual' => ['monthly', '0.5'],
    ];

    /**
     * Private areas crawlers should stay out of.
     *
     * @var list<string>
     */
    private const DISALLOWED_PATHS = [
        '/admin',
        '/settings',
        '/home',
        '/give',
        '/groups',
        '/insights',
        '/notifications',
        '/onboarding',
        '/partners',
        '/marketplace',
        '/certificates',
        '/leaderboard',
        '/login',
        '/register',
    ];

    public function robots(): Response
    {
        $lines = ['User-agent: *'];

        foreach (self::DISALLOWED_PATHS as $path) {
            $lines[] = 'Disallow: '.$path;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }

    public function sitemap(): Response
    {
        $urls = collect(self::PUBLIC_ROUTES)->map(fn (array $meta, string $name) => sprintf(
            '<url><loc>%s</loc><changefreq>%s</changefreq><priority>%s</priority></url>',
            e($name === 'root' ? url('/') : route($name)),
            $meta[0],
            $meta[1],
        ));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .$urls->implode('')
            .'</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
