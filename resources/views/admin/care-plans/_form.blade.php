{{--
    Shared create/edit body for a care plan.

    Expects: $carePlan (empty model on create).
    The page wraps this in its own <form> so action/method differ.
--}}

<div class="grid gap-5 lg:grid-cols-2">

    {{-- Left: identity + pricing --}}
    <div class="space-y-4">
        <x-admin.field name="name" label="Plan name" required
                       hint="Shown on the public site, e.g. “Care Plan — Basic”.">
            <input type="text" name="name" required maxlength="255"
                   value="{{ old('name', $carePlan->name) }}"
                   placeholder="e.g. Care Plan — Professional">
        </x-admin.field>

        <x-admin.field name="description" label="Description"
                       hint="What the monthly fee buys. Be specific — this is the sales pitch.">
            <textarea name="description" rows="5"
                      placeholder="e.g. Monthly hosting, backups, security patches and 4 support hours.">{{ old('description', $carePlan->description) }}</textarea>
        </x-admin.field>

        <x-admin.field name="price_tzs" label="Monthly price (TZS)" required
                       hint="Charged per month in Tanzanian shillings.">
            <input type="number" name="price_tzs" required min="0" step="1"
                   value="{{ old('price_tzs', $carePlan->price_tzs ?? 0) }}">
        </x-admin.field>

        <x-admin.field name="price_usd" label="Monthly price (USD)" required
                       hint="Charged per month in US dollars for foreign clients.">
            <input type="number" name="price_usd" required min="0" step="0.01"
                   value="{{ old('price_usd', $carePlan->price_usd ?? 0) }}">
        </x-admin.field>
    </div>

    {{-- Right: visibility --}}
    <div class="space-y-4">
        <x-admin.card title="Visibility" icon="eye" class="bg-gray-50">
            <div class="space-y-3">
                <label class="admin-checkbox">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $carePlan->active ?? true))>
                    Active — offer this plan on the public site
                </label>

                <label class="admin-checkbox">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $carePlan->is_featured))>
                    Featured — highlight this plan on the public site
                </label>
            </div>

            <p class="mt-4 text-xs admin-muted leading-relaxed">
                Inactive plans stay in this list for your records but are hidden from visitors.
                Featured plans are visually promoted on the pricing page — pick one you actually
                want to sell.
            </p>
        </x-admin.card>

        @if ($carePlan->exists)
            <x-admin.card title="Record" icon="info">
                <dl class="space-y-2.5 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="admin-muted">Created</dt>
                        <dd>{{ optional($carePlan->created_at)->format('j M Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="admin-muted">Last updated</dt>
                        <dd>{{ optional($carePlan->updated_at)->diffForHumans() ?? '—' }}</dd>
                    </div>
                </dl>
            </x-admin.card>
        @endif
    </div>
</div>