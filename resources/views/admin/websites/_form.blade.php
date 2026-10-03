{{--
    Shared create/edit body for a tracked website.

    Expects: $website (empty model on create).
    The page wraps this in its own <form> so action/method differ.
--}}

<div class="grid gap-5 lg:grid-cols-2">

    {{-- Left: the site identity --}}
    <div class="space-y-4">
        <x-admin.field name="business_name" label="Business name" required
                       hint="How the client is known — shown throughout the admin and on reports.">
            <input type="text" name="business_name" required maxlength="255"
                   value="{{ old('business_name', $website->business_name) }}"
                   placeholder="e.g. Demo Cafe">
        </x-admin.field>

        <x-admin.field name="url" label="Website address" required
                       hint="Must be unique and valid. https:// is added automatically if omitted.">
            <input type="text" name="url" inputmode="url" required
                   value="{{ old('url', $website->url) }}"
                   placeholder="e.g. abc.co.tz">
        </x-admin.field>

        <x-admin.field name="sector" label="Sector"
                       hint="Optional grouping for reporting, e.g. Hospitality, Retail, NGO.">
            <input type="text" name="sector" maxlength="255"
                   value="{{ old('sector', $website->sector) }}"
                   placeholder="e.g. Hospitality">
        </x-admin.field>
    </div>

    {{-- Right: what happens next --}}
    <div class="space-y-4">
        <x-admin.card title="What tracking does" icon="pulse" class="bg-gray-50">
            <ul class="space-y-3 text-sm text-gray-700">
                <li class="flex gap-2">
                    <x-admin.icon name="scan" class="mt-0.5 w-4 h-4 shrink-0" />
                    <span>Runs the health scanner against this address and scores it across the eight areas.</span>
                </li>
                <li class="flex gap-2">
                    <x-admin.icon name="clock" class="mt-0.5 w-4 h-4 shrink-0" />
                    <span>Keeps a history of every scan so you can show improvement over time.</span>
                </li>
                <li class="flex gap-2">
                    <x-admin.icon name="document-text" class="mt-0.5 w-4 h-4 shrink-0" />
                    <span>Feeds the report generator and pitch recommendations for outreach.</span>
                </li>
            </ul>
        </x-admin.card>
    </div>
</div>