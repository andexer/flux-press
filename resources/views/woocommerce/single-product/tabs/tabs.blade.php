@php
    if (! defined('ABSPATH')) {
        exit;
    }

    $productTabs = is_array($productTabs ?? null)
        ? $productTabs
        : apply_filters('woocommerce_product_tabs', []);

    $tabKeys = array_values(array_filter(array_keys($productTabs), static fn ($key) => is_string($key) && $key !== ''));
    $defaultTab = isset($tabKeys[0]) && is_string($tabKeys[0]) ? $tabKeys[0] : '';
@endphp

@if(! empty($productTabs))
    <div
        class="flux-single-tabs"
        x-data="{
            activeTab: '{{ esc_attr($defaultTab) }}',
            tabKeys: @js($tabKeys),
            isActive(key) {
                return this.activeTab === key;
            },
            setTab(key) {
                this.activeTab = key;
                if (!(window.history && typeof window.history.replaceState === 'function')) {
                    return;
                }

                window.history.replaceState({}, '', `#tab-${key}`);
            },
            syncFromHash() {
                const hash = String(window.location.hash || '');
                if (!hash.startsWith('#tab-')) {
                    return;
                }

                const next = hash.replace('#tab-', '');
                if (this.tabKeys.includes(next)) {
                    this.activeTab = next;
                }
            },
            init() {
                this.syncFromHash();
                if (!this.tabKeys.includes(this.activeTab)) {
                    this.activeTab = this.tabKeys[0] || '';
                }
            },
        }"
        x-init="init()"
        x-on:hashchange.window="syncFromHash()"
    >
        <div class="flex flex-wrap gap-2" role="tablist" aria-label="{{ esc_attr__('Paneles del producto', 'flux-press') }}">
            @foreach($productTabs as $key => $productTab)
                <flux:button
                    type="button"
                    variant="ghost"
                    id="tab-title-{{ esc_attr($key) }}"
                    role="tab"
                    x-on:click="setTab('{{ esc_js($key) }}')"
                    x-bind:aria-selected="isActive('{{ esc_js($key) }}')"
                    x-bind:tabindex="isActive('{{ esc_js($key) }}') ? '0' : '-1'"
                    aria-controls="tab-{{ esc_attr($key) }}"
                    class="flux-single-tabs__link"
                    x-bind:class="isActive('{{ esc_js($key) }}')
                        ? 'is-active'
                        : 'is-inactive'"
                >
                    {!! wp_kses_post(apply_filters("woocommerce_product_{$key}_tab_title", $productTab['title'], $key)) !!}
                </flux:button>
            @endforeach
        </div>

        <div class="mt-6 space-y-6">
            @foreach($productTabs as $key => $productTab)
                @php
                    $isDefaultTab = $key === $defaultTab;
                @endphp
                <div
                    class="woocommerce-Tabs-panel woocommerce-Tabs-panel--{{ esc_attr($key) }} panel entry-content flux-single-tabs__panel"
                    id="tab-{{ esc_attr($key) }}"
                    role="tabpanel"
                    aria-labelledby="tab-title-{{ esc_attr($key) }}"
                    x-show="isActive('{{ esc_js($key) }}')"
                    x-bind:aria-hidden="!isActive('{{ esc_js($key) }}')"
                    @if(! $isDefaultTab)
                        style="display:none;"
                    @endif
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                >
                    @if(isset($productTab['callback']))
                        @php call_user_func($productTab['callback'], $key, $productTab); @endphp
                    @endif
                </div>
            @endforeach
        </div>

        @php do_action('woocommerce_product_after_tabs'); @endphp
    </div>
@endif
