<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-display text-xl font-bold text-ink">Log in to your account</h1>
        <p class="mt-1 text-sm text-ink-muted">Manage your business listing and leads.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-line text-brand focus:ring-brand/30" name="remember">
                <span class="ms-2 text-sm text-ink-muted">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-ink-muted transition hover:text-brand" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
            @endif
        </div>

        <x-primary-button class="w-full justify-center">{{ __('Log in') }}</x-primary-button>

        <p class="text-center text-sm text-ink-muted">
            Don&rsquo;t have an account?
            <a href="{{ route('register') }}" class="font-medium text-brand hover:underline">List your business</a>
        </p>
    </form>
</x-guest-layout>
