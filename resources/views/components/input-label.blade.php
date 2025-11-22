@props(['value'])

<label {{ $attributes->merge(['class' => 'label']) }}>
    <span class="label-text text-base font-medium text-base-content">
        {{ $value ?? $slot }}
    </span>
</label>
