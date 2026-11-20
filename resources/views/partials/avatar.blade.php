@php
    $avatarUser = $user ?? auth()->user();
    $avatarBox = $class ?? 'h-9 w-9 rounded-xl';
    $avatarFallback = $fallback ?? 'bg-brand-50 text-brand-700';
    $avatarText = $text ?? 'text-[11px]';
@endphp

@if ($avatarUser?->avatarUrl())
    <img src="{{ $avatarUser->avatarUrl() }}" alt="{{ $avatarUser->name }}"
        class="{{ $avatarBox }} shrink-0 object-cover ring-1 ring-black/10">
@else
    <span class="grid {{ $avatarBox }} shrink-0 place-items-center {{ $avatarFallback }} {{ $avatarText }} font-bold">
        {{ $avatarUser?->initials() }}
    </span>
@endif
