<?php

use Livewire\Component;

new class extends Component
{
    public int $productId;

    /**
     * @var array<int,array<string,mixed>>
     */
    public array $images = [];

    public function mount(int $productId): void
    {
        $this->productId = $productId;
        $this->images = $this->buildImages();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    protected function buildImages(): array
    {
        if (! function_exists('wc_get_product')) {
            return [$this->placeholderImage()];
        }

        $product = wc_get_product($this->productId);
        if (! $product) {
            return [$this->placeholderImage()];
        }

        $ids = array_values(array_unique(array_filter([
            $product->get_image_id(),
            ...$product->get_gallery_image_ids(),
        ])));

        $images = [];
        foreach ($ids as $attachmentId) {
            $image = $this->attachmentImage((int) $attachmentId, $product->get_name());
            if (! $image) {
                continue;
            }

            $images[] = $image;
        }

        return $images !== [] ? $images : [$this->placeholderImage($product->get_name())];
    }

    /**
     * @return array<string,mixed>|null
     */
    protected function attachmentImage(int $attachmentId, string $fallbackAlt = ''): ?array
    {
        if ($attachmentId <= 0) {
            return null;
        }

        $thumb = wp_get_attachment_image_url($attachmentId, 'woocommerce_thumbnail');
        $large = wp_get_attachment_image_url($attachmentId, 'woocommerce_single');
        $full = wp_get_attachment_image_url($attachmentId, 'full');

        if (! $large || ! $full) {
            return null;
        }

        $alt = trim((string) get_post_meta($attachmentId, '_wp_attachment_image_alt', true));
        if ($alt === '') {
            $alt = $fallbackAlt !== '' ? $fallbackAlt : __('Imagen del producto', 'sage');
        }

        return [
            'attachment_id' => $attachmentId,
            'thumb' => $thumb ?: $large,
            'large' => $large,
            'full' => $full,
            'alt' => $alt,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    protected function placeholderImage(string $fallbackAlt = ''): array
    {
        $placeholder = wc_placeholder_img_src('woocommerce_single');

        return [
            'attachment_id' => 0,
            'thumb' => wc_placeholder_img_src('woocommerce_thumbnail'),
            'large' => $placeholder,
            'full' => $placeholder,
            'alt' => $fallbackAlt !== '' ? $fallbackAlt : __('Producto sin imagen', 'sage'),
        ];
    }
};
?>

@php
    $galleryImages = array_values($images);
    $galleryLabel = __('Galeria del producto', 'sage');
    $previewLabel = __('Vista previa del producto', 'sage');
    $zoomLabel = __('Alternar zoom', 'sage');
    $fullImageLabel = __('Abrir imagen completa', 'sage');
    $focusModeLabel = __('Modo enfoque', 'sage');
    $closeFocusModeLabel = __('Cerrar enfoque', 'sage');
@endphp

<div
    x-data="fluxProductGallery({
        images: @js($galleryImages),
        galleryLabel: @js($galleryLabel),
    })"
    x-init="init()"
    x-on:keydown.escape.window="closeFocusMode()"
    x-on:keydown.right.window="if (focusMode) next()"
    x-on:keydown.left.window="if (focusMode) previous()"
    class="flux-product-gallery"
>
    <div class="flex flex-col gap-4 lg:grid lg:grid-cols-[5.5rem_minmax(0,1fr)] lg:items-start">
        <div class="order-2 lg:order-1">
            <div class="flex gap-2 overflow-x-auto pb-1 lg:grid lg:max-h-[44rem] lg:grid-cols-1 lg:overflow-y-auto lg:overflow-x-hidden lg:pb-0">
                <template x-for="(image, index) in displayImages" :key="`${image.full}-${index}`">
                    <button
                        type="button"
                        class="flux-product-gallery__thumb shrink-0"
                        :class="{ 'is-active': index === activeIndex }"
                        x-on:click="select(index)"
                        x-bind:aria-label="`${galleryLabel} ${index + 1}`"
                        x-bind:aria-pressed="index === activeIndex"
                    >
                        <img
                            x-bind:src="image.thumb"
                            x-bind:alt="image.alt"
                            loading="lazy"
                            class="h-full w-full object-cover"
                        />
                    </button>
                </template>
            </div>
        </div>

        <div class="order-1 lg:order-2 space-y-4">
            <div class="flux-product-gallery__stage">
                <div class="absolute left-3 top-3 z-10 flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-white/90 px-3 py-1 text-[11px] font-semibold text-zinc-700 shadow-sm ring-1 ring-black/5 backdrop-blur dark:bg-zinc-900/90 dark:text-zinc-200 dark:ring-white/10">
                        <span x-text="`${activeIndex + 1}/${displayImages.length}`"></span>
                    </span>
                </div>

                <div class="absolute right-3 top-3 z-10 flex items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-full bg-white/80 p-2.5 text-zinc-700 shadow-sm ring-1 ring-black/5 backdrop-blur transition-colors hover:text-accent-700 dark:bg-zinc-900/80 dark:text-zinc-200 dark:ring-white/10 dark:hover:text-accent-300"
                        aria-label="{{ $focusModeLabel }}"
                        x-on:click.stop="toggleFocusMode()"
                        x-bind:aria-pressed="focusMode"
                    >
                        <flux:icon.arrows-pointing-out x-show="!focusMode" class="size-4" />
                        <flux:icon.arrows-pointing-in x-show="focusMode" class="size-4" />
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-full bg-white/80 p-2.5 text-zinc-700 shadow-sm ring-1 ring-black/5 backdrop-blur transition-colors hover:text-accent-700 dark:bg-zinc-900/80 dark:text-zinc-200 dark:ring-white/10 dark:hover:text-accent-300"
                        aria-label="{{ $zoomLabel }}"
                        x-on:click.stop="toggleZoom()"
                        x-bind:aria-pressed="isZoomed()"
                    >
                        <flux:icon.magnifying-glass-plus x-show="!isZoomed()" class="size-4" />
                        <flux:icon.magnifying-glass-minus x-show="isZoomed()" class="size-4" />
                    </button>

                    <a
                        x-bind:href="currentImage.full"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center rounded-full bg-white/80 p-2.5 text-zinc-700 shadow-sm ring-1 ring-black/5 backdrop-blur transition-colors hover:text-accent-700 dark:bg-zinc-900/80 dark:text-zinc-200 dark:ring-white/10 dark:hover:text-accent-300"
                        aria-label="{{ $fullImageLabel }}"
                    >
                        <flux:icon.arrow-up-right class="size-4" />
                    </a>
                </div>

                <div
                    class="h-full w-full cursor-zoom-in overflow-hidden rounded-[1.5rem]"
                    :class="{ 'cursor-zoom-out': isZoomed() }"
                    x-on:click="toggleZoom()"
                    x-on:mousemove="onPointerMove($event)"
                    x-on:mouseleave="onPointerLeave()"
                    x-on:wheel.prevent="onWheel($event)"
                >
                    <img
                        x-bind:src="currentImage.large"
                        x-bind:alt="currentImage.alt"
                        aria-label="{{ $previewLabel }}"
                        class="h-full w-full object-contain transition-transform duration-200"
                        x-bind:style="zoomStyle(false)"
                    />
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <flux:text class="text-xs uppercase tracking-[0.25em] text-zinc-500 dark:text-zinc-400">
                    {{ __('Contenido visual (clic para zoom)', 'sage') }}
                </flux:text>

                <div class="flex items-center gap-2">
                    <flux:button type="button" variant="ghost" size="sm" icon="chevron-left" x-on:click="previous()" x-bind:disabled="displayImages.length <= 1" />
                    <flux:button type="button" variant="ghost" size="sm" icon="chevron-right" x-on:click="next()" x-bind:disabled="displayImages.length <= 1" />
                </div>
            </div>
        </div>
    </div>

    <div
        x-show="focusMode"
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-[120]"
        role="dialog"
        aria-modal="true"
        aria-label="{{ $focusModeLabel }}"
    >
        <div class="absolute inset-0 bg-zinc-950/92 backdrop-blur-sm" x-on:click="closeFocusMode()"></div>

        <div class="relative flex h-full flex-col">
            <div class="absolute inset-x-0 top-0 z-20 flex items-center justify-between gap-3 bg-linear-to-b from-black/60 to-transparent px-4 py-3 sm:px-6">
                <div class="min-w-0">
                    <p class="text-xs uppercase tracking-[0.28em] text-zinc-400">{{ __('Enfoque', 'sage') }}</p>
                    <h3 class="mt-1 truncate text-sm font-semibold text-white sm:text-base" x-text="currentImage.alt"></h3>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/10 p-2.5 text-white transition-colors hover:bg-white/20" x-on:click="previous()" aria-label="{{ __('Anterior', 'sage') }}">
                        <flux:icon.chevron-left class="size-4" />
                    </button>
                    <button type="button" class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/10 p-2.5 text-white transition-colors hover:bg-white/20" x-on:click="next()" aria-label="{{ __('Siguiente', 'sage') }}">
                        <flux:icon.chevron-right class="size-4" />
                    </button>
                    <button type="button" class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/10 p-2.5 text-white transition-colors hover:bg-white/20" x-on:click="toggleZoom()" aria-label="{{ $zoomLabel }}">
                        <flux:icon.magnifying-glass-plus x-show="!isZoomed()" class="size-4" />
                        <flux:icon.magnifying-glass-minus x-show="isZoomed()" class="size-4" />
                    </button>
                    <a
                        x-bind:href="currentImage.full"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/10 p-2.5 text-white transition-colors hover:bg-white/20"
                        aria-label="{{ $fullImageLabel }}"
                    >
                        <flux:icon.arrow-up-right class="size-4" />
                    </a>
                    <button type="button" class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/10 p-2.5 text-white transition-colors hover:bg-white/20" x-on:click="closeFocusMode()" aria-label="{{ $closeFocusModeLabel }}">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>
            </div>

            <div class="relative grid min-h-0 flex-1 gap-3 px-3 pb-3 pt-20 lg:grid-cols-[7rem_minmax(0,1fr)] lg:px-6 lg:pb-6">
                <div class="order-2 overflow-x-auto pb-1 lg:order-1 lg:overflow-y-auto lg:overflow-x-hidden">
                    <div class="flex gap-2 lg:grid lg:grid-cols-1">
                        <template x-for="(image, index) in displayImages" :key="`focus-${image.full}-${index}`">
                            <button
                                type="button"
                                class="flux-product-gallery__focus-thumb shrink-0"
                                :class="{ 'is-active': index === activeIndex }"
                                x-on:click="select(index)"
                                x-bind:aria-label="`${galleryLabel} ${index + 1}`"
                                x-bind:aria-pressed="index === activeIndex"
                            >
                                <img
                                    x-bind:src="image.thumb"
                                    x-bind:alt="image.alt"
                                    loading="lazy"
                                    class="h-full w-full object-cover"
                                />
                            </button>
                        </template>
                    </div>
                </div>

                <div class="order-1 min-h-0 lg:order-2">
                    <div
                        class="h-full w-full cursor-zoom-in overflow-hidden rounded-[1.25rem] bg-black/25"
                        :class="{ 'cursor-zoom-out': isZoomed() }"
                        x-on:click="toggleZoom()"
                        x-on:mousemove="onPointerMove($event)"
                        x-on:mouseleave="onPointerLeave()"
                        x-on:wheel.prevent="onWheel($event)"
                    >
                        <img
                            x-bind:src="currentImage.full || currentImage.large"
                            x-bind:alt="currentImage.alt"
                            class="h-full w-full object-contain transition-transform duration-150"
                            x-bind:style="zoomStyle(true)"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    <script data-navigate-once>
        window.fluxProductGallery = window.fluxProductGallery || function fluxProductGallery(config) {
            const initialImages = Array.isArray(config.images) ? config.images : [];

            return {
                baseImages: initialImages,
                displayImages: initialImages,
                activeIndex: 0,
                variationInserted: false,
                focusMode: false,
                zoomLevel: 1,
                zoomX: 50,
                zoomY: 50,
                galleryLabel: config.galleryLabel || 'Gallery',
                init() {
                    this.bindVariationForm();
                },
                get currentImage() {
                    return this.displayImages[this.activeIndex] ?? this.displayImages[0] ?? {
                        thumb: '',
                        large: '',
                        full: '',
                        alt: '',
                    };
                },
                select(index) {
                    if (index < 0 || index >= this.displayImages.length) {
                        return;
                    }

                    this.activeIndex = index;
                    this.resetZoom();
                },
                previous() {
                    if (this.displayImages.length <= 1) {
                        return;
                    }

                    this.activeIndex = (this.activeIndex - 1 + this.displayImages.length) % this.displayImages.length;
                    this.resetZoom();
                },
                next() {
                    if (this.displayImages.length <= 1) {
                        return;
                    }

                    this.activeIndex = (this.activeIndex + 1) % this.displayImages.length;
                    this.resetZoom();
                },
                updateBodyScrollLock() {
                    if (document && document.body) {
                        document.body.classList.toggle('overflow-hidden', this.focusMode);
                    }
                },
                isZoomed() {
                    return this.zoomLevel > 1.01;
                },
                openFocusMode() {
                    this.focusMode = true;
                    this.zoomLevel = 1.45;
                    this.updateBodyScrollLock();
                },
                closeFocusMode() {
                    this.focusMode = false;
                    this.updateBodyScrollLock();
                    this.resetZoom();
                },
                toggleFocusMode() {
                    if (this.focusMode) {
                        this.closeFocusMode();
                        return;
                    }

                    this.openFocusMode();
                },
                toggleZoom() {
                    if (this.isZoomed()) {
                        this.zoomLevel = 1;
                        this.zoomX = 50;
                        this.zoomY = 50;
                        return;
                    }

                    this.zoomLevel = this.focusMode ? 2.8 : 2.2;
                },
                onWheel(event) {
                    const delta = event.deltaY < 0 ? 0.24 : -0.24;
                    const maxZoom = this.focusMode ? 5.2 : 3.2;
                    const nextLevel = Math.min(maxZoom, Math.max(1, this.zoomLevel + delta));
                    this.zoomLevel = Number(nextLevel.toFixed(2));
                },
                onPointerMove(event) {
                    if (!this.isZoomed()) {
                        return;
                    }

                    const rect = event.currentTarget.getBoundingClientRect();
                    if (!rect.width || !rect.height) {
                        return;
                    }

                    const pointerX = ((event.clientX - rect.left) / rect.width) * 100;
                    const pointerY = ((event.clientY - rect.top) / rect.height) * 100;

                    this.zoomX = Math.max(0, Math.min(100, pointerX));
                    this.zoomY = Math.max(0, Math.min(100, pointerY));
                },
                onPointerLeave() {
                    if (!this.isZoomed()) {
                        this.zoomX = 50;
                        this.zoomY = 50;
                    }
                },
                zoomStyle(isFocusMode = false) {
                    const scale = this.zoomLevel;
                    const transition = this.isZoomed() ? 'transform 80ms linear' : 'transform 200ms ease';

                    return `transform: scale(${scale}); transform-origin: ${this.zoomX}% ${this.zoomY}%; transition: ${transition};`;
                },
                resetZoom() {
                    this.zoomLevel = 1;
                    this.zoomX = 50;
                    this.zoomY = 50;
                },
                syncVariationImage(variation) {
                    const image = variation && variation.image ? variation.image : null;
                    if (!image || !image.src) {
                        this.resetVariationImage();
                        return;
                    }

                    const matchingIndex = this.baseImages.findIndex((item) => {
                        return item.full === image.full_src
                            || item.large === image.src
                            || item.thumb === image.gallery_thumbnail_src;
                    });

                    if (matchingIndex >= 0) {
                        this.displayImages = [...this.baseImages];
                        this.variationInserted = false;
                        this.activeIndex = matchingIndex;
                        this.resetZoom();
                        return;
                    }

                    const variationImage = {
                        attachment_id: 0,
                        thumb: image.gallery_thumbnail_src || image.src,
                        large: image.src,
                        full: image.full_src || image.src,
                        alt: image.alt || image.title || this.currentImage.alt,
                    };

                    this.displayImages = [variationImage, ...this.baseImages];
                    this.variationInserted = true;
                    this.activeIndex = 0;
                    this.resetZoom();
                },
                resetVariationImage() {
                    this.displayImages = [...this.baseImages];
                    this.variationInserted = false;
                    this.activeIndex = 0;
                    this.resetZoom();
                },
                bindVariationForm() {
                    const productRoot = this.$el.closest('.product');
                    const form = productRoot ? productRoot.querySelector('form.variations_form') : null;

                    if (!form || !window.jQuery) {
                        return;
                    }

                    const $form = window.jQuery(form);
                    $form.off('.flux-gallery');
                    $form.on('found_variation.flux-gallery', (_, variation) => this.syncVariationImage(variation));
                    $form.on('reset_image.flux-gallery', () => this.resetVariationImage());
                },
            };
        };
    </script>
@endonce
