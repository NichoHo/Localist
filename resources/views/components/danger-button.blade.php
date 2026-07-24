<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn bg-danger text-white shadow-card transition hover:opacity-90 focus-visible:ring-danger']) }}>
    {{ $slot }}
</button>
