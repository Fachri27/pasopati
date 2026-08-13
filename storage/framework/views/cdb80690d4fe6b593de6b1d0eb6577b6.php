<?php
    $isEdit = ! is_null($event) && $event->exists;
    $formAction = $isEdit ? route('events.update', $event) : route('events.store');
    $orientation = old('orientation', $event?->orientation?->value ?? 'landscape');
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="bg-white shadow rounded-lg p-6">
        <nav class="text-sm text-gray-600 mb-6 flex items-center gap-2">
            <a href="<?php echo e(route('events.index')); ?>" class="hover:text-blue-600 font-medium">Event / Kejadian</a>
            <span class="text-gray-400">›</span>
            <span class="text-blue-600 font-semibold"><?php echo e($isEdit ? 'Edit Event' : 'Tambah Event'); ?></span>
        </nav>

        <h1 class="text-2xl font-bold mb-8 text-gray-800"><?php echo e($isEdit ? 'Edit Event' : 'Tambah Event'); ?></h1>

        <form id="event-form" method="POST" action="<?php echo e($formAction); ?>"
              enctype="multipart/form-data" novalidate>
            <?php echo csrf_field(); ?>
            <?php if($isEdit): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="mb-6 px-4 py-3 rounded-lg bg-red-50 border border-red-200">
                    <ul class="list-disc list-inside text-sm text-red-700">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-12 gap-6">
                
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    
                    <div>
                        <label class="block font-medium text-gray-700">Gambar Indonesia <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mt-0.5 mb-2">JPG, JPEG, PNG, WEBP — maks 100 MB · wajib bila tanpa video</p>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 text-center">
                            <img id="preview-image-id"
                                 src="<?php echo e(old('image_id') ? '' : ($event?->image_id_url ?? '')); ?>"
                                 alt="Preview Image Indonesia"
                                 class="mx-auto rounded border border-gray-200 object-cover max-h-56 <?php echo e($event?->image_id ? '' : 'hidden'); ?>"
                                 data-orientation-preview
                                 style="aspect-ratio: 16 / 9">
                            <label class="mt-3 inline-block cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                <?php echo e($isEdit && $event->image_id ? 'Ganti Gambar' : 'Pilih Gambar'); ?>

                                <input type="file" name="image_id" accept="image/jpeg,image/jpg,image/png,image/webp"
                                       class="hidden" data-image-input data-preview-target="preview-image-id">
                            </label>
                            <?php if($isEdit && $event->image_id): ?>
                                <p class="text-xs text-gray-500 mt-2">Gambar lama dipertahankan jika tidak diganti.</p>
                            <?php endif; ?>
                        </div>
                        <?php $__errorArgs = ['image_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="block font-medium text-gray-700">Gambar English <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mt-0.5 mb-2">JPG, JPEG, PNG, WEBP — maks 100 MB · wajib bila tanpa video</p>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 text-center">
                            <img id="preview-image-en"
                                 src="<?php echo e(old('image_en') ? '' : ($event?->image_en_url ?? '')); ?>"
                                 alt="Preview Image English"
                                 class="mx-auto rounded border border-gray-200 object-cover max-h-56 <?php echo e($event?->image_en ? '' : 'hidden'); ?>"
                                 data-orientation-preview
                                 style="aspect-ratio: 16 / 9">
                            <label class="mt-3 inline-block cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                <?php echo e($isEdit && $event->image_en ? 'Ganti Gambar' : 'Pilih Gambar'); ?>

                                <input type="file" name="image_en" accept="image/jpeg,image/jpg,image/png,image/webp"
                                       class="hidden" data-image-input data-preview-target="preview-image-en">
                            </label>
                            <?php if($isEdit && $event->image_en): ?>
                                <p class="text-xs text-gray-500 mt-2">Gambar lama dipertahankan jika tidak diganti.</p>
                            <?php endif; ?>
                        </div>
                        <?php $__errorArgs = ['image_en'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="block font-medium text-gray-700">Video <span class="text-xs text-gray-400 font-normal">(opsional)</span></label>
                        <p class="text-xs text-gray-500 mt-0.5 mb-2">MP4, MOV, MKV, WEBM — maks 100 MB. Bila gambar tidak diunggah, thumbnail otomatis diambil dari frame video.</p>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 text-center">
                            <video id="preview-video" controls preload="metadata"
                                   class="mx-auto rounded border border-gray-200 max-h-56 <?php echo e($event?->has_video ? '' : 'hidden'); ?>">
                                <?php if($event?->video): ?>
                                    <source src="<?php echo e($event->video_url); ?>">
                                <?php endif; ?>
                            </video>
                            <label class="mt-3 inline-block cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                <?php echo e($isEdit && $event->has_video ? 'Ganti Video' : 'Pilih Video'); ?>

                                <input type="file" name="video" accept="video/mp4,video/quicktime,video/x-matroska,video/webm"
                                       class="hidden" data-video-input data-video-preview="preview-video">
                            </label>
                            <?php if($isEdit && $event->has_video): ?>
                                <p class="text-xs text-gray-500 mt-2">Video lama dipertahankan jika tidak diganti.</p>
                            <?php endif; ?>
                        </div>
                        <?php $__errorArgs = ['video'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="block font-medium text-gray-700 mb-2">Image Orientation <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="orientation" value="landscape" <?php if($orientation === 'landscape'): echo 'checked'; endif; ?>>
                                <span class="text-sm text-gray-700">Landscape</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="orientation" value="horizontal" <?php if($orientation === 'horizontal'): echo 'checked'; endif; ?>>
                                <span class="text-sm text-gray-700">Horizontal</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Preview gambar menyesuaikan aspect ratio pilihan.</p>
                        <?php $__errorArgs = ['orientation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                
                <div class="col-span-12 lg:col-span-8 space-y-6">
                    
                    <div>
                        <label for="title_id" class="block font-medium text-gray-700">Title (Indonesia) <span class="text-red-500">*</span></label>
                        <input type="text" id="title_id" name="title_id" value="<?php echo e(old('title_id', $event?->title_id)); ?>"
                               maxlength="255" placeholder="Judul event dalam Bahasa Indonesia"
                               class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                        <?php $__errorArgs = ['title_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label for="title_en" class="block font-medium text-gray-700">Title (English) <span class="text-red-500">*</span></label>
                        <input type="text" id="title_en" name="title_en" value="<?php echo e(old('title_en', $event?->title_en)); ?>"
                               maxlength="255" placeholder="Event title in English"
                               class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                        <?php $__errorArgs = ['title_en'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label for="event_date" class="block font-medium text-gray-700">Tanggal Kejadian <span class="text-red-500">*</span></label>
                        <input type="date" id="event_date" name="event_date"
                               value="<?php echo e(old('event_date', $event?->event_date?->format('Y-m-d'))); ?>"
                               data-date-picker
                               class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                        <?php $__errorArgs = ['event_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label for="location-search" class="block font-medium text-gray-700">Cari Lokasi (GeoServer)</label>
                        <input type="text" id="location-search" autocomplete="off"
                               placeholder="Ketik nama lokasi, mis. bandung..."
                               value="<?php echo e(old('location', $event?->location)); ?>"
                               class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                        <div id="location-results" class="mt-1 w-full rounded-lg border border-gray-200 shadow-sm hidden"></div>
                        <input type="hidden" name="location" id="location" value="<?php echo e(old('location', $event?->location)); ?>">
                        <input type="hidden" name="location_geojson" id="location_geojson"
                               value="<?php echo e(old('location_geojson', $event?->location_geojson ? json_encode($event->location_geojson) : '')); ?>">
                        <p class="text-xs text-gray-500 mt-1">Pilih hasil dari GeoServer atau klik langsung pada peta.</p>
                        <?php $__errorArgs = ['location'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="block font-medium text-gray-700 mb-2">Pilih Lokasi di Peta</label>
                        <div id="event-map" class="h-96 w-full rounded-lg border border-gray-300 z-0"></div>
                    </div>

                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="location_lat" class="block font-medium text-gray-700">Latitude</label>
                            <input type="number" step="any" min="-90" max="90" id="location_lat" name="location_lat"
                                   value="<?php echo e(old('location_lat', $event?->location_lat)); ?>" data-coordinate="lat"
                                   placeholder="-6.917500"
                                   class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                            <?php $__errorArgs = ['location_lat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="location_lng" class="block font-medium text-gray-700">Longitude</label>
                            <input type="number" step="any" min="-180" max="180" id="location_lng" name="location_lng"
                                   value="<?php echo e(old('location_lng', $event?->location_lng)); ?>" data-coordinate="lng"
                                   placeholder="107.609100"
                                   class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300">
                            <?php $__errorArgs = ['location_lng'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                            Simpan
                        </button>
                        <a href="<?php echo e(route('events.index')); ?>"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php /**PATH /Users/aiti/pasopati/resources/views/events/partials/event-form.blade.php ENDPATH**/ ?>