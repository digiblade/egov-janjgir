@props(['name' => 'arrow-up-right', 'class' => ''])

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
    @switch($name)
        @case('building')
            <path d="M4 20h16M6 20V5l6-2 6 2v15M9 8h.01M15 8h.01M9 12h.01M15 12h.01M10 20v-4h4v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('users')
            <path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20M10 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM16 4.2a3.5 3.5 0 0 1 0 6.8M17.5 15H19a3 3 0 0 1 3 3v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('file-text')
            <path d="M7 3.5h7l4 4V20H7a2 2 0 0 1-2-2V5.5a2 2 0 0 1 2-2ZM14 3.5v4h4M8.5 12h7M8.5 15.5h7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('book-open')
            <path d="M12 6.5C9.5 4.8 6.5 4.4 3.5 5.3v13c3-.9 6-.5 8.5 1.2m0-13C14.5 4.8 17.5 4.4 20.5 5.3v13c-3-.9-6-.5-8.5 1.2m0-13v13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('chart')
            <path d="M4 19.5h16M6.5 16V9.5M12 16V5M17.5 16v-3.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @case('image')
            <rect x="3.5" y="4.5" width="17" height="15" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="9" cy="10" r="1.5" stroke="currentColor" stroke-width="1.7"/><path d="m5 17 4.5-4 3 2.5 2.5-2 4 3.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            @break
        @default
            <path d="M7 17 17 7M8 7h9v9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    @endswitch
</svg>
