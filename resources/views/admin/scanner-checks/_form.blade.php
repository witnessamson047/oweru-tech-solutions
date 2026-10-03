{{--
    Shared create/edit body for a scanner check.

    Expects: $scannerCheck (empty model on create), $areas.
    The page wraps this in its own <form> so action/method differ.
--}}

<div class="grid gap-5 lg:grid-cols-2">

    {{-- Left: identity --}}
    <div class="space-y-4">
        <x-admin.field name="name" label="Check name" required
                       hint="Human-readable name shown in scan results and reports.">
            <input type="text" name="name" required
                   value="{{ old('name', $scannerCheck->name) }}"
                   placeholder="e.g. SSL Certificate Valid">
        </x-admin.field>

        <x-admin.field name="slug" label="Slug" required
                       hint="Lowercase, hyphen-separated identifier used by the scanner, e.g. ssl-valid.">
            <input type="text" name="slug" required
                   value="{{ old('slug', $scannerCheck->slug) }}"
                   placeholder="e.g. ssl-valid">
        </x-admin.field>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-admin.field name="area" label="Area" required
                           hint="One of the eight health areas.">
                <select name="area" required>
                    <option value="">Select area</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area }}" @selected(old('area', $scannerCheck->area) === $area)>{{ $area }}</option>
                    @endforeach
                </select>
            </x-admin.field>

            <x-admin.field name="weight" label="Weight (points)" required
                           hint="Points awarded when this check passes (1–100).">
                <input type="number" name="weight" required min="1" max="100"
                       value="{{ old('weight', $scannerCheck->weight) }}"
                       placeholder="e.g. 10">
            </x-admin.field>
        </div>

        <x-admin.field name="description" label="Description"
                       hint="Brief note for staff on what this check measures.">
            <textarea name="description" rows="2"
                      placeholder="Brief description of what this check measures">{{ old('description', $scannerCheck->description) }}</textarea>
        </x-admin.field>
    </div>

    {{-- Right: client-facing wording + status --}}
    <div class="space-y-4">
        <x-admin.field name="wording_pass" label="Wording when passed"
                       hint="Shown to the client when this check passes.">
            <textarea name="wording_pass" rows="3"
                      placeholder="Message shown to the client when this check passes">{{ old('wording_pass', $scannerCheck->wording_pass) }}</textarea>
        </x-admin.field>

        <x-admin.field name="wording_fail" label="Wording when failed"
                       hint="Shown to the client when this check fails — lead with the consequence.">
            <textarea name="wording_fail" rows="3"
                      placeholder="Message shown to the client when this check fails">{{ old('wording_fail', $scannerCheck->wording_fail) }}</textarea>
        </x-admin.field>

        <x-admin.card title="Status" icon="check" class="bg-gray-50">
            <label class="admin-checkbox">
                <input type="hidden" name="enabled" value="0">
                <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $scannerCheck->enabled ?? true))>
                Enable this check
            </label>
            <p class="mt-3 text-xs admin-muted leading-relaxed">
                Disabled checks stay in the catalogue but are skipped by the scanner, so you can
                retire a rule without losing its history.
            </p>
        </x-admin.card>
    </div>
</div>