@extends('layouts.app')

@section('title', 'Mind Map')

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen" x-data="mindmapApp('{{ $mindmap->id }}')">
        <!-- Workspace Nav untuk Mind Map -->
        @include('components.workspace-nav', ['active' => 'mindmap'])

        <!-- Header Section -->
        <div class="container mx-auto px-6 py-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-4xl font-bold text-gray-800 mb-2">Mind Map Proyek</h1>
                    <p class="text-gray-600">Visualisasi ide dan struktur proyek Anda secara interaktif</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button @click="addNode()"
                        class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Node
                    </button>
                    <button @click="resetView()"
                        class="px-5 py-2.5 bg-white text-gray-700 rounded-lg hover:bg-gray-50 transition-all shadow-md hover:shadow-lg flex items-center gap-2 border border-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset View
                    </button>
                </div>
            </div>

            <!-- Mind Map Canvas Container -->
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <!-- Toolbar -->
                <div
                    class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <!-- Zoom Controls -->
                        <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                            <button @click="zoom(-0.1)" class="p-1 hover:bg-gray-100 rounded transition">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                            <span class="text-sm font-medium text-gray-700 min-w-16 text-center"
                                x-text="Math.round(zoomLevel * 100) + '%'">100%</span>
                            <button @click="zoom(0.1)" class="p-1 hover:bg-gray-100 rounded transition">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>

                        <!-- Undo / Redo -->
                        <div class="flex items-center gap-2">
                            <button @click="undo()"
                                :class="undoStack.length > 1 ? 'bg-white text-gray-700 hover:bg-gray-50' : 'bg-gray-50 text-gray-300 cursor-not-allowed'"
                                class="px-3 py-2 rounded-lg border border-gray-200 text-sm font-medium transition"
                                :disabled="undoStack.length <= 1" title="Undo (Ctrl+Z)">
                                ↶ Undo
                            </button>

                            <button @click="redo()"
                                :class="redoStack.length ? 'bg-white text-gray-700 hover:bg-gray-50' : 'bg-gray-50 text-gray-300 cursor-not-allowed'"
                                class="px-3 py-2 rounded-lg border border-gray-200 text-sm font-medium transition"
                                :disabled="redoStack.length === 0" title="Redo (Ctrl+Y)">
                                ↷ Redo
                            </button>
                        </div>

                        <!-- Connection Mode -->
                        <div class="flex items-center gap-2">
                            <button @click="toggleConnectionMode()"
                                :class="isConnecting ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-white text-gray-600 hover:bg-gray-50'"
                                class="px-4 py-2 rounded-lg transition shadow-sm border border-gray-200 text-sm font-medium flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                <span x-text="isConnecting ? 'Mode Koneksi (Klik untuk Batal)' : 'Buat Koneksi'"></span>
                            </button>
                        </div>

                        <!-- Connection Style Toggle -->
                        <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                            <button @click="toggleConnectionStyle()"
                                class="text-sm font-medium text-gray-700 flex items-center gap-2 hover:text-blue-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path x-show="connectionStyle === 'straight'" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2"
                                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                                    <path x-show="connectionStyle === 'curved'" stroke-linecap="round"
                                        stroke-linejoin="round" stroke-width="2"
                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                <span x-text="connectionStyle === 'straight' ? 'Garis Lurus' : 'Garis Lengkung'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Info Stats -->
                    <div class="flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-2 bg-blue-50 px-4 py-2 rounded-lg border border-blue-200">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="font-medium text-blue-700"><span x-text="nodes.length">0</span> Nodes</span>
                        </div>
                        <div class="flex items-center gap-2 bg-green-50 px-4 py-2 rounded-lg border border-green-200">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="font-medium text-green-700"><span x-text="connectionCount">0</span> Connections</span>
                        </div>
                        <div x-show="isSaving" class="flex items-center gap-2 bg-yellow-50 px-4 py-2 rounded-lg border border-yellow-200">
                            <svg class="animate-spin w-4 h-4 text-yellow-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="font-medium text-yellow-700">Menyimpan...</span>
                        </div>
                    </div>
                </div>

                <!-- Canvas Area -->
                <div id="mindmap-area" class="relative"
                    style="height: 700px; background: linear-gradient(to right, #f8fafc 1px, transparent 1px), linear-gradient(to bottom, #f8fafc 1px, transparent 1px); background-size: 20px 20px;">
                    <canvas id="mindmap-canvas" class="absolute inset-0 w-full h-full cursor-move"
                        @mousedown="startPan($event)" @mousemove="pan($event)" @mouseup="endPan()"
                        @mouseleave="endPan()" @wheel.prevent="handleWheel($event)">
                    </canvas>

                    <!-- Node Elements -->
                    <template x-for="node in nodes" :key="node.id">
                        <div :id="`node-${node.id}`" :data-node-id="node.id"
                            class="absolute transform -translate-x-1/2 -translate-y-1/2"
                            :style="`left: ${node.x * zoomLevel + panX}px; top: ${node.y * zoomLevel + panY}px; transform: scale(${zoomLevel}) translate(-50%, -50%); transform-origin: center;`"
                            @mousedown.stop="startDrag(node, $event)"
                            @dblclick.stop="editNode(node)"
                            @click.stop="handleNodeClick(node)">

                            <div class="group relative">
                                <!-- Node Card -->
                                <div
                                    :class="[
                                        'px-6 py-4 rounded-xl shadow-lg hover:shadow-2xl transition-all cursor-move border-2 min-w-48 max-w-64',
                                        node.isRoot ?
                                        'bg-gradient-to-br from-blue-600 to-indigo-600 text-white border-blue-700' :
                                        node.type === 'idea' ?
                                        'bg-gradient-to-br from-purple-500 to-pink-500 text-white border-purple-600' :
                                        node.type === 'task' ?
                                        'bg-gradient-to-br from-green-500 to-emerald-500 text-white border-green-600' :
                                        'bg-white text-gray-800 border-gray-300',
                                        isConnecting && connectionSource?.id === node.id ?
                                        'ring-4 ring-yellow-400 ring-opacity-50' : ''
                                    ]">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1">
                                            <div class="font-bold text-lg mb-1 truncate" x-text="node.title"></div>
                                            <div :class="node.isRoot || node.type === 'idea' || node.type === 'task' ?
                                                'text-white/90' : 'text-gray-600'"
                                                class="text-sm line-clamp-2" x-text="node.description"></div>

                                            <!-- Child Count Badge -->
                                            <div x-show="getChildCount(node.id) > 0" class="mt-2">
                                                <span
                                                    :class="node.isRoot || node.type === 'idea' || node.type === 'task' ?
                                                        'bg-white/20 text-white' : 'bg-gray-100 text-gray-700'"
                                                    class="text-xs px-2 py-1 rounded-full">
                                                    <span x-text="getChildCount(node.id)"></span> children
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Node Actions -->
                                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click.stop="addChildNode(node)"
                                                :class="node.isRoot || node.type === 'idea' || node.type === 'task' ?
                                                    'text-white hover:bg-white/20' : 'text-gray-600 hover:bg-gray-100'"
                                                class="p-1.5 rounded transition" title="Tambah Child Node">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                            <button @click.stop="removeConnection(node)"
                                                x-show="node.parentId"
                                                :class="node.isRoot || node.type === 'idea' || node.type === 'task' ?
                                                    'text-white hover:bg-white/20' : 'text-gray-600 hover:bg-gray-100'"
                                                class="p-1.5 rounded transition" title="Hapus Koneksi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 7l4-4m0 0l4 4m-4-4v18m0 0l-4-4m4 4l4-4" />
                                                </svg>
                                            </button>
                                            <button @click.stop="deleteNode(node)"
                                                x-show="!node.isRoot"
                                                :class="node.isRoot || node.type === 'idea' || node.type === 'task' ?
                                                    'text-white hover:bg-white/20' : 'text-gray-600 hover:bg-gray-100'"
                                                class="p-1.5 rounded transition" title="Hapus Node">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Connection Points -->
                                <div class="absolute -top-2 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-md hover:bg-blue-600 hover:scale-125 cursor-pointer transition-all"
                                    @click.stop="handleConnectionPointClick(node, 'top')"
                                    :class="isConnecting ? 'animate-pulse ring-2 ring-yellow-400' : ''">
                                </div>
                                <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-md hover:bg-blue-600 hover:scale-125 cursor-pointer transition-all"
                                    @click.stop="handleConnectionPointClick(node, 'bottom')"
                                    :class="isConnecting ? 'animate-pulse ring-2 ring-yellow-400' : ''">
                                </div>
                                <div class="absolute -left-2 top-1/2 transform -translate-y-1/2 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-md hover:bg-blue-600 hover:scale-125 cursor-pointer transition-all"
                                    @click.stop="handleConnectionPointClick(node, 'left')"
                                    :class="isConnecting ? 'animate-pulse ring-2 ring-yellow-400' : ''">
                                </div>
                                <div class="absolute -right-2 top-1/2 transform -translate-y-1/2 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-md hover:bg-blue-600 hover:scale-125 cursor-pointer transition-all"
                                    @click.stop="handleConnectionPointClick(node, 'right')"
                                    :class="isConnecting ? 'animate-pulse ring-2 ring-yellow-400' : ''">
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- <!-- Empty State -->
                    <div x-show="nodes.length === 0" class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Mind Map</h3>
                            <p class="text-gray-500 mb-4">Klik tombol "Tambah Node" untuk memulai</p>
                            <button @click="addNode()"
                                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg">
                                Buat Node Pertama
                            </button>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Node Editor Modal -->
            <div x-show="showModal" x-cloak @click.self="closeModal()"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                <div @click.stop class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform transition-all">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 rounded-t-2xl">
                        <h3 class="text-xl font-bold" x-text="editingNode.id ? 'Edit Node' : 'Tambah Node'"></h3>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Judul *</label>
                            <input x-model="editingNode.title" type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                placeholder="Masukkan judul node" @keydown.enter="saveNode()">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <textarea x-model="editingNode.description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                placeholder="Masukkan deskripsi node"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Node</label>
                            <select x-model="editingNode.type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <option value="default">Default</option>
                                <option value="idea">Idea</option>
                                <option value="task">Task</option>
                            </select>
                        </div>

                    <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end gap-3">
                        <button @click="closeModal()"
                            class="px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition">
                            Batal
                        </button>
                        <button @click="saveNode()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function mindmapApp(mindmapId) {
                return {
                    mindmapId: mindmapId,
                    nodes: [],
                    canvas: null,
                    ctx: null,
                    zoomLevel: 1,
                    panX: 0,
                    panY: 0,
                    isPanning: false,
                    panStartX: 0,
                    panStartY: 0,
                    draggingNode: null,
                    dragStartX: 0,
                    dragStartY: 0,
                    showModal: false,
                    editingNode: {},
                    nodeIdCounter: 1,
                    animationFrame: null,
                    pendingDrag: null,
                    autoSaveTimeout: null,
                    isSaving: false,
                    isLoading: false,

                    // Canvas boundaries
                    canvasPadding: 50,
                    minX: 0,
                    maxX: 0,
                    minY: 0,
                    maxY: 0,

                    // Undo / Redo
                    undoStack: [],
                    redoStack: [],
                    maxHistory: 60,

                    // Connection mode
                    isConnecting: false,
                    connectionSource: null,
                    connectionSourceSide: null,
                    tempConnectionTarget: null,
                    connectionStyle: 'curved',

                    // Computed property
                    get connectionCount() {
                        return this.nodes.filter(node => node.parentId).length;
                    },

                    async init() {
                        this.canvas = document.getElementById('mindmap-canvas');
                        this.ctx = this.canvas.getContext('2d');
                        this.resizeCanvas();
                        this.updateCanvasBoundaries();

                        window.addEventListener('resize', () => {
                            this.resizeCanvas();
                            this.updateCanvasBoundaries();
                            this.drawConnections();
                        });

                        // Load data dari database
                        await this.loadMindmapData();

                        // Event listeners
                        this.handleTempConnectionMove = this.handleTempConnectionMove.bind(this);
                        document.addEventListener('mousemove', this.handleTempConnectionMove);

                        // Keyboard shortcuts
                        this.keyHandler = (e) => {
                            if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key.toLowerCase() === 'z') {
                                e.preventDefault();
                                this.undo();
                            } else if ((e.ctrlKey || e.metaKey) && (e.key.toLowerCase() === 'y' || (e.shiftKey && e.key.toLowerCase() === 'z'))) {
                                e.preventDefault();
                                this.redo();
                            }
                        };
                        document.addEventListener('keydown', this.keyHandler);

                        // Continuous redraw
                        this.redrawLoop();

                        // Watch untuk perubahan
                        this.$watch('nodes', () => {
                            if (this.nodes.length > 0 && !this.isLoading) {
                                this.autoSave();
                            }
                            this.scheduleRedraw();
                        }, { deep: true });
                    },

                    // ========== DATABASE METHODS ==========
                    async saveToDatabase() {
                        if (this.isSaving || this.isLoading) return;

                        this.isSaving = true;

                        try {
                            const response = await fetch(`/mindmap/${this.mindmapId}/save`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    nodes: this.nodes.map(node => ({
                                        ...node,
                                        id: String(node.id)
                                    }))
                                })
                            });

                            if (!response.ok) {
                                throw new Error('Failed to save mindmap');
                            }

                            const result = await response.json();
                            console.log('Mindmap saved:', result);

                            // Tampilkan notifikasi sukses
                            this.showNotification('Tersimpan!', 'success');
                        } catch (error) {
                            console.error('Save error:', error);
                            this.showNotification('Gagal menyimpan: ' + error.message, 'error');
                        } finally {
                            this.isSaving = false;
                        }
                    },

                    autoSave() {
                        clearTimeout(this.autoSaveTimeout);
                        this.autoSaveTimeout = setTimeout(() => {
                            this.saveToDatabase();
                        }, 1500);
                    },

                    async loadMindmapData() {
                        this.isLoading = true;
                        try {
                            const response = await fetch(`/mindmap/${this.mindmapId}/data`);
                            const data = await response.json();

                            if (data.nodes && data.nodes.length > 0) {
                                this.nodes = data.nodes.map(node => ({
                                    ...node,
                                    id: String(node.id),
                                    parentId: node.parentId ? String(node.parentId) : null
                                }));

                                // Update counter berdasarkan ID tertinggi
                                const maxId = Math.max(...this.nodes.map(n => {
                                    const num = parseInt(n.id);
                                    return isNaN(num) ? 0 : num;
                                }), 0);
                                this.nodeIdCounter = maxId + 1;
                            } else {
                                // Tidak membuat node default - user harus klik tambah node dulu
                                this.nodes = [];
                            }

                            this.pushHistory('load');
                        } catch (error) {
                            console.error('Failed to load mindmap data:', error);
                            this.nodes = [];
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    showNotification(message, type = 'info') {
                        // Implementasi notifikasi sederhana
                        console.log(`[${type.toUpperCase()}] ${message}`);
                    },

                    // ========== CANVAS METHODS ==========
                    updateCanvasBoundaries() {
                        const rect = this.canvas.getBoundingClientRect();
                        this.minX = this.canvasPadding;
                        this.maxX = rect.width - this.canvasPadding;
                        this.minY = this.canvasPadding;
                        this.maxY = rect.height - this.canvasPadding;
                    },

                    redrawLoop() {
                        this.applyPendingDrag();
                        this.drawConnections();
                        this.animationFrame = requestAnimationFrame(() => this.redrawLoop());
                    },

                    scheduleRedraw() {
                        // Redraw sudah continuous, tidak perlu schedule
                    },

                    resizeCanvas() {
                        const rect = this.canvas.getBoundingClientRect();
                        this.canvas.width = rect.width;
                        this.canvas.height = rect.height;
                        this.ctx.imageSmoothingEnabled = true;
                        this.ctx.imageSmoothingQuality = 'high';
                    },

                    drawConnections() {
                        if (!this.ctx) return;

                        this.ctx.fillStyle = '#f8fafc';
                        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
                        this.drawGrid();

                        // Draw all connections
                        this.nodes.forEach(node => {
                            if (node.parentId) {
                                const parent = this.nodes.find(n => n.id === node.parentId);
                                if (parent) {
                                    this.drawConnection(parent, node);
                                }
                            }
                        });

                        // Draw temporary connection line
                        if (this.isConnecting && this.connectionSource && this.tempConnectionTarget) {
                            this.drawTempConnection();
                        }
                    },

                    drawGrid() {
                        const gridSize = 20 * this.zoomLevel;
                        this.ctx.strokeStyle = '#e2e8f0';
                        this.ctx.lineWidth = 0.5;

                        const offsetX = ((this.panX % gridSize) + gridSize) % gridSize;
                        const offsetY = ((this.panY % gridSize) + gridSize) % gridSize;

                        this.ctx.beginPath();
                        for (let x = offsetX; x < this.canvas.width; x += gridSize) {
                            this.ctx.moveTo(x, 0);
                            this.ctx.lineTo(x, this.canvas.height);
                        }
                        for (let y = offsetY; y < this.canvas.height; y += gridSize) {
                            this.ctx.moveTo(0, y);
                            this.ctx.lineTo(this.canvas.width, y);
                        }
                        this.ctx.stroke();
                    },

                    drawConnection(from, to) {
                        const canvasRect = this.canvas.getBoundingClientRect();
                        let startCenterX, startCenterY, endCenterX, endCenterY;

                        const fromEl = document.getElementById(`node-${from.id}`);
                        const toEl = document.getElementById(`node-${to.id}`);

                        if (fromEl && toEl) {
                            const fRect = fromEl.getBoundingClientRect();
                            const tRect = toEl.getBoundingClientRect();
                            startCenterX = fRect.left + fRect.width / 2 - canvasRect.left;
                            startCenterY = fRect.top + fRect.height / 2 - canvasRect.top;
                            endCenterX = tRect.left + tRect.width / 2 - canvasRect.left;
                            endCenterY = tRect.top + tRect.height / 2 - canvasRect.top;
                        } else {
                            startCenterX = from.x * this.zoomLevel + this.panX;
                            startCenterY = from.y * this.zoomLevel + this.panY;
                            endCenterX = to.x * this.zoomLevel + this.panX;
                            endCenterY = to.y * this.zoomLevel + this.panY;
                        }

                        let strokeColor = '#60a5fa';
                        if (to.type === 'idea') strokeColor = '#a855f7';
                        if (to.type === 'task') strokeColor = '#10b981';

                        const fromSide = this.getConnectionSide(from, to);
                        const toSide = this.getConnectionSide(to, from);

                        const fromRect = fromEl ? fromEl.getBoundingClientRect() : null;
                        const toRect = toEl ? toEl.getBoundingClientRect() : null;

                        const startPoint = this.getConnectionPoint(from, fromSide, startCenterX, startCenterY, fromRect);
                        const endPoint = this.getConnectionPoint(to, toSide, endCenterX, endCenterY, toRect);

                        this.ctx.beginPath();

                        if (this.connectionStyle === 'straight') {
                            this.drawStraightLine(startPoint, endPoint);
                        } else {
                            this.drawCurvedLine(startPoint, endPoint);
                        }

                        this.ctx.strokeStyle = strokeColor;
                        this.ctx.lineWidth = Math.max(2, 3 * this.zoomLevel);
                        this.ctx.lineCap = 'round';
                        this.ctx.lineJoin = 'round';
                        this.ctx.stroke();

                        this.drawArrow(endPoint.x, endPoint.y, endPoint.angle, strokeColor);
                    },

                    getConnectionSide(from, to) {
                        if (to.connectionSide && to.connectionSide !== 'auto') {
                            return to.connectionSide;
                        }

                        const dx = to.x - from.x;
                        const dy = to.y - from.y;

                        if (Math.abs(dx) > Math.abs(dy)) {
                            return dx > 0 ? 'right' : 'left';
                        } else {
                            return dy > 0 ? 'bottom' : 'top';
                        }
                    },

                    getConnectionPoint(node, side, screenX, screenY, elRect = null) {
                        let offsetX, offsetY;
                        if (elRect) {
                            offsetX = elRect.width / 2;
                            offsetY = elRect.height / 2;
                        } else {
                            const nodeWidth = 192;
                            const nodeHeight = 80;
                            offsetX = (nodeWidth / 2) * this.zoomLevel;
                            offsetY = (nodeHeight / 2) * this.zoomLevel;
                        }

                        let x = screenX;
                        let y = screenY;
                        let angle = 0;

                        switch (side) {
                            case 'top':
                                y = screenY - offsetY;
                                angle = -Math.PI / 2;
                                break;
                            case 'bottom':
                                y = screenY + offsetY;
                                angle = Math.PI / 2;
                                break;
                            case 'left':
                                x = screenX - offsetX;
                                angle = Math.PI;
                                break;
                            case 'right':
                                x = screenX + offsetX;
                                angle = 0;
                                break;
                        }

                        return { x, y, angle };
                    },

                    drawStraightLine(start, end) {
                        const midX = (start.x + end.x) / 2;
                        const midY = (start.y + end.y) / 2;

                        this.ctx.moveTo(start.x, start.y);

                        if (Math.abs(start.x - end.x) > Math.abs(start.y - end.y)) {
                            this.ctx.lineTo(midX, start.y);
                            this.ctx.lineTo(midX, end.y);
                        } else {
                            this.ctx.lineTo(start.x, midY);
                            this.ctx.lineTo(end.x, midY);
                        }

                        this.ctx.lineTo(end.x, end.y);
                    },

                    drawCurvedLine(start, end) {
                        const dx = end.x - start.x;
                        const dy = end.y - start.y;
                        const distance = Math.sqrt(dx * dx + dy * dy);
                        const controlOffset = Math.min(distance * 0.3, 100);

                        this.ctx.moveTo(start.x, start.y);

                        if (Math.abs(dx) > Math.abs(dy)) {
                            const controlX = start.x + dx * 0.5;
                            const controlY = start.y + (dy > 0 ? -controlOffset : controlOffset);
                            this.ctx.quadraticCurveTo(controlX, controlY, end.x, end.y);
                        } else {
                            const controlX = start.x + (dx > 0 ? -controlOffset : controlOffset);
                            const controlY = start.y + dy * 0.5;
                            this.ctx.quadraticCurveTo(controlX, controlY, end.x, end.y);
                        }
                    },

                    drawArrow(x, y, angle, color) {
                        const arrowLength = 12 * this.zoomLevel;
                        const arrowWidth = 8 * this.zoomLevel;

                        this.ctx.beginPath();
                        this.ctx.moveTo(x, y);
                        this.ctx.lineTo(
                            x - arrowLength * Math.cos(angle) - arrowWidth * Math.sin(angle),
                            y - arrowLength * Math.sin(angle) + arrowWidth * Math.cos(angle)
                        );
                        this.ctx.lineTo(
                            x - arrowLength * Math.cos(angle) + arrowWidth * Math.sin(angle),
                            y - arrowLength * Math.sin(angle) - arrowWidth * Math.cos(angle)
                        );
                        this.ctx.closePath();
                        this.ctx.fillStyle = color;
                        this.ctx.fill();
                    },

                    drawTempConnection() {
                        const canvasRect = this.canvas.getBoundingClientRect();
                        const sourceEl = document.getElementById(`node-${this.connectionSource.id}`);
                        let startX, startY;

                        if (sourceEl) {
                            const fRect = sourceEl.getBoundingClientRect();
                            startX = fRect.left + fRect.width / 2 - canvasRect.left;
                            startY = fRect.top + fRect.height / 2 - canvasRect.top;
                        } else {
                            startX = this.connectionSource.x * this.zoomLevel + this.panX;
                            startY = this.connectionSource.y * this.zoomLevel + this.panY;
                        }

                        const endX = this.tempConnectionTarget.x;
                        const endY = this.tempConnectionTarget.y;

                        const fromRect = sourceEl ? sourceEl.getBoundingClientRect() : null;
                        const startPoint = this.getConnectionPoint(this.connectionSource, this.connectionSourceSide, startX, startY, fromRect);

                        this.ctx.beginPath();

                        if (this.connectionStyle === 'straight') {
                            this.drawStraightLine(startPoint, { x: endX, y: endY });
                        } else {
                            this.drawCurvedLine(startPoint, { x: endX, y: endY });
                        }

                        this.ctx.strokeStyle = '#f59e0b';
                        this.ctx.lineWidth = 2;
                        this.ctx.setLineDash([5, 5]);
                        this.ctx.stroke();
                        this.ctx.setLineDash([]);
                    },

                    // ========== NODE MANAGEMENT ==========
                    addNode() {
                        const centerX = (this.canvas.width / 2 - this.panX) / this.zoomLevel;
                        const centerY = (this.canvas.height / 2 - this.panY) / this.zoomLevel;

                        // Cek apakah ini node pertama (akan menjadi root)
                        const isFirstNode = this.nodes.length === 0;

                        this.editingNode = {
                            id: null,
                            title: '',
                            description: '',
                            x: centerX,
                            y: centerY,
                            isRoot: isFirstNode,
                            type: 'default',
                            parentId: null,
                            connectionSide: 'auto'
                        };
                        this.showModal = true;
                    },

                    addChildNode(parent) {
                        const angle = Math.random() * Math.PI * 2;
                        const distance = 200;

                        this.editingNode = {
                            id: null,
                            title: '',
                            description: '',
                            x: parent.x + Math.cos(angle) * distance,
                            y: parent.y + Math.sin(angle) * distance,
                            isRoot: false,
                            type: 'default',
                            parentId: parent.id,
                            connectionSide: 'auto'
                        };
                        this.showModal = true;
                    },

                    getChildCount(nodeId) {
                        return this.nodes.filter(node => node.parentId === nodeId).length;
                    },

                    editNode(node) {
                        this.editingNode = { ...node };
                        this.showModal = true;
                    },

                    saveNode() {
                        if (!this.editingNode.title?.trim()) {
                            alert('Judul node tidak boleh kosong!');
                            return;
                        }

                        // Validasi posisi node agar tidak keluar batas
                        const boundedPos = this.getBoundedPosition(this.editingNode.x, this.editingNode.y);
                        this.editingNode.x = boundedPos.x;
                        this.editingNode.y = boundedPos.y;

                        if (this.editingNode.id) {
                            // Edit existing node
                            const index = this.nodes.findIndex(n => n.id === this.editingNode.id);
                            if (index !== -1) {
                                this.nodes[index] = { ...this.editingNode };
                            }
                            this.pushHistory('editNode');
                        } else {
                            // Add new node
                            this.editingNode.id = String(this.nodeIdCounter++);
                            this.nodes.push({ ...this.editingNode });
                            this.pushHistory('addNode');
                        }

                        this.closeModal();
                        this.saveToDatabase();
                    },

                    deleteNode(node) {
                        if (node.isRoot) {
                            alert('Node utama tidak dapat dihapus!');
                            return;
                        }

                        if (!confirm(`Hapus node "${node.title}"?`)) {
                            return;
                        }

                        // Hapus node dan semua koneksi yang terkait
                        this.nodes = this.nodes.filter(n => n.id !== node.id);

                        // Hapus parentId dari node yang memiliki parent yang dihapus
                        this.nodes.forEach(n => {
                            if (n.parentId === node.id) {
                                n.parentId = null;
                            }
                        });

                        this.pushHistory('deleteNode');
                        this.saveToDatabase();
                    },

                    removeConnection(node) {
                        if (!confirm(`Hapus koneksi dari node "${node.title}"?`)) {
                            return;
                        }

                        node.parentId = null;
                        this.pushHistory('removeConnection');
                        this.saveToDatabase();
                    },

                    closeModal() {
                        this.showModal = false;
                        this.editingNode = {};
                    },

                    // ========== CONNECTION MANAGEMENT ==========
                    toggleConnectionMode() {
                        this.isConnecting = !this.isConnecting;
                        if (!this.isConnecting) {
                            this.connectionSource = null;
                            this.connectionSourceSide = null;
                            this.tempConnectionTarget = null;
                        }
                        this.canvas.style.cursor = this.isConnecting ? 'crosshair' : 'move';
                    },

                    toggleConnectionStyle() {
                        this.connectionStyle = this.connectionStyle === 'straight' ? 'curved' : 'straight';
                    },

                    handleConnectionPointClick(node, side) {
                        if (!this.isConnecting) {
                            this.toggleConnectionMode();
                            this.connectionSource = node;
                            this.connectionSourceSide = side;
                            return;
                        }

                        if (this.connectionSource && this.connectionSource.id !== node.id) {
                            if (node.parentId === this.connectionSource.id) {
                                alert('Koneksi sudah ada!');
                            } else if (this.willCreateCycle(this.connectionSource.id, node.id)) {
                                alert('Tidak dapat membuat koneksi sirkular!');
                            } else {
                                this.createConnection(this.connectionSource, node, side);
                                this.pushHistory('createConnection');
                                this.saveToDatabase();
                            }
                        }

                        this.toggleConnectionMode();
                    },

                    createConnection(source, target, targetSide) {
                        target.parentId = source.id;
                        target.connectionSide = this.getOppositeSide(targetSide);
                    },

                    getOppositeSide(side) {
                        const opposites = {
                            'top': 'bottom',
                            'bottom': 'top',
                            'left': 'right',
                            'right': 'left'
                        };
                        return opposites[side] || 'auto';
                    },

                    handleNodeClick(node) {
                        if (this.isConnecting && this.connectionSource && this.connectionSource.id !== node.id) {
                            this.connectionSourceSide = this.getConnectionSide(this.connectionSource, node);
                            this.handleConnectionPointClick(node, this.getOppositeSide(this.connectionSourceSide));
                        }
                    },

                    handleTempConnectionMove(event) {
                        if (this.isConnecting && this.connectionSource) {
                            const rect = this.canvas.getBoundingClientRect();
                            this.tempConnectionTarget = {
                                x: event.clientX - rect.left,
                                y: event.clientY - rect.top
                            };
                        }
                    },

                    willCreateCycle(sourceId, targetId) {
                        let current = targetId;
                        const visited = new Set();

                        while (current) {
                            if (visited.has(current)) return false;
                            visited.add(current);

                            const node = this.nodes.find(n => n.id === current);
                            if (!node || !node.parentId) break;
                            if (node.parentId === sourceId) return true;
                            current = node.parentId;
                        }
                        return false;
                    },

                    // ========== DRAG & PAN ==========
                    startDrag(node, event) {
                        if (this.isConnecting) return;

                        this.draggingNode = node;
                        const rect = this.canvas.getBoundingClientRect();

                        this.dragStartX = (event.clientX - rect.left - this.panX) / this.zoomLevel;
                        this.dragStartY = (event.clientY - rect.top - this.panY) / this.zoomLevel;

                        this.handleDragBound = this.handleDrag.bind(this);
                        this.stopDragBound = this.stopDrag.bind(this);

                        document.addEventListener('mousemove', this.handleDragBound);
                        document.addEventListener('mouseup', this.stopDragBound);

                        this.draggedElement = event.currentTarget;
                        if (this.draggedElement) {
                            this.draggedElement.style.transition = 'none';
                            this.draggedElement.style.zIndex = '1000';
                        }

                        event.stopPropagation();
                    },

                    handleDrag(event) {
                        if (this.draggingNode && !this.isConnecting) {
                            const rect = this.canvas.getBoundingClientRect();
                            const newX = (event.clientX - rect.left - this.panX) / this.zoomLevel;
                            const newY = (event.clientY - rect.top - this.panY) / this.zoomLevel;

                            // Apply boundaries
                            const boundedPos = this.getBoundedPosition(newX, newY);
                            this.pendingDrag = { x: boundedPos.x, y: boundedPos.y };
                        }
                    },

                    stopDrag() {
                        if (this.draggingNode) {
                            this.draggingNode = null;
                            document.removeEventListener('mousemove', this.handleDragBound);
                            document.removeEventListener('mouseup', this.stopDragBound);

                            if (this.draggedElement) {
                                this.draggedElement.style.transition = '';
                                this.draggedElement.style.zIndex = '';
                                this.draggedElement = null;
                            }

                            this.drawConnections();
                            this.pushHistory('dragEnd');
                            this.saveToDatabase();
                        }
                    },

                    applyPendingDrag() {
                        if (this.pendingDrag && this.draggingNode) {
                            this.draggingNode.x = this.pendingDrag.x;
                            this.draggingNode.y = this.pendingDrag.y;
                            this.pendingDrag = null;
                        }
                    },

                    getBoundedPosition(x, y) {
                        // Convert world coordinates to screen coordinates for boundary check
                        const screenX = x * this.zoomLevel + this.panX;
                        const screenY = y * this.zoomLevel + this.panY;

                        let boundedX = x;
                        let boundedY = y;

                        if (screenX < this.minX) {
                            boundedX = (this.minX - this.panX) / this.zoomLevel;
                        } else if (screenX > this.maxX) {
                            boundedX = (this.maxX - this.panX) / this.zoomLevel;
                        }

                        if (screenY < this.minY) {
                            boundedY = (this.minY - this.panY) / this.zoomLevel;
                        } else if (screenY > this.maxY) {
                            boundedY = (this.maxY - this.panY) / this.zoomLevel;
                        }

                        return { x: boundedX, y: boundedY };
                    },

                    startPan(event) {
                        if (event.target === this.canvas && !this.isConnecting && !this.draggingNode) {
                            this.isPanning = true;
                            this.panStartX = event.clientX - this.panX;
                            this.panStartY = event.clientY - this.panY;
                            this.canvas.style.cursor = 'grabbing';
                        }
                    },

                    pan(event) {
                        if (this.isPanning) {
                            this.panX = event.clientX - this.panStartX;
                            this.panY = event.clientY - this.panStartY;
                        }
                    },

                    endPan() {
                        this.isPanning = false;
                        this.canvas.style.cursor = this.isConnecting ? 'crosshair' : 'move';
                    },

                    zoom(delta) {
                        const oldZoom = this.zoomLevel;
                        this.zoomLevel = Math.max(0.1, Math.min(3, this.zoomLevel + delta));

                        const zoomFactor = this.zoomLevel / oldZoom;
                        this.panX = this.canvas.width / 2 - (this.canvas.width / 2 - this.panX) * zoomFactor;
                        this.panY = this.canvas.height / 2 - (this.canvas.height / 2 - this.panY) * zoomFactor;
                    },

                    handleWheel(event) {
                        event.preventDefault();
                        const delta = event.deltaY > 0 ? -0.1 : 0.1;
                        const zoomPointX = event.clientX - this.canvas.getBoundingClientRect().left;
                        const zoomPointY = event.clientY - this.canvas.getBoundingClientRect().top;

                        const oldZoom = this.zoomLevel;
                        this.zoomLevel = Math.max(0.1, Math.min(3, this.zoomLevel + delta));

                        const zoomFactor = this.zoomLevel / oldZoom;
                        this.panX = zoomPointX - (zoomPointX - this.panX) * zoomFactor;
                        this.panY = zoomPointY - (zoomPointY - this.panY) * zoomFactor;
                    },

                    resetView() {
                        this.zoomLevel = 1;
                        this.panX = 0;
                        this.panY = 0;
                        this.pushHistory('resetView');
                    },

                    // ========== HISTORY (UNDO/REDO) ==========
                    createSnapshot() {
                        return JSON.parse(JSON.stringify({
                            nodes: this.nodes,
                            nodeIdCounter: this.nodeIdCounter,
                            panX: this.panX,
                            panY: this.panY,
                            zoomLevel: this.zoomLevel
                        }));
                    },

                    pushHistory(desc = '') {
                        try {
                            const snap = this.createSnapshot();
                            this.undoStack.push({ snap, desc });
                            if (this.undoStack.length > this.maxHistory) this.undoStack.shift();
                            this.redoStack = [];
                        } catch (e) {
                            console.warn('pushHistory error', e);
                        }
                    },

                    applySnapshot(snap) {
                        if (!snap) return;
                        this.nodes = snap.nodes.map(n => ({ ...n }));
                        this.nodeIdCounter = snap.nodeIdCounter;
                        this.panX = snap.panX;
                        this.panY = snap.panY;
                        this.zoomLevel = snap.zoomLevel;
                        this.pendingDrag = null;
                        this.drawConnections();
                    },

                    undo() {
                        if (this.undoStack.length <= 1) return;

                        const current = this.undoStack.pop();
                        this.redoStack.push(current);
                        const last = this.undoStack[this.undoStack.length - 1];
                        if (last && last.snap) {
                            this.applySnapshot(last.snap);
                            this.saveToDatabase();
                        }
                    },

                    redo() {
                        if (this.redoStack.length === 0) return;

                        const next = this.redoStack.pop();
                        const currentSnap = this.createSnapshot();
                        this.undoStack.push({ snap: currentSnap, desc: 'redo-preserve' });
                        this.applySnapshot(next.snap);
                        this.saveToDatabase();
                    },

                    // Cleanup
                    destroy() {
                        if (this.animationFrame) {
                            cancelAnimationFrame(this.animationFrame);
                        }
                        document.removeEventListener('mousemove', this.handleTempConnectionMove);
                        document.removeEventListener('keydown', this.keyHandler);
                    }
                }
            }
        </script>
    @endpush

    @push('styles')
        <style>
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            #mindmap-canvas {
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
                transform: translateZ(0);
                backface-visibility: hidden;
            }

            .cursor-move {
                cursor: grab;
            }

            .cursor-move:active {
                cursor: grabbing;
            }

            [x-for="node in nodes"] {
                will-change: transform;
                pointer-events: auto;
            }

            .group:hover {
                filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
            }

            .absolute.w-4.h-4 {
                transition: all 0.2s ease;
                z-index: 20;
                pointer-events: auto;
            }

            .absolute.w-4.h-4:hover {
                transform: scale(1.3);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            }

            @keyframes connection-pulse {
                0%, 100% {
                    transform: scale(1);
                    opacity: 1;
                }
                50% {
                    transform: scale(1.2);
                    opacity: 0.8;
                }
            }

            .animate-pulse {
                animation: connection-pulse 1.5s ease-in-out infinite;
            }

            .backdrop-blur-sm {
                backdrop-filter: blur(8px);
            }
        </style>
    @endpush
@endsection
