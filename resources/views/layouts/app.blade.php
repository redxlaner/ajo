<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta property="og:site_name" content="Ajo">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ $ogTitle ?? 'Ajo — Build what matters, together' }}">
        <meta property="og:description" content="{{ $ogDescription ?? 'Fund dreams with people you trust. Create a team, contribute, and track progress together.' }}">
        <meta property="og:image" content="{{ $ogImage ?? asset('images/ajo-logo.png') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $ogTitle ?? 'Ajo — Build what matters, together' }}">
        <meta name="twitter:description" content="{{ $ogDescription ?? 'Fund dreams with people you trust.' }}">
        <meta name="twitter:image" content="{{ $ogImage ?? asset('images/ajo-logo.png') }}">

        <title>{{ config('app.name', 'Ajo') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="icon" type="image/png" href="{{ asset('images/ajo-logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/ajo-logo.png') }}">
        <style>[x-cloak]{display:none!important}</style>
        

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>

        <script>  
            document.addEventListener('DOMContentLoaded', () => {
                const token = () => document.querySelector('meta[name="csrf-token"]')?.content;

                document.addEventListener('click', async (e) => {
                    const btn = e.target.closest('[data-action]');
                    if (!btn) return;

                    const action = btn.dataset.action;

                    if (action === 'share') {
                        e.preventDefault();
                        const shareData = {
                            title: btn.dataset.title || 'Ajo Post',
                            url: btn.dataset.url || window.location.href,
                        };
                        if (navigator.share) {
                            try { await navigator.share(shareData); } catch (_) {}
                        } else {
                            await navigator.clipboard.writeText(shareData.url);
                            const original = btn.innerHTML;
                            btn.innerHTML = '<span>Copied</span>';
                            setTimeout(() => { btn.innerHTML = original; }, 1500);
                        }
                        return;
                    }

                    if (action === 'delete') {
                        e.preventDefault();
                        if (!confirm('Delete this post?')) return;

                        const card = document.getElementById('activity-card-' + btn.dataset.activityId);
                        btn.disabled = true;
                        try {
                            const res = await fetch(btn.dataset.url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': token(),
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            });
                            if (res.ok && card) {
                                card.remove();
                            } else {
                                alert('Could not delete post.');
                                btn.disabled = false;
                            }
                        } catch (err) {
                            console.error(err);
                            btn.disabled = false;
                        }
                        return;
                    }

                    if (!['like', 'repost', 'bookmark'].includes(action)) return;

                    e.preventDefault();
                    btn.disabled = true;
                    try {
                        const res = await fetch(btn.dataset.url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token(),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });
                        const data = await res.json().catch(() => null);
                        if (!res.ok || !data) {
                            alert((data && data.message) || ('Repost failed (' + res.status + ')'));
                            return;
                        }

                        if (action === 'like') {
                            const icon = btn.querySelector('[data-like-icon]');
                            const count = btn.querySelector('[data-like-count]');
                            if (icon) icon.textContent = data.liked ? '♥' : '♡';
                            if (count && data.likes_count !== undefined) count.textContent = data.likes_count;
                        } else if (action === 'repost') {
                            const count = document.querySelector('[data-repost-count="' + btn.dataset.activityId + '"]');
                            if (count && data.reposts_count !== undefined) count.textContent = data.reposts_count;
                            btn.textContent = data.label || (data.reposted ? 'Undo repost' : 'Repost');
                            document.getElementById('repost-menu-' + btn.dataset.activityId)?.classList.add('hidden');
                        } else if (action === 'bookmark') {
                            const icon = btn.querySelector('[data-bookmark-icon]');
                            if (icon) icon.textContent = data.bookmarked ? '★' : '☆';
                        }
                    } catch (err) {
                        console.error(err);
                    } finally {
                        btn.disabled = false;
                    }
                });

                document.addEventListener('submit', async (e) => {
                    const form = e.target.closest('[data-comment-form]');
                    if (!form) return;

                    e.preventDefault();
                    const btn = form.querySelector('button[type="submit"]');
                    const activityId = form.dataset.activityId;
                    if (btn) btn.disabled = true;

                    try {
                        const res = await fetch(form.dataset.url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token(),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            body: new FormData(form),
                        });
                        const data = await res.json().catch(() => null);
                        if (!res.ok || !data) {
                            alert(data?.message || 'Could not post comment.');
                            return;
                        }

                        const countEl = document.querySelector('[data-comment-count="' + activityId + '"]');
                        if (countEl && data.comments_count !== undefined) {
                            countEl.textContent = data.comments_count;
                        }
                        const textarea = form.querySelector('[name="body"]');
                        if (textarea) textarea.value = '';
                        form.classList.add('hidden');
                    } catch (err) {
                        console.error(err);
                        alert('Something went wrong.');
                    } finally {
                        if (btn) btn.disabled = false;
                    }
                });

                document.addEventListener('click', (e) => {
                    document.querySelectorAll('[id^="repost-menu-"]').forEach((menu) => {
                        if (menu.classList.contains('hidden')) return;
                        if (menu.contains(e.target)) return;
                        if (e.target.closest('button')?.getAttribute('onclick')?.includes(menu.id)) return;
                        menu.classList.add('hidden');
                    });
                });
            });
        </script>
    </body>
</html>