<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-display text-xl font-bold text-ink">Reset your password</h1>
        <p class="mt-1 text-sm leading-relaxed text-ink-muted">{{ __('Enter your email address and we will send you a link to choose a new password.') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">{{ __('Email password reset link') }}</x-primary-button>

        <p class="text-center text-sm text-ink-muted">
            <a href="{{ route('login') }}" class="transition hover:text-brand">&larr; Back to log in</a>
        </p>
    </form>
</x-guest-layout>
