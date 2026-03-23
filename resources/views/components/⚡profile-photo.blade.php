<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithFileUploads;

    public $photo;

    /**
     * Update the photo when selected.
     */
    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:2048', // 2MB Max
        ]);

        $this->savePhoto();
    }

    /**
     * Save the uploaded photo to WordPress media library.
     */
    public function savePhoto()
    {
        if (!$this->photo) {
            return;
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $user_id = get_current_user_id();
        
        // Handle the sideload
        $file_array = [
            'name'     => $this->photo->getClientOriginalName(),
            'tmp_name' => $this->photo->getRealPath(),
        ];

        $attachment_id = media_handle_sideload($file_array, 0);

        if (!is_wp_error($attachment_id)) {
            // Delete old photo if exists
            $old_photo_id = get_user_meta($user_id, 'flux_profile_photo_id', true);
            if ($old_photo_id) {
                wp_delete_attachment($old_photo_id, true);
            }

            update_user_meta($user_id, 'flux_profile_photo_id', $attachment_id);
            $this->dispatch('profile-photo-updated');
            
            // Clear the temporary upload
            $this->photo = null;
        }
    }

    /**
     * Remove the custom profile photo.
     */
    public function removePhoto()
    {
        $user_id = get_current_user_id();
        $old_photo_id = get_user_meta($user_id, 'flux_profile_photo_id', true);
        
        if ($old_photo_id) {
            wp_delete_attachment($old_photo_id, true);
            delete_user_meta($user_id, 'flux_profile_photo_id');
            $this->dispatch('profile-photo-updated');
        }
    }

    #[Computed]
    public function avatarUrl()
    {
        $user_id = get_current_user_id();
        $photo_id = get_user_meta($user_id, 'flux_profile_photo_id', true);

        if ($photo_id) {
            return wp_get_attachment_image_url($photo_id, 'thumbnail');
        }

        return get_avatar_url($user_id, ['size' => 96]);
    }
}; ?>

<div class="relative group">
    <div class="relative z-10 shrink-0">
        {{-- Avatar Image --}}
        <img 
            src="{{ $this->avatarUrl }}" 
            alt="{{ wp_get_current_user()->display_name }}" 
            class="size-16 sm:size-20 rounded-full shadow-sm object-cover border-2 border-white dark:border-zinc-800 ring-1 ring-zinc-200 dark:ring-zinc-700 transition-opacity group-hover:opacity-75" 
        />

        {{-- Upload Overlay --}}
        <label class="absolute inset-0 flex items-center justify-center rounded-full bg-black/40 opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity">
            <flux:icon.camera class="size-6 text-white" />
            <input type="file" wire:model="photo" class="hidden" accept="image/*" />
        </label>

        {{-- Loading Spinner --}}
        <div wire:loading wire:target="photo" class="absolute inset-0 flex items-center justify-center rounded-full bg-black/20">
            <flux:icon.arrow-path class="size-6 text-white animate-spin" />
        </div>
    </div>

    {{-- Remove Photo Option (if custom photo exists) --}}
    @if(get_user_meta(get_current_user_id(), 'flux_profile_photo_id', true))
        <button 
            wire:click="removePhoto" 
            class="absolute -top-1 -right-1 z-20 p-1 rounded-full bg-red-500 text-white shadow-sm opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
            title="{{ __('Eliminar foto', 'flux-press') }}"
        >
            <flux:icon.x-mark class="size-3" />
        </button>
    @endif
</div>
