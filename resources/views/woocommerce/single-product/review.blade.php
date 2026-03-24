@php
    if (! defined('ABSPATH')) {
        exit;
    }

    if (! isset($comment) || ! $comment instanceof \WP_Comment) {
        return;
    }

    $commentUserId = (int) $comment->user_id;
    $customPhotoId = $commentUserId > 0 ? (int) get_user_meta($commentUserId, 'flux_profile_photo_id', true) : 0;
    $customPhotoUrl = $customPhotoId > 0 ? wp_get_attachment_image_url($customPhotoId, 'thumbnail') : '';
    $avatarAlt = trim((string) get_comment_author($comment));
    $avatarHtml = '';

    if (is_string($customPhotoUrl) && $customPhotoUrl !== '') {
        $avatarHtml = sprintf(
            '<img src="%s" alt="%s" class="avatar avatar-72 photo" loading="lazy" decoding="async" />',
            esc_url($customPhotoUrl),
            esc_attr($avatarAlt)
        );
    } else {
        $avatarHtml = get_avatar($comment, 72, '', '', [
            'class' => 'avatar avatar-72 photo',
            'loading' => 'lazy',
            'decoding' => 'async',
        ]);
    }

    $isVerifiedOwner = function_exists('wc_review_is_from_verified_owner')
        ? wc_review_is_from_verified_owner($comment->comment_ID)
        : false;
@endphp

<li @php comment_class('flux-review-item-shell'); @endphp id="li-comment-{{ (int) $comment->comment_ID }}">
    <article id="comment-{{ (int) $comment->comment_ID }}" class="flux-review-item">
        <div class="flux-review-item__avatar" aria-hidden="true">
            {!! $avatarHtml !!}
        </div>

        <div class="flux-review-item__body">
            <header class="flux-review-item__header">
                <div class="flux-review-item__author-wrap">
                    <h3 class="flux-review-item__author">
                        {{ get_comment_author($comment) }}
                    </h3>

                    @if($isVerifiedOwner)
                        <span class="flux-review-item__badge">
                            {{ __('Compra verificada', 'flux-press') }}
                        </span>
                    @endif
                </div>

                <time class="flux-review-item__date" datetime="{{ esc_attr(get_comment_date('c', $comment)) }}">
                    {{ get_comment_date(wc_date_format(), $comment) }}
                </time>
            </header>

            <div class="flux-review-item__rating">
                @php woocommerce_review_display_rating($comment); @endphp
            </div>

            <div class="flux-review-item__content">
                @php do_action('woocommerce_review_before_comment_text', $comment); @endphp
                @php comment_text(); @endphp
                @php do_action('woocommerce_review_after_comment_text', $comment); @endphp
            </div>
        </div>
    </article>
