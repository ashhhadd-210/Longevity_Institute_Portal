{{--
    The Institute's logo, complete with its name, and the Portal's name. Present
    on every page: the collaborator Portal, the Admin area, sign-in and the
    public pages. Only the full logo from the Institute's branding set is used;
    the compass mark on its own is not part of that set (the Institute,
    October 2026).
--}}
@props(['size' => 'md'])
@php
    $img = $size === 'lg' ? 'h-20 w-auto' : 'h-14 w-auto';
    $text = $size === 'lg' ? 'text-lg' : 'text-base';
@endphp
<a href="{{ auth()->check() ? route('home') : url('/') }}" class="flex items-center gap-4" aria-label="The Longevity Compass, Collaborator Portal">
    <img src="{{ asset('images/longevity-compass-logo.svg') }}" alt="The Longevity Compass" class="{{ $img }}">
    <span class="border-l border-stone-300 pl-4 {{ $text }} font-semibold tracking-tight text-stone-800">Collaborator Portal</span>
</a>
