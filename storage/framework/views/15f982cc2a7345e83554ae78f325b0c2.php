<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />
    <title>Fire Pasopati — Pantauan Karhutla Indonesia</title>
    <meta
      name="description"
      content="Pantauan kebakaran hutan dan lahan di Indonesia — berita terkini, statistik harian, dan peta sebaran wilayah rawan."
    />
    <link rel="stylesheet" href="<?php echo e(asset('assets/vendor/leaflet/leaflet.css')); ?>" />
    
    <link rel="stylesheet" href="<?php echo e(asset('assets/vendor/flatpickr/flatpickr.min.css')); ?>" />

    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>

    <link rel="stylesheet" href="<?php echo e(asset('dist/style.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('css/nav-pasopati.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('css/pantauan-kosong.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('css/rincian-laporan.css')); ?>" />
  </head>
  <body>
    <h1 class="sr-only">Pantauan kebakaran hutan dan lahan Indonesia</h1>

    <?php echo $__env->make('pasopati.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldContent('konten'); ?>

    <script src="<?php echo e(asset('assets/vendor/leaflet/leaflet.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/vendor/flatpickr/flatpickr.min.js')); ?>"></script>
    <script src="<?php echo e(asset('data/konten.js')); ?>"></script>
    <script src="<?php echo e(asset('data/peta-provinsi.js')); ?>"></script>
    <script src="<?php echo e(asset('js/panggung.js')); ?>"></script>
    <script src="<?php echo e(asset('js/beranda.js')); ?>"></script>
    <script defer src="<?php echo e(asset('assets/vendor/alpine/alpine.min.js')); ?>"></script>
    <script src="<?php echo e(asset('js/peta.js')); ?>"></script>
    <script src="<?php echo e(asset('js/nav.js')); ?>"></script>
    <script src="<?php echo e(asset('js/parallax.js')); ?>"></script>

    
    <?php if(! empty(config('services.turnstile.site_key'))): ?>
      <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
    <?php endif; ?>

  </body>
</html>
<?php /**PATH /Users/aiti/pasopati/resources/views/pasopati/layout.blade.php ENDPATH**/ ?>