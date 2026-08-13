<!--[if BLOCK]><![endif]--><?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="<?php echo e(__('Pagination Navigation')); ?>" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            <!--[if BLOCK]><![endif]--><?php if($paginator->onFirstPage()): ?>
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-200 cursor-default rounded-lg">
                    <?php echo __('pagination.previous'); ?>

                </span>
            <?php else: ?>
                <a href="<?php echo e($paginator->previousPageUrl()); ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-[#2B5343] bg-white border border-gray-200 rounded-lg hover:bg-[#2B5343] hover:text-white focus:z-10 focus:outline-none focus:ring-2 focus:ring-[#2B5343]/30 transition ease-in-out duration-150">
                    <?php echo __('pagination.previous'); ?>

                </a>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]--><?php if($paginator->hasMorePages()): ?>
                <a href="<?php echo e($paginator->nextPageUrl()); ?>" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-[#2B5343] bg-white border border-gray-200 rounded-lg hover:bg-[#2B5343] hover:text-white focus:z-10 focus:outline-none focus:ring-2 focus:ring-[#2B5343]/30 transition ease-in-out duration-150">
                    <?php echo __('pagination.next'); ?>

                </a>
            <?php else: ?>
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-200 cursor-default rounded-lg">
                    <?php echo __('pagination.next'); ?>

                </span>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-600 leading-5">
                    <?php echo __('Showing'); ?>

                    <!--[if BLOCK]><![endif]--><?php if($paginator->firstItem()): ?>
                        <span class="font-semibold text-[#2B5343]"><?php echo e($paginator->firstItem()); ?></span>
                        <?php echo __('to'); ?>

                        <span class="font-semibold text-[#2B5343]"><?php echo e($paginator->lastItem()); ?></span>
                    <?php else: ?>
                        <?php echo e($paginator->count()); ?>

                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php echo __('of'); ?>

                    <span class="font-semibold text-[#2B5343]"><?php echo e($paginator->total()); ?></span>
                    <?php echo __('results'); ?>

                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rtl:flex-row-reverse rounded-lg shadow-sm">
                    
                    <!--[if BLOCK]><![endif]--><?php if($paginator->onFirstPage()): ?>
                        <span aria-disabled="true" aria-label="<?php echo e(__('pagination.previous')); ?>">
                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-default rounded-l-lg leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-[#2B5343] bg-white border border-gray-200 rounded-l-lg leading-5 hover:bg-[#2B5343] hover:text-white focus:z-10 focus:outline-none focus:ring-2 focus:ring-[#2B5343]/30 transition ease-in-out duration-150" aria-label="<?php echo e(__('pagination.previous')); ?>">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        
                        <!--[if BLOCK]><![endif]--><?php if(is_string($element)): ?>
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-200 cursor-default leading-5"><?php echo e($element); ?></span>
                            </span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        
                        <!--[if BLOCK]><![endif]--><?php if(is_array($element)): ?>
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <!--[if BLOCK]><![endif]--><?php if($page == $paginator->currentPage()): ?>
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-bold text-white bg-[#2B5343] border border-[#2B5343] leading-5"><?php echo e($page); ?></span>
                                    </span>
                                <?php else: ?>
                                    <a href="<?php echo e($url); ?>" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-200 leading-5 hover:bg-[#2B5343] hover:text-white focus:z-10 focus:outline-none focus:ring-2 focus:ring-[#2B5343]/30 transition ease-in-out duration-150" aria-label="<?php echo e(__('Go to page :page', ['page' => $page])); ?>">
                                        <?php echo e($page); ?>

                                    </a>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                    
                    <!--[if BLOCK]><![endif]--><?php if($paginator->hasMorePages()): ?>
                        <a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-[#2B5343] bg-white border border-gray-200 rounded-r-lg leading-5 hover:bg-[#2B5343] hover:text-white focus:z-10 focus:outline-none focus:ring-2 focus:ring-[#2B5343]/30 transition ease-in-out duration-150" aria-label="<?php echo e(__('pagination.next')); ?>">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php else: ?>
                        <span aria-disabled="true" aria-label="<?php echo e(__('pagination.next')); ?>">
                            <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-default rounded-r-lg leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </span>
            </div>
        </div>
    </nav>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH /Users/aiti/pasopati/resources/views/vendor/pagination/custom.blade.php ENDPATH**/ ?>