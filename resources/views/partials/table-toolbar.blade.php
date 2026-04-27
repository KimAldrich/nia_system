@php
    $action = $action ?? url()->current();
    $asyncTarget = $asyncTarget ?? null;
    $searchName = $searchName ?? null;
    $searchValue = $searchValue ?? ($searchName ? request($searchName) : '');
    $searchPlaceholder = $searchPlaceholder ?? 'Search table...';
    $filters = $filters ?? [];
    $resetKeys = collect($resetKeys ?? [])->filter()->values()->all();
    $preservedQuery = request()->except($resetKeys);
    $resetQuery = request()->except($resetKeys);
    $resetUrl = count($resetQuery) ? $action . '?' . http_build_query($resetQuery) : $action;
@endphp

<form
    action="{{ $action }}"
    method="GET"
    class="table-toolbar"
    @if ($asyncTarget)
        data-async-get="true"
        data-async-target="{{ $asyncTarget }}"
        data-async-preserve-scroll="true"
    @endif>
    @foreach ($preservedQuery as $key => $value)
        @if (is_array($value))
            @foreach ($value as $nestedValue)
                <input type="hidden" name="{{ $key }}[]" value="{{ $nestedValue }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    @if ($searchName)
        <label class="table-toolbar__search">
            <span class="table-toolbar__label">Search</span>
            <input
                type="search"
                name="{{ $searchName }}"
                value="{{ $searchValue }}"
                class="table-toolbar__input"
                placeholder="{{ $searchPlaceholder }}">
        </label>
    @endif

    @foreach ($filters as $filter)
        @php
            $filterName = $filter['name'] ?? null;
            $filterLabel = $filter['label'] ?? 'Filter';
            $filterValue = $filter['value'] ?? ($filterName ? request($filterName) : '');
            $filterOptions = $filter['options'] ?? [];
        @endphp
        @if ($filterName)
            <label class="table-toolbar__field">
                <span class="table-toolbar__label">{{ $filterLabel }}</span>
                <select name="{{ $filterName }}" class="table-toolbar__select">
                    @foreach ($filterOptions as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" {{ (string) $filterValue === (string) $optionValue ? 'selected' : '' }}>
                            {{ $optionLabel }}
                        </option>
                    @endforeach
                </select>
            </label>
        @endif
    @endforeach

    <div class="table-toolbar__actions">
        <button type="submit" class="table-toolbar__button table-toolbar__button--primary">Apply</button>
        <a href="{{ $resetUrl }}" class="table-toolbar__button table-toolbar__button--ghost">Reset</a>
    </div>
</form>
