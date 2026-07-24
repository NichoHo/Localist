<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-display text-xl font-bold text-ink">Confirm your password</h1>
        <p class="mt-1 text-sm leading-relaxed text-ink-muted">{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">{{ __('Confirm') }}</x-primary-button>
    </form>
</x-guest-layout>
