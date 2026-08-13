<?php $ptop = $data['mt'] ?? null; $pbot = $data['mb'] ?? null; ?>
<div <?php if($ptop || $pbot): ?> style="margin-top: <?php echo e($ptop); ?>px; margin-bottom: <?php echo e($pbot); ?>px;" <?php endif; ?>
     class="
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
    <?php echo $data['html'] ?? ''; ?>

</div>
<?php /**PATH /Users/aiti/pasopati/resources/views/front/blocks/paragraph.blade.php ENDPATH**/ ?>