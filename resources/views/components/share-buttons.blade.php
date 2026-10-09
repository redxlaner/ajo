@props(['url', 'text' => 'Join me on Ajo'])

@php
    $encodedUrl = urlencode($url);
    $encodedText = urlencode($text);
@endphp

<div class="flex flex-wrap gap-2 mt-3">
    <a href="https://wa.me/?text={{ $encodedText }}%20{{ $encodedUrl }}"
       target="_blank" rel="noopener"
       class="text-sm px-3 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700">
        WhatsApp
    </a>
    <a href="https://twitter.com/intent/tweet?text={{ $encodedText }}&url={{ $encodedUrl }}"
       target="_blank" rel="noopener"
       class="text-sm px-3 py-2 rounded-xl bg-sky-500 text-white hover:bg-sky-600">
        X
    </a>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}"
       target="_blank" rel="noopener"
       class="text-sm px-3 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
        Facebook
    </a>
    <button type="button"
            onclick="navigator.clipboard.writeText('{{ $url }}'); this.innerText='Copied';"
            class="text-sm px-3 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
        Copy link
    </button>
</div>