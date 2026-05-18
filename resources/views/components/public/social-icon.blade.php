@php
    $network = \App\Support\SocialLinks::normalizeNetwork((string) ($network ?? 'website'));
    $class = $class ?? 'h-5 w-5';
@endphp

@switch($network)
    @case('facebook')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M14 8.5V6.7c0-.9.5-1.4 1.5-1.4H17V2.2C16.3 2.1 15.2 2 14.2 2 11.5 2 10 3.6 10 6.4v2.1H7v3.6h3V22h4v-9.9h2.7l.5-3.6H14Z" />
        </svg>
        @break

    @case('instagram')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="5" />
            <circle cx="12" cy="12" r="4" />
            <circle cx="17.3" cy="6.7" r="1" fill="currentColor" stroke="none" />
        </svg>
        @break

    @case('x')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
            <path d="M4 4l16 16" />
            <path d="M20 4L4 20" />
        </svg>
        @break

    @case('linkedin')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M6.9 9.3H3.4V21h3.5V9.3ZM5.2 3C4 3 3.2 3.8 3.2 4.9s.8 1.9 2 1.9 2-.8 2-1.9S6.4 3 5.2 3ZM21 14.5c0-3.4-1.8-5.4-4.6-5.4-1.5 0-2.6.7-3.2 1.7V9.3H9.8V21h3.5v-6.1c0-1.6.8-2.6 2.1-2.6 1.2 0 2.1.8 2.1 2.7v6H21v-6.5Z" />
        </svg>
        @break

    @case('youtube')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="2.8" y="6.2" width="18.4" height="11.6" rx="3" />
            <path d="M10 9.4v5.2L15 12l-5-2.6Z" fill="currentColor" stroke="none" />
        </svg>
        @break

    @case('tiktok')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M16.8 3c.3 2.4 1.7 4 4.2 4.2v3.6c-1.5 0-2.9-.4-4.2-1.2v5.5c0 3.6-2.4 5.9-5.9 5.9-3.2 0-5.5-2.1-5.5-5.1 0-3.2 2.8-5.4 6.2-4.9v3.5c-1.3-.4-2.7.3-2.7 1.5 0 1 .8 1.7 2 1.7 1.4 0 2.3-.9 2.3-2.7V3h3.6Z" />
        </svg>
        @break

    @case('pinterest')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12.2 2C6.7 2 3 5.9 3 10.5c0 2.8 1.5 5 3.8 5.9.4.1.7-.2.8-.5l.3-1.2c.1-.4 0-.5-.2-.8-.7-.8-1.1-1.8-1.1-3 0-3.1 2.3-5.5 5.8-5.5 3.2 0 5.3 1.9 5.3 4.8 0 3.6-1.8 5.9-4.2 5.9-1.3 0-2.3-.7-2.7-1.6l-.8 3.1c-.3 1.1-.9 2.3-1.5 3.2 1.1.7 2.4 1.1 3.8 1.1 5.5 0 9.8-4.3 9.8-10S17.7 2 12.2 2Z" />
        </svg>
        @break

    @case('whatsapp')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M17.5 14.4c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.1-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1-.3-.2-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6.1-.1.3-.4.5-.5.1-.2.2-.3.3-.5.1-.2.1-.4 0-.5-.1-.2-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.2.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.2-.3-.3-.6-.5ZM12.1 21.8a9.9 9.9 0 0 1-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.8 9.8 0 0 1 2.3 12C2.3 6.6 6.7 2.2 12.1 2.2c2.6 0 5.1 1 7 2.9 1.9 1.9 2.9 4.3 2.9 7 0 5.4-4.5 9.7-9.9 9.7Z" />
        </svg>
        @break

    @case('threads')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M17.5 7.5C16.4 5.8 14.6 5 12.1 5 8.7 5 6 7.2 6 12c0 4.9 2.7 7 6.2 7 3.4 0 5.8-1.9 5.8-4.5 0-2.4-2-3.7-5.5-3.7h-1" />
            <path d="M11 14.8c.5.7 1.4 1.1 2.4.8 1.2-.3 1.8-1.3 1.5-2.4-.3-1.3-1.6-1.9-3.5-1.5" />
        </svg>
        @break

    @case('telegram')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M21.7 4.3c.3-1-.6-1.7-1.5-1.3L2.9 9.8c-1.1.4-1.1 2 0 2.3l4.4 1.4 1.7 5.5c.3.9 1.4 1.1 2 .4l2.5-2.8 4.8 3.5c.8.6 2 .2 2.2-.8l3.2-15ZM18.8 6.8l-8.5 7.7-.3 2.6-1.2-4 10-6.3Z" />
        </svg>
        @break

    @case('vimeo')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 8.5l4.3 8.4c.7 1.3 2.4 1.4 3.2.2l4.1-6.1c.7-1.1 1.9-1.1 2.5-.2.6.9.2 2.3-1 3.7" />
        </svg>
        @break

    @default
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <path d="M3.6 9h16.8" />
            <path d="M3.6 15h16.8" />
            <path d="M12 3c2.2 2.4 3.3 5.4 3.3 9S14.2 18.6 12 21c-2.2-2.4-3.3-5.4-3.3-9S9.8 5.4 12 3Z" />
        </svg>
@endswitch
