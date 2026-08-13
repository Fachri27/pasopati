<?php $__env->startSection('content'); ?>

<?php
$locale = app()->getLocale();
$translation = $page->translations->where('locale', $locale)->first();
?>

<div>
    
    
    
    <?php if($page->type === 'parallax'): ?>
    <section x-data="{ offset: 0 }" x-init="
                window.addEventListener('scroll', () => {
                    if (window.innerWidth >= 768) {
                        offset = window.scrollY * 0.3
                    }
                })
            " class="relative min-h-[30vh] md:min-h-[80vh] overflow-hidden md:mb-20 mb-10">
        <!-- Background Image -->
        <div class="absolute inset-0 w-full h-full bg-center bg-cover"
            :style="`transform: translateY(${offset}px); background-image: url('<?php echo e(asset('storage/' . $page->featured_image)); ?>')`">
        </div>
    </section>

    <div class="max-w-4xl mx-auto text-center my-10 sm:my-16 md:my-20 px-5">
        
        <div class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-base mb-7 font-serif">
            <?php echo e($translation->title ?? $page->slug); ?>

        </div>
    </div>

    <?php else: ?>
    
    
    
    <div class="max-w-4xl mx-auto text-center my-10 sm:my-16 md:my-20 px-5">
        <div class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-base mb-7 font-serif pt-15">
            <?php echo e($translation->title ?? $page->slug); ?>

        </div>
        <p class="text-base sm:text-lg md:text-xl font-light font-sans mb-8 leading-relaxed">
            <?php echo $translation->excerpt ?? ''; ?>

        </p>

        <?php if($page->featured_image): ?>
        <img src="<?php echo e(asset('storage/' . $page->featured_image)); ?>" alt="<?php echo e($page->slug); ?>"
            class="w-full max-h-[700px] object-cover shadow mx-auto mt-5">
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>





<?php if($translation->content_blocks && count($translation->content_blocks) > 0): ?>
    <?php $__currentLoopData = $translation->content_blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $__env->make('front.blocks.' . ($block['type'] ?? 'paragraph'), ['data' => $block['data'] ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
<div class="
      prose
      max-w-2xl mx-auto
      px-5
      poppins-regular

      md:text-md sm:text-base text-[16px]
      text-left

      prose-p:tracking-[0.020em]
      prose-p:my-[1em]

      prose-h2:text-[24px]
      prose-h2:mt-8 prose-h2:mb-4 prose-h2:font-bold

      prose-h3:text-[21px]
      prose-h3:mt-6 prose-h3:mb-3 prose-h3:font-semibold
    ">
    <?php echo $translation->content ?? ''; ?>

</div>
<?php endif; ?>

<section class="bg-paper border-t border-line px-5 py-12 sm:py-16" aria-label="Komentar">
    <div class="max-w-[720px] mx-auto">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('comment-section', ['commentable' => $page]);

$__html = app('livewire')->mount($__name, $__params, 'comments-'.e($page->id).'', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </div>
</section>

<?php echo $__env->make('front.components.otherArtikel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('front.components.floating', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/aiti/pasopati/resources/views/front/page-expose.blade.php ENDPATH**/ ?>