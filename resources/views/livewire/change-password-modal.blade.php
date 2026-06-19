<div>
    <x-filament::modal id="change-password-modal" width="md" alignment="center">
        <x-slot name="heading">
            Change Password
        </x-slot>

        <form wire:submit.prevent="submit" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-end gap-x-3">
                <x-filament::button 
                    color="gray" 
                    x-on:click="close"
                    type="button"
                >
                    Cancel
                </x-filament::button>

                <x-filament::button type="submit">
                    Save Changes
                </x-filament::button>
            </div>
        </form>
    </x-filament::modal>
</div>
