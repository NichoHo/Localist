<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-display text-xl font-bold text-ink">Verify your email</h1>
        <p class="mt-1 text-sm leading-relaxed text-ink-muted">{{ __('Thanks for signing up! Please verify your email by clicking the link we just sent you. If you did not receive it, we will gladly send another.') }}</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-lg bg-success-soft px-4 py-3 text-sm font-medium text-success-text">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>{{ __('Resend email') }}</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-ink-muted transition hover:text-brand">{{ __('Log out') }}</button>
        </form>
    </div>
</x-guest-layout>
