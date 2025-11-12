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

    <img :src="file.icon" :alt="file.type" class="w-14 h-14 mb-3">
    <span class="text-xs text-gray-600 truncate w-full" x-text="file.name"></span>
</div>
