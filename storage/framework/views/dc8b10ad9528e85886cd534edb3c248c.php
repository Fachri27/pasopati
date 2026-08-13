<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Event / Kejadian</h2>
            <a href="<?php echo e(route('events.create')); ?>"
               class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium">
                + Tambah Event
            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 px-4 py-3 rounded-lg bg-green-100 text-green-800 border border-green-200">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        
        <form method="GET" action="<?php echo e(route('events.index')); ?>"
              class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Title / Lokasi</label>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari event..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Orientation</label>
                <select name="orientation" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Semua</option>
                    <option value="landscape" <?php if(request('orientation') === 'landscape'): echo 'selected'; endif; ?>>Landscape</option>
                    <option value="horizontal" <?php if(request('orientation') === 'horizontal'): echo 'selected'; endif; ?>>Horizontal</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg">Filter</button>
                <a href="<?php echo e(route('events.index')); ?>"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Reset</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        <th class="px-4 py-3">Thumbnail</th>
                        <th class="px-4 py-3">Title (ID)</th>
                        <th class="px-4 py-3">Title (EN)</th>
                        <th class="px-4 py-3">Tanggal Kejadian</th>
                        <th class="px-4 py-3">Lokasi</th>
                        <th class="px-4 py-3">Orientation</th>
                        <th class="px-4 py-3">Dibuat</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                    <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <?php if($event->image_id_url): ?>
                                    <img src="<?php echo e($event->image_id_url); ?>" alt="<?php echo e($event->title_id); ?>"
                                         class="w-24 h-16 object-cover rounded border border-gray-200">
                                <?php else: ?>
                                    <div class="w-24 h-16 rounded border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-gray-400 text-xs">Tanpa gambar</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                <?php echo e($event->title_id); ?>

                                <?php if($event->has_video): ?>
                                    <span class="ml-1 inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-red-100 text-red-700"
                                          title="Ada video">VIDEO</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3"><?php echo e($event->title_en); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap"><?php echo e($event->event_date->format('d M Y')); ?></td>
                            <td class="px-4 py-3 max-w-xs truncate"><?php echo e($event->location); ?></td>
                            <td class="px-4 py-3">
                                <?php if($event->orientation === \App\Enums\EventOrientation::Landscape): ?>
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Landscape</span>
                                <?php else: ?>
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Horizontal</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap"><?php echo e($event->created_at->format('d M Y')); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap space-x-1">
                                <a href="<?php echo e(route('events.show', $event)); ?>"
                                   class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs">Detail</a>
                                <a href="<?php echo e(route('events.edit', $event)); ?>"
                                   class="inline-block bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-xs">Edit</a>
                                <form action="<?php echo e(route('events.destroy', $event)); ?>" method="POST" class="inline"
                                      onsubmit="return confirm('Yakin ingin menghapus event ini? Gambar juga akan dihapus.');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                                Belum ada event. <a href="<?php echo e(route('events.create')); ?>" class="text-blue-600 underline">Buat event pertama</a>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <?php echo e($events->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/aiti/pasopati/resources/views/events/index.blade.php ENDPATH**/ ?>