@props([
    'routeName',
    'target' => null,
    'label' => 'Import Excel',
])

<form action="{{ route($routeName) }}" method="POST" enctype="multipart/form-data"
    @if($target) data-async-target="{{ $target }}" @endif
    data-async-reset="true"
    style="display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap;">
    @csrf
    <input type="file" name="import_file" accept=".xlsx,.xls,.csv" required
        style="max-width: 210px; font-size: 12px; color: #475569;">
    <button type="submit"
        style="background: #0f172a; color: white; border: none; padding: 8px 14px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer;">
        {{ $label }}
    </button>
</form>
