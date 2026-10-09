@props(['url', 'text' => 'Check this out on Ajo'])

@php
    $encodedUrl = urlencode($url);
    $encodedText = urlencode($text);
@endphp

<div class="flex flex-wrap gap-2">
    <a href="https://wa.me/?text={{ $encodedText }}%20{{ $encodedUrl }}"
       target="_blank" rel="noopener"
       class="text-xs px-3 py-1.5 rounded-full bg-emerald-600 text-white hover:bg-emerald-700">WhatsApp</a>
    <a href="https://twitter.com/intent/tweet?text={{ $encodedText }}&url={{ $encodedUrl }}"
       target="_blank" rel="noopener"
       class="text-xs px-3 py-1.5 rounded-full bg-sky-500 text-white hover:bg-sky-600">X</a>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}"
       target="_blank" rel="noopener"
       class="text-xs px-3 py-1.5 rounded-full bg-blue-600 text-white hover:bg-blue-700">Facebook</a>
    <button type="button"
            onclick="navigator.clipboard.writeText(@js($url)); this.textContent='Copied';"
            class="text-xs px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
        Copy link
    </button>
</div>