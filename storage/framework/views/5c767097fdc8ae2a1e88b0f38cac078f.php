<div class="flex flex-col justify-center items-center">
    <div class="bg-white shadow rounded-lg p-6 w-full">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Deforestory — Subscriber</h2>
                <p class="text-sm text-gray-500 mt-1">
                    <?php echo e($activeCount); ?> aktif / <?php echo e($total); ?> total
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between mb-4 gap-3">
            <input type="text" wire:model.live.debounce.100ms="search" placeholder="Cari email..." class="border p-2 rounded">
            <div class="flex items-center gap-2">
                <label for="statusFilter" class="text-sm font-medium text-gray-700">Filter Status:</label>
                <select id="statusFilter" wire:model.live.debounce.100ms="status" class="border-gray-300 rounded-md text-sm">
                    <option value="all">Semua</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse table-fixed">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm font-medium text-gray-600">
                        <th class="p-3">Email</th>
                        <th class="p-3">Scope</th>
                        <th class="p-3">Bahasa</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $subscribers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subscriber): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="p-3"><?php echo e($subscriber->email); ?></td>
                            <td class="p-3">
                                <!--[if BLOCK]><![endif]--><?php if($subscriber->type === 'all'): ?>
                                    <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-blue-100 text-blue-700">Semua</span>
                                <?php else: ?>
                                    <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-amber-100 text-amber-700">Kasus</span>
                                    <span class="block text-xs text-gray-500 mt-1 truncate max-w-[180px]"><?php echo e($subscriber->case->translation('id')?->title ?? $subscriber->case->slug ?? '-'); ?></span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </td>
                            <td class="p-3 uppercase"><?php echo e($subscriber->locale); ?></td>
                            <td class="p-3">
                                <!--[if BLOCK]><![endif]--><?php if($subscriber->active): ?>
                                    <span class="inline-block px-3 py-1 text-white text-xs font-semibold rounded-full bg-green-500">aktif</span>
                                <?php else: ?>
                                    <span class="inline-block px-3 py-1 text-white text-xs font-semibold rounded-full bg-gray-500">nonaktif</span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </td>
                            <td class="p-3"><?php echo e($subscriber->subscribed_at?->format('d M Y H:i') ?? '-'); ?></td>
                            <td class="p-3">
                                <button wire:click="toggle(<?php echo e($subscriber->id); ?>)" class="bg-yellow-600 px-3 py-1 rounded text-white">
                                    <?php echo e($subscriber->active ? 'Nonaktifkan' : 'Aktifkan'); ?>

                                </button>
                                <button wire:click="delete(<?php echo e($subscriber->id); ?>)"
                                    onclick="return confirm('Hapus subscriber ini?')"
                                    class="bg-red-600 px-3 py-1 rounded text-white">Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td class="p-3" colspan="6">Data tidak ditemukan</td>
                        </tr>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <?php echo e($subscribers->links('vendor.pagination.custom')); ?>

        </div>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session('success')): ?>
        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 3000)"
            class="fixed bottom-6 right-6 bg-green-400 text-white px-6 py-4 shadow-lg">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH /Users/aiti/pasopati/resources/views/livewire/deforestory/deforestory-subscriber-table.blade.php ENDPATH**/ ?>