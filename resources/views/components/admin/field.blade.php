@props([
    'name',
    'label' => null,
    'hint' => null,
    'required' => false,
])

{{--
    Label + control + hint + validation error, in the order a person reads them.
    Pairs with @error() inside the slot, but also renders the error itself so a
    field can never look fine while the save is failing.

    <x-admin.field name="email" label="Email" required>
        <input type="email" name="email" value="{{ old('email') }}">
    </x-admin.field>
--}}

<div {{ $attributes->class(['admin-field']) }}>
    @if ($label)
        <label class="admin-label" for="{{ $name }}">
            {{ $label }}
            @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif
        </label>
    @endif

    {{ $slot }}

    @error($name)
        <p class="admin-error">
            <x-admin.icon name="warning" class="w-3.5 h-3.5" />
            {{ $message }}
        </p>
    @enderror

    @if ($hint && ! $errors->has($name))
        <p class="admin-hint">{{ $hint }}</p>
    @endif
</div>