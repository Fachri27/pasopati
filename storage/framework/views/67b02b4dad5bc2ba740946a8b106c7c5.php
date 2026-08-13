<?php $__env->startSection('content'); ?>

<?php
    $isId = $locale === 'en' ? false : true;
    $archiveUrl = route('deforestory', ['locale' => $locale]);
?>


<header class="pt-[calc(64px+2.5rem)] pb-8">
    <div class="max-w-[1200px] mx-auto px-5">
        <p class="font-mono-ui text-[.72rem] font-semibold uppercase tracking-[.18em] text-pasopati mb-3">
            <a href="<?php echo e($archiveUrl); ?>" class="hover:text-pasopati-d transition-colors">Deforestory</a>
            <span class="mx-2 text-line-d">/</span>
            <span class="text-ink-2"><?php echo e($isId ? 'Arsip Kasus' : 'Case Archive'); ?></span>
        </p>
        <h1 class="font-display font-bold text-[clamp(1.9rem,5vw,2.8rem)] leading-[1.15] tracking-[-.015em] max-w-[28ch]"><?php echo e($title); ?></h1>
    </div>
</header>


<section class="pb-20">
    <div class="max-w-[640px] mx-auto px-5 text-center">
        <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-soft flex items-center justify-center text-ink-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="9" y1="15" x2="15" y2="15"/>
            </svg>
        </div>
        <h2 class="font-display font-bold text-[clamp(1.4rem,3vw,1.9rem)] leading-[1.2] tracking-[-.01em] mb-3">
            <?php echo e($isId ? 'Belum ada laporan untuk kasus ini' : 'No reports for this case yet'); ?>

        </h2>
        <p class="text-[1rem] leading-[1.7] text-ink-2 max-w-[52ch] mx-auto mb-8">
            <?php echo e($isId
                ? 'Kasus ini terdaftar, tetapi laporan dan arsipnya belum dipublikasikan. Konten akan muncul di halaman ini begitu ada laporan yang ditambahkan melalui CMS.'
                : 'This case is listed, but its reports and archive have not been published yet. Content will appear on this page once a report is added via the CMS.'); ?>

        </p>
        <a href="<?php echo e($archiveUrl); ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-forest hover:text-forest-d transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            <?php echo e($isId ? 'Kembali ke daftar Deforestory' : 'Back to Deforestory list'); ?>

        </a>
    </div>
</section>


<section class="bg-paper border-t border-line px-5 py-16" aria-label="<?php echo e($isId ? 'Berlangganan arsip' : 'Subscribe to archive'); ?>">
    <div class="max-w-[1200px] mx-auto grid md:grid-cols-[1fr_1.2fr] gap-10 items-center">
        <div>
            <p class="font-mono-ui text-[.7rem] font-semibold uppercase tracking-[.14em] text-pasopati mb-3">Update Arsip</p>
            <h2 class="font-display font-bold text-[clamp(1.35rem,3vw,1.85rem)] leading-[1.2] tracking-[-.01em] max-w-[26ch]">
                <?php echo e($isId ? 'Terus terhubung dengan kasus-kasus ini.' : 'Stay connected to these cases.'); ?>

            </h2>
            <p class="text-[.95rem] text-ink-2 leading-[1.7] max-w-[52ch] mt-3">
                <?php echo e($isId
                    ? 'Dapatkan pemberitahuan ketika laporan baru, data satelit, atau analisis tindak lanjut dari arsip Deforestory diterbitkan.'
                    : 'Get notified when new reports, satellite data, or follow-up analysis from the Deforestory archive are published.'); ?>

            </p>
        </div>
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('deforestory-subscribe', ['locale' => $locale,'variant' => 'archive']);

$__html = app('livewire')->mount($__name, $__params, 'lw-802974946-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.deforestory', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/aiti/pasopati/resources/views/front/deforestory-case-preview.blade.php ENDPATH**/ ?>