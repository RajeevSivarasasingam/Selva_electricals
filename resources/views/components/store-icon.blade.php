@props(['file', 'size' => 18])
<img src="{{ asset('images/figma/'.$file) }}" alt="" width="{{ $size }}" height="{{ $size }}" {{ $attributes->class(['icon']) }} aria-hidden="true">
