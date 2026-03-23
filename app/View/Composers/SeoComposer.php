<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class SeoComposer extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        '*',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'schemaMarkup' => $this->generateSchema(),
        ];
    }

    /**
     * Generate basic Schema.org JSON-LD.
     *
     * @return string
     */
    protected function generateSchema()
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'url' => home_url(),
        ], JSON_UNESCAPED_SLASHES);
    }
}
