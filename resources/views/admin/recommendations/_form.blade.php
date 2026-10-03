{{--
    Shared create/edit body for a recommendation mapping.

    Expects: $recommendation (empty model for create), $checks, $areas.
    The page wraps this in its own <form> so the action/method differ.
--}}

<div class="grid gap-5 lg:grid-cols-2">

    {{-- Left column: what failed and why it matters --}}
    <div class="space-y-4">
        <x-admin.field name="check_name" label="Failed check this maps from" required
                       hint="Must match the scanner check name exactly — that string is the join key between a failed scan and this pitch.">
            <input type="text" name="check_name" list="check-names" required
                   value="{{ old('check_name', $recommendation->check_name) }}"
                   placeholder="e.g. SSL certificate valid">
            {{-- $checks is a name => name map, so iterate the keys. --}}
            <datalist id="check-names">
                @foreach ($checks->keys() as $checkName)
                    <option value="{{ $checkName }}"></option>
                @endforeach
            </datalist>
        </x-admin.field>

        <x-admin.field name="area" label="Scanner area" required
                       hint="Which of the eight health areas this finding belongs to.">
            <select name="area" required>
                @foreach ($areas as $area)
                    <option value="{{ $area }}" @selected(old('area', $recommendation->area) === $area)>
                        {{ ucfirst($area) }}
                    </option>
                @endforeach
            </select>
        </x-admin.field>

        <x-admin.field name="finding_example" label="Example finding"
                       hint="What the scanner reports when this check fails. Shown to staff as evidence.">
            <textarea name="finding_example" rows="3"
                      placeholder="e.g. Page loads over HTTP with no valid SSL certificate">{{ old('finding_example', $recommendation->finding_example) }}</textarea>
        </x-admin.field>

        <x-admin.field name="consequence" label="Business consequence"
                       hint="Why the client should care — the 'so what' in plain language.">
            <textarea name="consequence" rows="3"
                      placeholder="e.g. Visitors see a browser warning and most will leave without contacting you.">{{ old('consequence', $recommendation->consequence) }}</textarea>
        </x-admin.field>
    </div>

    {{-- Right column: what we sell --}}
    <div class="space-y-4">
        <x-admin.field name="solution" label="Recommended solution" required
                       hint="The Oweru service or package to offer in response.">
            <textarea name="solution" rows="3" required
                      placeholder="e.g. SSL certificate installation and full HTTPS migration">{{ old('solution', $recommendation->solution) }}</textarea>
        </x-admin.field>

        <x-admin.field name="service_type" label="Service category" required
                       hint="Used to group pitches by what the client would be buying.">
            <input type="text" name="service_type" required
                   value="{{ old('service_type', $recommendation->service_type) }}"
                   placeholder="e.g. Website Development">
        </x-admin.field>

        <x-admin.field name="priority" label="Priority" required
                       hint="High priority pitches are surfaced first to staff.">
            <select name="priority" required>
                @foreach (['high' => 'High — always mention this', 'medium' => 'Medium — mention when relevant', 'low' => 'Low — optional upsell'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('priority', $recommendation->priority ?: 'medium') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-admin.field>

        @if ($recommendation->exists)
            <x-admin.field name="active" label="Status">
                <label class="admin-checkbox">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $recommendation->active))>
                    Active — include this mapping in scan results
                </label>
            </x-admin.field>
        @endif
    </div>
</div>