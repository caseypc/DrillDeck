<script>
    // Apply preference before CSS paints; honor runtime OS/browser changes.
    (() => {
        const preference = window.matchMedia('(prefers-color-scheme: dark)');
        const apply = () => document.documentElement.setAttribute('data-bs-theme', preference.matches ? 'dark' : 'light');
        apply();
        if (preference.addEventListener) preference.addEventListener('change', apply);
        else preference.addListener(apply);
    })();
</script>
