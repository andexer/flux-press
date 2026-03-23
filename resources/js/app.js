import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

document.addEventListener('livewire:init', () => {
  Livewire.on('wc-cart-fragments-refresh', () => {
    if (window.jQuery) {
      window.jQuery(document.body).trigger('wc_fragment_refresh');
      window.jQuery(document.body).trigger('added_to_cart');
    }
  });
});
