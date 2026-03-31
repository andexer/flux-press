<?php

namespace App\Services;

class HomeSectionBlocksService
{
    public const SECTION_TYPES = [
        'hero' => [
            'name' => 'Hero',
            'icon' => 'photo',
            'fields' => ['title', 'subtitle', 'badge', 'badge_color', 'image', 'primary_label', 'primary_url', 'animation'],
        ],
        'features' => [
            'name' => 'Features',
            'icon' => 'cube',
            'fields' => ['title', 'items', 'animation', 'columns'],
        ],
        'stats' => [
            'name' => 'Stats',
            'icon' => 'chart-bar',
            'fields' => ['title', 'items', 'animation'],
        ],
        'cta' => [
            'name' => 'CTA',
            'icon' => 'megaphone',
            'fields' => ['title', 'description', 'button_text', 'button_url', 'bg_color', 'animation'],
        ],
        'testimonials' => [
            'name' => 'Testimonials',
            'icon' => 'chat-bubble-left-right',
            'fields' => ['title', 'items', 'animation'],
        ],
        'team' => [
            'name' => 'Team',
            'icon' => 'users',
            'fields' => ['title', 'items', 'animation'],
        ],
        'pricing' => [
            'name' => 'Pricing',
            'icon' => 'currency-dollar',
            'fields' => ['title', 'items', 'animation', 'highlight'],
        ],
        'faq' => [
            'name' => 'FAQ',
            'icon' => 'question-mark-circle',
            'fields' => ['title', 'items', 'animation'],
        ],
        'blog' => [
            'name' => 'Blog Posts',
            'icon' => 'newspaper',
            'fields' => ['title', 'limit'],
        ],
        'contact' => [
            'name' => 'Contact',
            'icon' => 'envelope',
            'fields' => ['title', 'email', 'phone', 'address'],
        ],
        'gallery' => [
            'name' => 'Gallery',
            'icon' => 'photo',
            'fields' => ['title', 'images'],
        ],
        'video' => [
            'name' => 'Video',
            'icon' => 'play',
            'fields' => ['title', 'video_url', 'poster'],
        ],
        'custom_html' => [
            'name' => 'Custom HTML',
            'icon' => 'code-bracket',
            'fields' => ['html_content'],
        ],
    ];

    public function getSections(): array
    {
        $json = get_theme_mod('home_custom_sections_json', '[]');
        $sections = json_decode($json, true);

        return is_array($sections) ? $sections : [];
    }

    public function saveSections(array $sections): bool
    {
        $sanitized = $this->sanitizeSections($sections);
        $json = wp_json_encode($sanitized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        set_theme_mod('home_custom_sections_json', is_string($json) ? $json : '[]');

        return true;
    }

    /**
     * Export sections to JSON file.
     */
    public function exportSections(): string
    {
        $sections = $this->getSections();
        $export = [
            'version' => '1.0',
            'exported_at' => current_time('mysql'),
            'sections' => $sections,
        ];

        return wp_json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Import sections from JSON.
     */
    public function importSections(string $json): array
    {
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return ['success' => false, 'message' => 'Invalid JSON format'];
        }

        if (isset($decoded['sections']) && is_array($decoded['sections'])) {
            $sections = $decoded['sections'];
        } elseif (isset($decoded[0]) && is_array($decoded[0])) {
            $sections = $decoded;
        } else {
            return ['success' => false, 'message' => 'No sections found in import data'];
        }

        foreach ($sections as &$section) {
            $section['id'] = 'section_'.uniqid();
            $section['order'] = array_search($section, $sections, true);
        }

        $this->saveSections($sections);

        return ['success' => true, 'count' => count($sections)];
    }

    public function addSection(string $type, array $data = []): array
    {
        $sections = $this->getSections();
        $sectionTypes = self::SECTION_TYPES;

        if (! isset($sectionTypes[$type])) {
            return ['success' => false, 'message' => 'Invalid section type'];
        }

        $newSection = [
            'id' => 'section_'.uniqid(),
            'type' => $type,
            'enabled' => true,
            'order' => count($sections),
            'data' => $this->getDefaultSectionData($type, $data),
        ];

        $sections[] = $newSection;
        $this->saveSections($sections);

        return ['success' => true, 'section' => $newSection];
    }

    public function updateSection(string $sectionId, array $data): bool
    {
        $sections = $this->getSections();

        foreach ($sections as &$section) {
            if ($section['id'] === $sectionId) {
                $section['data'] = array_merge($section['data'] ?? [], $data);
                $this->saveSections($sections);

                return true;
            }
        }

        return false;
    }

    public function deleteSection(string $sectionId): bool
    {
        $sections = $this->getSections();
        $sections = array_values(array_filter($sections, fn ($s) => $s['id'] !== $sectionId));

        foreach ($sections as $i => $section) {
            $sections[$i]['order'] = $i;
        }

        $this->saveSections($sections);

        return true;
    }

    public function reorderSections(array $order): bool
    {
        $sections = $this->getSections();
        $sectionMap = [];

        foreach ($sections as $section) {
            $sectionMap[$section['id']] = $section;
        }

        $newOrder = [];
        foreach ($order as $id) {
            if (isset($sectionMap[$id])) {
                $newOrder[] = $sectionMap[$id];
            }
        }

        foreach ($newOrder as $i => $section) {
            $newOrder[$i]['order'] = $i;
        }

        $this->saveSections($newOrder);

        return true;
    }

    public function toggleSection(string $sectionId, bool $enabled): bool
    {
        $sections = $this->getSections();

        foreach ($sections as &$section) {
            if ($section['id'] === $sectionId) {
                $section['enabled'] = $enabled;
                $this->saveSections($sections);

                return true;
            }
        }

        return false;
    }

    public function getDefaultSectionData(string $type, array $override = []): array
    {
        $defaults = [
            'hero' => [
                'title' => __('Your Amazing Title', 'flux-press'),
                'subtitle' => __('Describe your value proposition here', 'flux-press'),
                'badge' => '',
                'badge_color' => 'sky',
                'image' => '',
                'primary_label' => __('Get Started', 'flux-press'),
                'primary_url' => '#',
            ],
            'features' => [
                'title' => __('Why Choose Us', 'flux-press'),
                'items' => [
                    ['icon' => 'shield-check', 'title' => __('Feature 1', 'flux-press'), 'text' => __('Description of feature 1', 'flux-press')],
                    ['icon' => 'bolt', 'title' => __('Feature 2', 'flux-press'), 'text' => __('Description of feature 2', 'flux-press')],
                    ['icon' => 'chart-bar', 'title' => __('Feature 3', 'flux-press'), 'text' => __('Description of feature 3', 'flux-press')],
                ],
            ],
            'stats' => [
                'title' => '',
                'items' => [
                    ['value' => '100+', 'label' => __('Clients', 'flux-press')],
                    ['value' => '99%', 'label' => __('Uptime', 'flux-press')],
                    ['value' => '24/7', 'label' => __('Support', 'flux-press')],
                    ['value' => '5★', 'label' => __('Rating', 'flux-press')],
                ],
            ],
            'cta' => [
                'title' => __('Ready to Get Started?', 'flux-press'),
                'description' => __('Join thousands of satisfied customers today.', 'flux-press'),
                'button_text' => __('Contact Us', 'flux-press'),
                'button_url' => '#',
                'bg_color' => 'bg-slate-900',
            ],
            'testimonials' => [
                'title' => __('What Our Clients Say', 'flux-press'),
                'items' => [
                    ['quote' => __('Great service!', 'flux-press'), 'author' => __('John Doe', 'flux-press'), 'role' => 'CEO'],
                    ['quote' => __('Highly recommended!', 'flux-press'), 'author' => __('Jane Smith', 'flux-press'), 'role' => 'CTO'],
                ],
            ],
            'team' => [
                'title' => __('Meet Our Team', 'flux-press'),
                'items' => [
                    ['name' => __('Team Member 1', 'flux-press'), 'role' => __('Position', 'flux-press'), 'image' => ''],
                    ['name' => __('Team Member 2', 'flux-press'), 'role' => __('Position', 'flux-press'), 'image' => ''],
                ],
            ],
            'pricing' => [
                'title' => __('Our Pricing', 'flux-press'),
                'items' => [
                    ['name' => __('Basic', 'flux-press'), 'price' => '$9', 'features' => [__('Feature 1', 'flux-press'), __('Feature 2', 'flux-press')]],
                    ['name' => __('Pro', 'flux-press'), 'price' => '$29', 'features' => [__('Feature 1', 'flux-press'), __('Feature 2', 'flux-press'), __('Feature 3', 'flux-press')]],
                ],
            ],
            'faq' => [
                'title' => __('Frequently Asked Questions', 'flux-press'),
                'items' => [
                    ['question' => __('Question 1?', 'flux-press'), 'answer' => __('Answer 1', 'flux-press')],
                    ['question' => __('Question 2?', 'flux-press'), 'answer' => __('Answer 2', 'flux-press')],
                ],
            ],
            'blog' => [
                'title' => __('Latest from Blog', 'flux-press'),
                'limit' => 3,
            ],
            'contact' => [
                'title' => __('Get in Touch', 'flux-press'),
                'email' => 'hello@example.com',
                'phone' => '',
                'address' => '',
            ],
            'gallery' => [
                'title' => __('Gallery', 'flux-press'),
                'images' => [],
            ],
            'video' => [
                'title' => __('Watch Our Video', 'flux-press'),
                'video_url' => '',
                'poster' => '',
            ],
            'custom_html' => [
                'html_content' => '',
            ],
        ];

        $data = $defaults[$type] ?? [];

        return array_merge($data, $override);
    }

    protected function sanitizeSections(array $sections): array
    {
        $sanitized = [];
        $sectionTypes = array_keys(self::SECTION_TYPES);

        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }

            $type = sanitize_key($section['type'] ?? '');
            if (! in_array($type, $sectionTypes, true)) {
                continue;
            }

            $sanitized[] = [
                'id' => sanitize_text_field($section['id'] ?? 'section_'.uniqid()),
                'type' => $type,
                'enabled' => (bool) ($section['enabled'] ?? true),
                'order' => absint($section['order'] ?? 0),
                'data' => $this->sanitizeSectionData($type, $section['data'] ?? []),
            ];
        }

        usort($sanitized, fn ($a, $b) => $a['order'] - $b['order']);

        return $sanitized;
    }

    protected function sanitizeSectionData(string $type, array $data): array
    {
        $sanitized = [];

        switch ($type) {
            case 'hero':
                $sanitized['title'] = sanitize_text_field($data['title'] ?? '');
                $sanitized['subtitle'] = sanitize_textarea_field($data['subtitle'] ?? '');
                $sanitized['badge'] = sanitize_text_field($data['badge'] ?? '');
                $sanitized['badge_color'] = in_array($data['badge_color'] ?? 'sky', ['sky', 'lime', 'orange', 'cyan', 'violet', 'rose'], true) ? $data['badge_color'] : 'sky';
                $sanitized['image'] = esc_url_raw($data['image'] ?? '');
                $sanitized['primary_label'] = sanitize_text_field($data['primary_label'] ?? '');
                $sanitized['primary_url'] = esc_url_raw($data['primary_url'] ?? '');
                break;

            case 'features':
            case 'stats':
                $sanitized['title'] = sanitize_text_field($data['title'] ?? '');
                $sanitized['items'] = [];
                if (isset($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $item) {
                        if (is_array($item)) {
                            $sanitized['items'][] = [
                                'icon' => sanitize_text_field($item['icon'] ?? ''),
                                'title' => sanitize_text_field($item['title'] ?? ''),
                                'text' => sanitize_textarea_field($item['text'] ?? ''),
                                'value' => sanitize_text_field($item['value'] ?? ''),
                                'label' => sanitize_text_field($item['label'] ?? ''),
                            ];
                        }
                    }
                }
                break;

            case 'cta':
                $sanitized['title'] = sanitize_text_field($data['title'] ?? '');
                $sanitized['description'] = sanitize_textarea_field($data['description'] ?? '');
                $sanitized['button_text'] = sanitize_text_field($data['button_text'] ?? '');
                $sanitized['button_url'] = esc_url_raw($data['button_url'] ?? '');
                $sanitized['bg_color'] = sanitize_text_field($data['bg_color'] ?? 'bg-slate-900');
                break;

            case 'testimonials':
                $sanitized['title'] = sanitize_text_field($data['title'] ?? '');
                $sanitized['items'] = [];
                if (isset($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $item) {
                        if (is_array($item)) {
                            $sanitized['items'][] = [
                                'quote' => sanitize_textarea_field($item['quote'] ?? ''),
                                'author' => sanitize_text_field($item['author'] ?? ''),
                                'role' => sanitize_text_field($item['role'] ?? ''),
                            ];
                        }
                    }
                }
                break;

            case 'team':
                $sanitized['title'] = sanitize_text_field($data['title'] ?? '');
                $sanitized['items'] = [];
                if (isset($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $item) {
                        if (is_array($item)) {
                            $sanitized['items'][] = [
                                'name' => sanitize_text_field($item['name'] ?? ''),
                                'role' => sanitize_text_field($item['role'] ?? ''),
                                'image' => esc_url_raw($item['image'] ?? ''),
                            ];
                        }
                    }
                }
                break;

            case 'pricing':
                $sanitized['title'] = sanitize_text_field($data['title'] ?? '');
                $sanitized['items'] = [];
                if (isset($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $item) {
                        if (is_array($item)) {
                            $features = is_array($item['features'] ?? null)
                                ? array_map('sanitize_text_field', $item['features'])
                                : [];
                            $sanitized['items'][] = [
                                'name' => sanitize_text_field($item['name'] ?? ''),
                                'price' => sanitize_text_field($item['price'] ?? ''),
                                'features' => $features,
                            ];
                        }
                    }
                }
                break;

            case 'faq':
                $sanitized['title'] = sanitize_text_field($data['title'] ?? '');
                $sanitized['items'] = [];
                if (isset($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $item) {
                        if (is_array($item)) {
                            $sanitized['items'][] = [
                                'question' => sanitize_text_field($item['question'] ?? ''),
                                'answer' => sanitize_textarea_field($item['answer'] ?? ''),
                            ];
                        }
                    }
                }
                break;

            case 'blog':
                $sanitized['title'] = sanitize_text_field($data['title'] ?? '');
                $sanitized['limit'] = absint($data['limit'] ?? 3);
                break;

            case 'contact':
                $sanitized['title'] = sanitize_text_field($data['title'] ?? '');
                $sanitized['email'] = sanitize_email($data['email'] ?? '');
                $sanitized['phone'] = sanitize_text_field($data['phone'] ?? '');
                $sanitized['address'] = sanitize_textarea_field($data['address'] ?? '');
                break;

            case 'gallery':
                $sanitized['title'] = sanitize_text_field($data['title'] ?? '');
                $sanitized['images'] = [];
                if (isset($data['images']) && is_array($data['images'])) {
                    foreach ($data['images'] as $img) {
                        if (filter_var($img, FILTER_VALIDATE_URL)) {
                            $sanitized['images'][] = esc_url_raw($img);
                        }
                    }
                }
                break;

            case 'video':
                $sanitized['title'] = sanitize_text_field($data['title'] ?? '');
                $sanitized['video_url'] = esc_url_raw($data['video_url'] ?? '');
                $sanitized['poster'] = esc_url_raw($data['poster'] ?? '');
                break;

            case 'custom_html':
                $sanitized['html_content'] = wp_kses_post($data['html_content'] ?? '');
                break;

            default:
                $sanitized = $data;
        }

        return $sanitized;
    }
}
