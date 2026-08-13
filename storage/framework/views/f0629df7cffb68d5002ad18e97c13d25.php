<div class="flex flex-col justify-center items-center">
    <div class="bg-white shadow rounded-lg p-6 w-full">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Deforestory — Arsip Kasus</h2>
                <p class="text-xs text-gray-500 mt-1">
                    Daftar kartu kasus berasal dari API. Tiap kasus bisa punya
                    <strong>banyak laporan</strong> (judul, slug, gambar, isi sendiri). Klik
                    <strong>Kelola laporan</strong> untuk menambah/mengedit laporan per kasus.
                    Halaman arsip <span class="font-mono">/{slug}</span> menampilkan daftar laporan;
                    detail di <span class="font-mono">/{slug}/{slug-laporan}</span>.
                </p>
            </div>
            <button wire:click="refreshList" wire:loading.attr="disabled"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium border">
                ⟳ Refresh
            </button>
        </div>

        <!--[if BLOCK]><![endif]--><?php if(session('success')): ?>
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded"><?php echo e(session('success')); ?></div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if(session('error')): ?>
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded"><?php echo e(session('error')); ?></div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <div class="flex flex-wrap items-center justify-between mb-4 gap-3">
            <input type="text" wire:model.live.debounce.150ms="search" placeholder="Cari judul / slug kartu..." class="border p-2 rounded">
            <span class="text-xs text-gray-400">Sumber: API (<?php echo e(config('services.deforestory_api.url')); ?>)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse table-fixed">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm font-medium text-gray-600">
                        <th class="p-3 w-24">Gambar</th>
                        <th class="p-3">Kartu (API)</th>
                        <th class="p-3 w-44">UUID Simontini</th>
                        <th class="p-3 w-28">Kategori</th>
                        <th class="p-3 w-24">Tahun</th>
                        <th class="p-3 w-32">Jumlah laporan</th>
                        <th class="p-3 w-56">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $cases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $imgUrl = '';
                            if (!empty($row['image'])) {
                                $imgUrl = \Illuminate\Support\Str::startsWith($row['image'], ['http://','https://'])
                                    ? $row['image'] : asset('storage/' . $row['image']);
                            }
                            $publicUrl = route('deforestory.case', ['locale' => 'id', 'slug' => $row['slug']]);
                            $laporanUrl = route('deforestory.laporan.index', ['caseSlug' => $row['slug']]);
                        ?>
                        <tr>
                            <td class="p-3">
                                <!--[if BLOCK]><![endif]--><?php if($imgUrl): ?>
                                    <img src="<?php echo e($imgUrl); ?>" alt="" class="w-20 h-12 object-cover rounded">
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">—</span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </td>
                            <td class="p-3">
                                <span class="text-sm font-medium text-gray-800 block"><?php echo e($row['title']); ?></span>
                                <span class="text-xs text-gray-400">/<?php echo e($row['slug']); ?></span>
                            </td>
                            <td class="p-3">
                                <!--[if BLOCK]><![endif]--><?php if(! empty($row['uuid'])): ?>
                                    <div x-data="{ copied:false, copy(){ navigator.clipboard?.writeText('<?php echo e(e($row['uuid'])); ?>'); this.copied=true; setTimeout(()=>this.copied=false,1200) } }">
                                        <code class="block text-[10px] font-mono text-gray-600 break-all leading-tight"><?php echo e($row['uuid']); ?></code>
                                        <button type="button" @click="copy" class="mt-1 text-[11px] text-blue-600 hover:underline whitespace-nowrap" x-text="copied ? 'Tersalin ✓' : 'Salin'">Salin</button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">—</span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </td>
                            <td class="p-3 text-xs"><?php echo e($row['category'] ?: '-'); ?></td>
                            <td class="p-3 text-xs"><?php echo e($row['year'] ?: '-'); ?></td>
                            <td class="p-3">
                                <!--[if BLOCK]><![endif]--><?php if($row['laporan_count'] > 0): ?>
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700"><?php echo e($row['laporan_count']); ?> laporan</span>
                                <?php else: ?>
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-600">Belum ada</span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </td>
                            <td class="p-3">
                                <a href="<?php echo e($publicUrl); ?>" target="_blank">
                                    <button class="bg-gray-600 px-3 py-1 rounded text-white text-xs">Lihat</button>
                                </a>
                                <a href="<?php echo e($laporanUrl); ?>">
                                    <button class="bg-blue-600 px-3 py-1 rounded text-white text-xs">Kelola laporan</button>
                                </a>
                                <button wire:click="deleteCard('<?php echo e($row['slug']); ?>')"
                                    wire:confirm="<?php echo e($row['laporan_total'] > 0
                                        ? 'Kartu ini punya ' . $row['laporan_total'] . ' laporan. Hapus kartu beserta semua konten detailnya?'
                                        : 'Hapus kartu ini?'); ?>"
                                    class="bg-red-600 px-3 py-1 rounded text-white text-xs">Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td class="p-3 text-center text-gray-400" colspan="7">
                                Tidak ada kartu dari API. Periksa konfigurasi <code>DEFORESTORY_API_URL</code>.
                            </td>
                        </tr>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <?php echo e($cases->links('vendor.pagination.custom')); ?>

        </div>
    </div>
</div><?php /**PATH /Users/aiti/pasopati/resources/views/livewire/deforestory/deforestory-case-table.blade.php ENDPATH**/ ?>