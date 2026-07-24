<x-public-layout :title="'Claim '.$business->name.' | Localist'" :noindex="true">
    <div class="mx-auto max-w-xl px-4 py-16 text-center sm:py-24">
        <div class="mx-auto mb-6 grid size-14 place-items-center rounded-2xl bg-brand-soft text-brand ring-1 ring-inset ring-brand-line">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="size-7" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
            </svg>
        </div>
        <h1 class="font-display text-2xl font-bold text-ink sm:text-3xl">Claim {{ $business->name }}</h1>
        <p class="mx-auto mt-3 max-w-md text-ink-muted">
            {{ $business->category->name }} · {{ $business->city->name }}<br>
            Claiming this listing links it to your account so you can edit its details, upload photos, and receive leads.
        </p>
        <form method="post" action="{{ route('claim.store', $business) }}" class="mt-8">
            @csrf
            <button class="btn btn-primary px-6 py-3 text-base">This is my business, claim it</button>
        </form>
        <a href="{{ route('business', $business) }}" class="mt-5 inline-block text-sm text-ink-muted transition hover:text-brand">Back to listing</a>
    </div>
</x-public-layout>
