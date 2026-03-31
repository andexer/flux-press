import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

if (!window.fluxVisualBuilderPanel) {
  window.fluxVisualBuilderPanel = ({ initialOpen = false } = {}) => ({
    open: !!initialOpen,

    init() {
      this.syncPageState();
    },

    openPanel() {
      this.open = true;
      this.syncPageState();
    },

    closePanel() {
      this.open = false;
      this.syncPageState();
    },

    togglePanel() {
      this.open = !this.open;
      this.syncPageState();
    },

    syncPageState() {
      const html = document.documentElement;
      const body = document.body;

      if (!html || !body) {
        return;
      }

      html.classList.toggle('flux-home-builder-open', this.open);
      body.classList.toggle('overflow-hidden', this.open);
    },

  });
}

if (!window.fluxSectionSorter) {
  window.fluxSectionSorter = (wire) => ({
    draggingKey: null,

    start(event) {
      this.draggingKey = event.currentTarget?.dataset?.section ?? null;
      if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        if (this.draggingKey) {
          event.dataTransfer.setData('text/plain', this.draggingKey);
        }
      }
    },

    drop(event) {
      const targetKey = event.currentTarget?.dataset?.section ?? null;
      if (!this.draggingKey || !targetKey || this.draggingKey === targetKey) {
        return;
      }

      const currentOrder = Array.from(this.$refs.sectionList.querySelectorAll('[data-section]'))
        .map((element) => element.dataset.section)
        .filter((key) => typeof key === 'string' && key.length > 0);

      const fromIndex = currentOrder.indexOf(this.draggingKey);
      const toIndex = currentOrder.indexOf(targetKey);

      if (fromIndex < 0 || toIndex < 0) {
        this.draggingKey = null;
        return;
      }

      const nextOrder = [...currentOrder];
      const [moved] = nextOrder.splice(fromIndex, 1);
      nextOrder.splice(toIndex, 0, moved);
      wire.reorderSections(nextOrder);
      this.draggingKey = null;
    },

    end() {
      this.draggingKey = null;
    },
  });
}

if (!window.fluxSlideSorter) {
  window.fluxSlideSorter = (wire) => ({
    draggingKey: null,

    start(event) {
      this.draggingKey = event.currentTarget?.dataset?.slideKey ?? null;
      if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        if (this.draggingKey) {
          event.dataTransfer.setData('text/plain', this.draggingKey);
        }
      }
    },

    drop(event) {
      const targetKey = event.currentTarget?.dataset?.slideKey ?? null;
      if (!this.draggingKey || !targetKey || this.draggingKey === targetKey) {
        return;
      }

      const currentOrder = Array.from(this.$refs.slideList.querySelectorAll('[data-slide-key]'))
        .map((element) => element.dataset.slideKey)
        .filter((key) => typeof key === 'string' && key.length > 0);

      const fromIndex = currentOrder.indexOf(this.draggingKey);
      const toIndex = currentOrder.indexOf(targetKey);

      if (fromIndex < 0 || toIndex < 0) {
        this.draggingKey = null;
        return;
      }

      const nextOrder = [...currentOrder];
      const [moved] = nextOrder.splice(fromIndex, 1);
      nextOrder.splice(toIndex, 0, moved);
      wire.reorderHeroSlides(nextOrder);
      this.draggingKey = null;
    },

    end() {
      this.draggingKey = null;
    },
  });
}

const hasFluxHomeBuilderDrawer = () => document.querySelector('[data-flux-home-builder-drawer]') !== null;

const refreshHomeBuilderLivewireComponents = () => {
  if (!window.Livewire || typeof window.Livewire.find !== 'function') {
    return;
  }

  document
    .querySelectorAll('[wire\\:name=\"ecommerce-home-builder\"][wire\\:id]')
    .forEach((element) => {
      const id = element.getAttribute('wire:id');
      if (!id) {
        return;
      }

      const component = window.Livewire.find(id);
      if (!component || typeof component.call !== 'function') {
        return;
      }

      component.call('refreshBuilder');
    });
};

const initializeFluxBuilderBridge = () => {
  const drawerExists = hasFluxHomeBuilderDrawer();

  if (drawerExists) {
    try {
      const url = new URL(window.location.href);
      if (url.searchParams.get('flux_builder') === '1') {
        window.dispatchEvent(new CustomEvent('flux-home-builder:open'));
      }
    } catch (error) {
      // noop
    }
  }

  const builderLinks = document.querySelectorAll(
    '#wp-admin-bar-flux-visual-builder-live > a.ab-item',
  );

  builderLinks.forEach((link) => {
    if (link.dataset.fluxBuilderBound === '1') {
      return;
    }

    link.dataset.fluxBuilderBound = '1';
    link.addEventListener('click', (event) => {
      if (!hasFluxHomeBuilderDrawer()) {
        return;
      }

      try {
        const url = new URL(window.location.href);
        if (url.searchParams.get('flux_builder') !== '1') {
          event.preventDefault();
          url.searchParams.set('flux_builder', '1');
          window.location.assign(url.toString());
          return;
        }
      } catch (error) {
        // noop
      }

      event.preventDefault();
      window.dispatchEvent(new CustomEvent('flux-home-builder:toggle'));
    });
  });
};

const parseNumber = (value, fallback) => {
  const parsed = Number.parseFloat(value);
  return Number.isFinite(parsed) ? parsed : fallback;
};

const stepPrecision = (step) => {
  const value = String(step);
  if (!value.includes('.')) {
    return 0;
  }

  return value.split('.')[1].length;
};

const clamp = (value, min, max) => {
  let next = value;

  if (Number.isFinite(min)) {
    next = Math.max(min, next);
  }

  if (Number.isFinite(max)) {
    next = Math.min(max, next);
  }

  return next;
};

const updateQtyButtonsState = (control) => {
  if (!control) {
    return;
  }

  const input = control.querySelector('input.qty');
  if (!input) {
    return;
  }

  const minusButton = control.querySelector('.flux-cart-qty-btn--minus');
  const plusButton = control.querySelector('.flux-cart-qty-btn--plus');
  const min = parseNumber(input.getAttribute('min'), Number.NEGATIVE_INFINITY);
  const maxRaw = parseNumber(input.getAttribute('max'), Number.POSITIVE_INFINITY);
  const max = maxRaw > 0 ? maxRaw : Number.POSITIVE_INFINITY;
  const current = parseNumber(input.value, min || 0);

  if (minusButton) {
    minusButton.disabled = Number.isFinite(min) && current <= min;
  }

  if (plusButton) {
    plusButton.disabled = Number.isFinite(max) && current >= max;
  }
};

const adjustCartQuantity = (button) => {
  const control = button.closest('.flux-cart-qty-control');
  if (!control) {
    return;
  }

  const input = control.querySelector('input.qty');
  if (!input) {
    return;
  }

  const parsedStep = parseNumber(input.getAttribute('step'), 1);
  const step = parsedStep > 0 ? parsedStep : 1;
  const precision = stepPrecision(step);
  const min = parseNumber(input.getAttribute('min'), 0);
  const maxRaw = parseNumber(input.getAttribute('max'), Number.POSITIVE_INFINITY);
  const max = maxRaw > 0 ? maxRaw : Number.POSITIVE_INFINITY;
  const current = parseNumber(input.value, min);
  const direction = button.dataset.direction === 'minus' ? -1 : 1;
  const next = clamp(current + step * direction, min, max);

  if (next === current) {
    updateQtyButtonsState(control);
    return;
  }

  input.value = String(Number(next.toFixed(precision)));
  input.dispatchEvent(new Event('input', { bubbles: true }));
  input.dispatchEvent(new Event('change', { bubbles: true }));
  updateQtyButtonsState(control);
};

const initializeCartQtyControls = () => {
  document.querySelectorAll('.flux-cart-qty-control').forEach((control) => {
    updateQtyButtonsState(control);
  });
};

const initializeWooSingleProduct = () => {
  if (!window.jQuery) {
    return;
  }

  const $ = window.jQuery;

  $('.variations_form').each(function initializeVariationForm() {
    const $form = $(this);

    if (typeof $form.wc_variation_form !== 'function') {
      return;
    }

    $form.off('.wc-variation-form');
    $form.wc_variation_form();
    $form.trigger('check_variations');
  });

  $('#rating').each(function initializeRatingSelect() {
    const $rating = $(this);
    if ($rating.prev('.stars').length > 0) {
      return;
    }

    $rating.trigger('init');
  });
};

document.addEventListener('click', (event) => {
  const button = event.target.closest('.flux-cart-qty-btn');
  if (!button) {
    return;
  }

  event.preventDefault();
  adjustCartQuantity(button);
});

document.addEventListener('input', (event) => {
  const input = event.target.closest('.flux-cart-qty-control input.qty');
  if (!input) {
    return;
  }

  updateQtyButtonsState(input.closest('.flux-cart-qty-control'));
});

const initializeFrontInteractions = () => {
  initializeFluxBuilderBridge();
  initializeCartQtyControls();
  initializeWooSingleProduct();
};

document.addEventListener('livewire:navigated', initializeFrontInteractions);
document.addEventListener('DOMContentLoaded', initializeFrontInteractions);

document.addEventListener('livewire:init', () => {
  Livewire.on('wc-cart-fragments-refresh', () => {
    if (window.jQuery) {
      window.jQuery(document.body).trigger('wc_fragment_refresh');
      window.jQuery(document.body).trigger('added_to_cart');
    }

    initializeCartQtyControls();
  });
});

window.addEventListener('flux-home-builder:reload', () => {
  window.location.reload();
});

const handleFluxHomeBuilderRefresh = () => {
  refreshHomeBuilderLivewireComponents();
};

window.addEventListener('flux-home-builder:refresh', handleFluxHomeBuilderRefresh);
window.addEventListener('fluxHomeBuilderRefresh', () => {
  window.dispatchEvent(new CustomEvent('flux-home-builder:refresh'));
});
