<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Admin — <?= SITE_NAME ?></title>
    <meta name="color-scheme" content="light dark">
    <script>
        (function() {
            var t = null;
            var urlParam = new URLSearchParams(window.location.search).get('theme');
            if (urlParam === 'dark' || urlParam === 'light') {
                t = urlParam;
            } else {
                try { t = localStorage.getItem('theme'); } catch (e) {}
            }
            if (t !== 'dark' && t !== 'light') {
                t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <script src="/assets/js/theme.js" defer></script>
    <meta name="robots" content="noindex">
</head>
<body>
