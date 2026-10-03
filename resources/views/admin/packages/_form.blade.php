{{--
    Shared create/edit body for a service package.

    Expects: $package (empty model on create), $groups, $serviceLines.
    The page wraps this in its own <form> so action/method differ.
--}}

<div class="grid gap-5 lg:grid-cols-2">

    {{-- Left: identity + pricing --}}
    <div class="space-y-4">
        <x-admin.field name="name" label="Package name" required
                       hint="Shown on the public pricing page, e.g. “E-Commerce Starter”.">
            <input type="text" name="name" required
                   value="{{ old('name', $package->name) }}"
                   placeholder="e.g. E-Commerce Starter">
        </x-admin.field>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-admin.field name="group" label="Customer group" required
                           hint="Who this package is pitched to.">
                <select name="group" required>
                    @foreach ($groups as $key => $label)
                        <option value="{{ $key }}" @selected(old('group', $package->group) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </x-admin.field>

            <x-admin.field name="service_line_id" label="Service line"
                           hint="Optional grouping into a service line.">
                <select name="service_line_id">
                    <option value="">— None —</option>
                    @foreach ($serviceLines as $line)
                        <option value="{{ $line->id }}" @selected(old('service_line_id', $package->service_line_id) == $line->id)>
                            {{ $line->icon }} {{ $line->name }}
                        </option>
                    @endforeach
                </select>
            </x-admin.field>
        </div>

        <x-admin.field name="description" label="Description"
                       hint="What the client gets. This is the sales copy.">
            <textarea name="description" rows="4"
                      placeholder="What does the client get with this package?">{{ old('description', $package->description) }}</textarea>
        </x-admin.field>
    </div>

    {{-- Right: numbers + visibility --}}
    <div class="space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-1">
            <x-admin.field name="price_tzs" label="Price (TZS)" required
                           hint="Price in Tanzanian shillings.">
                <input type="number" name="price_tzs" required min="0" step="1"
                       value="{{ old('price_tzs', $package->price_tzs) }}">
            </x-admin.field>

            <x-admin.field name="price_usd" label="Price (USD)" required
                           hint="Price in US dollars for foreign clients.">
                <input type="number" name="price_usd" required min="0" step="0.01"
                       value="{{ old('price_usd', $package->price_usd) }}">
            </x-admin.field>

            <x-admin.field name="delivery_days" label="Delivery (days)" required
                           hint="How long delivery takes once started.">
                <input type="number" name="delivery_days" required min="1"
                       value="{{ old('delivery_days', $package->delivery_days ?? 7) }}">
            </x-admin.field>
        </div>

        <x-admin.card title="Visibility" icon="eye" class="bg-gray-50">
            <div class="space-y-3">
                <label class="admin-checkbox">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $package->active ?? true))>
                    Active — show this package on the public site
                </label>

                <label class="admin-checkbox">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $package->is_featured))>
                    Featured — highlight with the “Recommended” badge
                </label>
            </div>
        </x-admin.card>
    </div>
</div>