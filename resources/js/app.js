import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

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
