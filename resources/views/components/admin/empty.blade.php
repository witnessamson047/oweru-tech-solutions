@props([
    'icon' => 'inbox-empty',
    'title' => 'Nothing here yet',
    'text' => null,
    'colspan' => null,
])

{{--
    Empty state. Explain WHY it is empty and what to do next — a bare "No
    records" leaves staff unsure whether they broke something.

    Pass :colspan when used inside a <td> context, or omit and use it as a
    standalone block below a table.
--}}

@if ($colspan)
    <tr>
        <td colspan="{{ $colspan }}">
            <div class="admin-empty">
                <span class="admin-empty-icon"><x-admin.icon :name="$icon" /></span>
                <p class="admin-empty-title">{{ $title }}</p>
                @if ($text)<p class="admin-empty-text">{{ $text }}</p>@endif
                @if (! $slot->isEmpty())
                    <div class="mt-2">{{ $slot }}</div>
                @endif
            </div>
        </td>
    </tr>
@else
    <div class="admin-empty">
        <span class="admin-empty-icon"><x-admin.icon :name="$icon" /></span>
        <p class="admin-empty-title">{{ $title }}</p>
        @if ($text)<p class="admin-empty-text">{{ $text }}</p>@endif
        @if (! $slot->isEmpty())
            <div class="mt-2">{{ $slot }}</div>
        @endif
    </div>
@endif