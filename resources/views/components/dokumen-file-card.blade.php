<div 
    @click="selectMode ? toggleDocumentSelection(file) : openFile(file)"
    :class="{
        'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(file.id),
        'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(file.id),
        'cursor-pointer': true
    }"
    class="flex flex-col items-center text-center p-4 border rounded-lg transition relative"
>
    <!-- Checkbox untuk select mode -->
    <div x-show="selectMode" class="absolute top-2 right-2">
        <div :class="isDocumentSelected(file.id) ? 'bg-blue-600 border-blue-600' : 'bg-white border-gray-300'"
            class="w-5 h-5 border-2 rounded flex items-center justify-center">
            <svg x-show="isDocumentSelected(file.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>
    </div>

    <!-- =========================== -->
    <!-- PREVIEW / ICON              -->
    <!-- =========================== -->
    <div class="w-14 h-14 mb-3 flex items-center justify-center overflow-hidden">

        <!-- IMAGE THUMBNAIL -->
        <template x-if="file.type === 'Image'">
            <img 
                :src="file.file_url" 
                alt="Image"
                class="w-full h-full object-cover rounded">
        </template>

        <!-- VIDEO THUMBNAIL -->
        <template x-if="file.type === 'Video'">
            <video 
                :src="file.file_url"
                class="w-full h-full object-cover rounded"
                muted
            ></video>
        </template>

        <!-- DEFAULT ICON -->
        <template x-if="file.type !== 'Image' && file.type !== 'Video'">
            <img 
                :src="file.icon" 
                :alt="file.type" 
                class="w-14 h-14">
        </template>

    </div>

    <!-- Nama File -->
    <span class="text-xs text-gray-600 truncate w-full" x-text="file.name"></span>
    <!-- Tipe File -->
  <div class="flex items-center gap-1">
        <span class="text-xs text-gray-400" x-text="file.type"></span>

        <template x-if="file.isSecret">
            <svg class="w-3 h-3 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </template>
    </div>

</div>
