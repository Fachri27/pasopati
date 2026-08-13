<?php $__env->startSection('content'); ?>

<?php
    $isId = $locale === 'en' ? false : true;
    $archiveUrl = route('deforestory', ['locale' => $locale]);
?>

<section class="pt-[calc(64px+3rem)] sm:pt-[calc(64px+5rem)] pb-20">
    <div class="max-w-[640px] mx-auto px-5 text-center">
        <p class="font-mono-ui text-[.72rem] font-semibold uppercase tracking-[.18em] text-pasopati mb-4">
            <?php echo e($isId ? 'Deforestory / Arsip Belum Tersedia' : 'Deforestory / Archive Not Available'); ?>

        </p>
        <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-soft flex items-center justify-center text-ink-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
            </svg>
        </div>
        <h1 class="font-display font-bold text-[clamp(1.8rem,4vw,2.4rem)] leading-[1.15] tracking-[-.015em] mb-4">
            <?php echo e($isId ? 'Arsip kasus ini belum dibuat' : 'This case archive has not been created'); ?>

        </h1>
        <p class="text-[1.02rem] leading-[1.7] text-ink-2 max-w-[54ch] mx-auto mb-2">
            <?php echo e($isId
                ? 'Kasus <span class="font-mono-ui text-ink">/' . e($slug) . '</span> terdaftar di daftar, tetapi konten arsip dan laporannya belum diisi melalui CMS.'
                : 'The case <span class="font-mono-ui text-ink">/' . e($slug) . '</span> is listed, but its archive and report content has not been filled in via the CMS yet.'); ?>

        </p>
        <p class="text-[.92rem] leading-[1.6] text-ink-3 max-w-[54ch] mx-auto mb-8">
            <?php echo e($isId
                ? 'Silakan isi melalui menu Admin → Deforestory, buat kasus baru dengan slug yang sama, lalu set status Active.'
                : 'Please add it via Admin → Deforestory, create a new case with the same slug, then set status Active.'); ?>

        </p>
        <a href="<?php echo e($archiveUrl); ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-forest hover:text-forest-d transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            <?php echo e($isId ? 'Kembali ke daftar Deforestory' : 'Back to Deforestory list'); ?>

        </a>
    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.deforestory', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/aiti/pasopati/resources/views/front/deforestory-case-empty.blade.php ENDPATH**/ ?>