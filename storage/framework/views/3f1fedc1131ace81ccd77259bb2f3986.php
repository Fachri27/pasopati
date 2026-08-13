<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <link rel="shortcut icon" href="https://pasopati.id/theme/webmag/img/auriga2.png">

    <?php
        $meta = seo()->all();
        $locale = $meta['locale'];
        $alternate = $locale === 'id' ? 'en' : 'id';
        // build alternate url safe: jika kamu punya struktur /{locale}/...
        $alternateUrl = url(str_replace("/{$locale}/", "/{$alternate}/", request()->getRequestUri()));
    ?>

    

    <!-- 🌐 Basic Meta -->
    <title><?php echo e($meta['title']); ?></title>
    <meta name="description" content="<?php echo e($meta['description']); ?>">
    <meta name="title" content="<?php echo e($meta['title']); ?>">
    <link rel="canonical" href="<?php echo e(url()->current()); ?>">

    <!-- 🔍 Google / Schema.org -->
    <meta itemprop="name" content="<?php echo e($meta['title']); ?>">
    <meta itemprop="description" content="<?php echo e($meta['description']); ?>">
    <meta itemprop="image" content="<?php echo e($meta['image']); ?>">

    <!-- 🟦 Open Graph / Facebook / WhatsApp -->
    <meta property="og:locale" content="<?php echo e(app()->getLocale()); ?>">
    <meta property="og:site_name" content="<?php echo e(config('app.name')); ?>">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:type" content="<?php echo e($meta['type'] ?? 'website'); ?>">
    <meta property="og:title" content="<?php echo e($meta['title']); ?>">
    <meta property="og:description" content="<?php echo e($meta['description']); ?>">
    <meta property="og:image" content="<?php echo e($meta['image']); ?>">
    <meta property="og:image:alt" content="<?php echo e($meta['title']); ?>">
    <meta property="og:image:width" content="300">
    <meta property="og:image:height" content="150">

    <!-- 🐦 Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="{{ config('app.twitter_handle') ?? 'yourhandle' }}">
    <meta name="twitter:title" content="<?php echo e($meta['title']); ?>">
    <meta name="twitter:description" content="<?php echo e($meta['description']); ?>">
    <meta name="twitter:image" content="<?php echo e($meta['image']); ?>">
    <meta name="twitter:image:alt" content="<?php echo e($meta['title']); ?>">


    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-5WC19K3Y9D"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-5WC19K3Y9D');
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Merriweather:wght@400;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">


    <style>
        html.preloader-active { overflow: hidden; }
        body {
            font-family: 'Inter', sans-serif;
        }

        h1,
        a h2,
        h3,
        h4 {
            font-family: 'Poppins', sans-serif;
        }
    </style>

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>


    <?php if(! empty(config('services.turnstile.site_key'))): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
    <?php endif; ?>
</head>

<body class="flex flex-col min-h-screen bg-white">
    <?php if(request()->routeIs('home')): ?>
        <?php echo $__env->make('front.partials.preloader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    
    <?php echo $__env->make('front.components.navbar-user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


    <!-- Page Content -->
    <main class="flex-grow">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    
    <?php echo $__env->make('front.components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
<script>
document.addEventListener('click', function (e) {
    const slider = e.target.closest('.tmce-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('figure');
    let index = parseInt(slider.dataset.index);

    if (e.target.classList.contains('next')) {
        index = (index + 1) % slides.length;
    }

    if (e.target.classList.contains('prev')) {
        index = (index - 1 + slides.length) % slides.length;
    }

    slides.forEach(slide => slide.classList.remove('active'));
    slides[index].classList.add('active');
    slider.dataset.index = index;
});
</script>

</html>
<?php /**PATH /Users/aiti/pasopati/resources/views/layouts/app.blade.php ENDPATH**/ ?>