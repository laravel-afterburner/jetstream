@php
    $colorScheme = \App\Support\ColorScheme::forUser(auth()->user());
@endphp
<script>
    (function () {
        const scheme = @json($colorScheme);
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDark = scheme === 'dark' || (scheme === 'system' && prefersDark);

        document.documentElement.dataset.colorScheme = scheme;

        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();
</script>
