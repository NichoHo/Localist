<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-ink">{{ __('Profile') }}</h1>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6 px-4 py-10 sm:px-6 sm:py-12">
        <div class="surface-card p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
