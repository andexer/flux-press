<?php

namespace App\View\Composers;

use App\Services\MenuService;
use Roots\Acorn\View\Composer;

class AppComposer extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'layouts.app',
        'sections.header',
        'sections.footer',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'logoUrl'       => $this->logoUrl(),
            'siteName'      => get_bloginfo('name', 'display'),
            'primaryMenu'   => MenuService::items('primary_navigation'),
        ];
    }

    /**
     * Resolve the custom logo URL.
     *
     * @return string|null
     */
    protected function logoUrl()
    {
        $logoId = get_theme_mod('custom_logo');
        return $logoId ? wp_get_attachment_image_url($logoId, 'full') : null;
    }
}
