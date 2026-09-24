@props(['name'])
<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@if ($name === 'edit')
<path d="m16 3 5 5-12 12-6 1 1-6Z"/><path d="m14 5 5 5"/>
@elseif ($name === 'delete')
<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7M14 10v7"/>
@else
<path d="M10 4H4v16h6M14 8l4 4-4 4M8 12h13"/>
@endif
</svg>
