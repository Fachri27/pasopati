<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Artikel</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo e($totalArticles); ?></p>
                </div>
                <div class="text-blue-500 text-3xl opacity-30">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </div>
            </div>
            <div class="mt-2 flex gap-3 text-xs text-gray-500">
                <span class="text-green-600 font-semibold"><?php echo e($activeArticles); ?> aktif</span>
                <span class="text-yellow-600 font-semibold"><?php echo e($draftArticles); ?> draft</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Fellowship</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo e($totalFellowships); ?></p>
                </div>
                <div class="text-green-500 text-3xl opacity-30">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-2 flex gap-3 text-xs text-gray-500">
                <span class="text-green-600 font-semibold"><?php echo e($activeFellowships); ?> aktif</span>
                <span class="text-blue-600 font-semibold"><?php echo e($upcomingFellowships); ?> akan datang</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Petisi</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo e($totalPetitions); ?></p>
                </div>
                <div class="text-purple-500 text-3xl opacity-30">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="mt-2 flex gap-3 text-xs text-gray-500">
                <span class="text-purple-600 font-semibold"><?php echo e($activePetitions); ?> aktif</span>
                <span class="text-gray-600 font-semibold"><?php echo e(number_format($totalSignatures)); ?> ttd</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-amber-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Komentar</p>
                    <p class="text-3xl font-bold text-gray-800"><?php echo e($totalComments); ?></p>
                </div>
                <div class="text-amber-500 text-3xl opacity-30">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
            </div>
            <div class="mt-2 flex gap-3 text-xs text-gray-500">
                <?php if($pendingComments > 0): ?>
                    <span class="text-red-600 font-semibold"><?php echo e($pendingComments); ?> perlu moderasi</span>
                <?php else: ?>
                    <span class="text-green-600 font-semibold">Semua terverifikasi</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Tanda Tangan Petisi</p>
            </div>
            <p class="text-3xl font-bold text-gray-800"><?php echo e(number_format($totalSignatures)); ?></p>
            <p class="text-xs text-gray-500 mt-1">
                <span class="text-red-500 font-semibold"><?php echo e(number_format($pendingSignatures)); ?></span> menunggu verifikasi
            </p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Pengguna</p>
            </div>
            <p class="text-3xl font-bold text-gray-800"><?php echo e($totalUsers); ?></p>
            <p class="text-xs text-gray-500 mt-1">Admin & Editor</p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500">Progress Petisi Aktif</p>
            </div>

            <p class="text-3xl font-bold text-gray-800"><?php echo e($avgProgress); ?>%</p>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                <div class="bg-purple-600 h-2 rounded-full" style="width: <?php echo e($avgProgress); ?>%"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        
        <div class="bg-white rounded-xl shadow">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Artikel Terbaru</h3>
                <a href="<?php echo e(route('pages.index')); ?>" class="text-sm text-blue-600 hover:underline">Lihat semua</a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $recentArticles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($article['title']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($article['created_at']->diffForHumans()); ?></p>
                        </div>
                        <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full
                            <?php switch($article['status']):
                                case ('active'): ?> bg-green-100 text-green-700 <?php break; ?>
                                <?php case ('draft'): ?> bg-yellow-100 text-yellow-700 <?php break; ?>
                                <?php default: ?> bg-gray-100 text-gray-700
                            <?php endswitch; ?>">
                            <?php echo e(ucfirst($article['status'])); ?>

                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="px-6 py-4 text-sm text-gray-400">Belum ada artikel.</p>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="bg-white rounded-xl shadow">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Petisi Terbaru</h3>
                <a href="<?php echo e(route('petition.admin.index')); ?>" class="text-sm text-blue-600 hover:underline">Lihat semua</a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $recentPetitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $petition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($petition['title']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e(number_format($petition['signatures'])); ?> ttd &middot; <?php echo e($petition['created_at']->diffForHumans()); ?></p>
                        </div>
                        <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full
                            <?php switch($petition['status']):
                                case ('active'): ?> bg-green-100 text-green-700 <?php break; ?>
                                <?php case ('draft'): ?> bg-yellow-100 text-yellow-700 <?php break; ?>
                                <?php case ('closed'): ?> bg-red-100 text-red-700 <?php break; ?>
                                <?php case ('succeeded'): ?> bg-blue-100 text-blue-700 <?php break; ?>
                                <?php default: ?> bg-gray-100 text-gray-700
                            <?php endswitch; ?>">
                            <?php echo e(ucfirst($petition['status'])); ?>

                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="px-6 py-4 text-sm text-gray-400">Belum ada petisi.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        
        <div class="bg-white rounded-xl shadow">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">
                    Komentar Menunggu Moderasi
                    <?php if($pendingComments > 0): ?>
                        <span class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full"><?php echo e($pendingComments); ?></span>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $recentComments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="px-6 py-3">
                        <p class="text-sm font-medium text-gray-900"><?php echo e($comment['name']); ?></p>
                        <p class="text-xs text-gray-500 truncate"><?php echo e($comment['body']); ?></p>
                        <p class="text-xs text-gray-400 mt-1">
                            Pada: <?php echo e($comment['page_title']); ?> &middot; <?php echo e($comment['created_at']->diffForHumans()); ?>

                        </p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="px-6 py-4 text-sm text-gray-400">Tidak ada komentar yang perlu dimoderasi.</p>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="bg-white rounded-xl shadow">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Petisi dengan Tanda Tangan Terbanyak</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $topPetitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $petition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="px-6 py-3">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm font-medium text-gray-900 truncate flex-1"><?php echo e($petition['title']); ?></p>
                            <span class="text-sm font-bold text-purple-600 ml-2"><?php echo e(number_format($petition['signatures'])); ?></span>
                        </div>
                        <?php if($petition['goal'] > 0): ?>
                            <?php $pct = min(100, round(($petition['signatures'] / $petition['goal']) * 100)); ?>
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-purple-600 h-1.5 rounded-full" style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5"><?php echo e($pct); ?>% dari <?php echo e(number_format($petition['goal'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="px-6 py-4 text-sm text-gray-400">Belum ada data.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="mt-8 bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Aksi Cepat</h3>
        <div class="flex flex-wrap gap-3">
            <a href="<?php echo e(route('pages.create')); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">+ Artikel Baru</a>
            <a href="<?php echo e(route('fellowship.create')); ?>" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">+ Fellowship Baru</a>
            <a href="<?php echo e(route('petition.admin.create')); ?>" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm">+ Petisi Baru</a>
            <a href="<?php echo e(route('kategori.create')); ?>" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 text-sm">+ Kategori Baru</a>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/aiti/pasopati/resources/views/pages/admin/dashboard-admin.blade.php ENDPATH**/ ?>