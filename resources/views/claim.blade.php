<x-public-layout :title="'Claim '.$business->name.' | Localist'" :noindex="true">
    <div class="mx-auto max-w-xl px-4 py-16 text-center">
        <h1 class="text-2xl font-bold">Claim {{ $business->name }}</h1>
        <p class="mt-3 text-gray-500 dark:text-gray-400">
            {{ $business->category->name }} · {{ $business->city->name }}<br>
            Claiming this listing links it to your account so you can edit its details, upload photos, and receive leads.
        </p>
        <form method="post" action="{{ route('claim.store', $business) }}" class="mt-8">
            @csrf
            <button class="rounded-md bg-teal-700 px-6 py-3 font-semibold text-white hover:bg-teal-800">
                This is my business, claim it
            </button>
        </form>
        <a href="{{ route('business', $business) }}" class="mt-4 inline-block text-sm text-gray-500 dark:text-gray-400 hover:text-teal-700 dark:hover:text-teal-400">Back to listing</a>
    </div>
</x-public-layout>
