@extends('layouts.app')

@section('title', 'Mind Map')

<style>
    [x-cloak] { display: none !important; }
</style>

@section('content')
<div class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen" x-data="mindmapApp()">
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
                <button @click="addNode()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Node
                </button>
                <button @click="resetView()" class="px-5 py-2.5 bg-white text-gray-700 rounded-lg hover:bg-gray-50 transition-all shadow-md hover:shadow-lg flex items-center gap-2 border border-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset View
                </button>
                <!-- Tombol untuk mengekspor mindmap

                <button @click="exportMindmap()" class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export
                </button>

                -->
            </div>
        </div>

        <!-- Mind Map Canvas Container -->
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
            <!-- Toolbar -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Zoom Controls -->
                    <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                        <button @click="zoom(-0.1)" class="p-1 hover:bg-gray-100 rounded transition">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </button>
                        <span class="text-sm font-medium text-gray-700 min-w-16 text-center" x-text="Math.round(zoomLevel * 100) + '%'">100%</span>
                        <button @click="zoom(0.1)" class="p-1 hover:bg-gray-100 rounded transition">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>


                    <!-- Undo / Redo (baru) -->
    <div class="flex items-center gap-2">
        <button
            @click="undo()"
            :class="undoStack.length ? 'bg-white text-gray-700' : 'bg-gray-50 text-gray-300 cursor-not-allowed'"
            class="px-3 py-2 rounded-lg border border-gray-200 text-sm font-medium"
            :disabled="undoStack.length === 0"
            title="Undo (Ctrl+Z)">
            ↶ Undo
        </button>

        <button
            @click="redo()"
            :class="redoStack.length ? 'bg-white text-gray-700' : 'bg-gray-50 text-gray-300 cursor-not-allowed'"
            class="px-3 py-2 rounded-lg border border-gray-200 text-sm font-medium"
            :disabled="redoStack.length === 0"
            title="Redo (Ctrl+Y)">
            ↷ Redo
        </button>
    </div>

                    <!-- Connection Mode -->
                    <div class="flex items-center gap-2">
                        <button @click="toggleConnectionMode()"
                                :class="isConnecting ? 'bg-red-600 text-white' : 'bg-white text-gray-600'"
                                class="px-4 py-2 rounded-lg transition shadow-sm border border-gray-200 text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span x-text="isConnecting ? 'Mode Koneksi (Klik untuk Batal)' : 'Buat Koneksi'"></span>
                        </button>
                    </div>

                    <!-- Connection Style Toggle -->
                    <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                        <button @click="toggleConnectionStyle()" class="text-sm font-medium text-gray-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="connectionStyle === 'straight'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"/>
                                <path x-show="connectionStyle === 'curved'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span x-text="connectionStyle === 'straight' ? 'Garis Lurus' : 'Garis Lengkung'"></span>
                        </button>
                    </div>
                </div>

                <!-- Info Stats -->
                <div class="flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-2 bg-blue-50 px-4 py-2 rounded-lg border border-blue-200">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="font-medium text-blue-700"><span x-text="nodes.length">0</span> Nodes</span>
                    </div>
                    <div class="flex items-center gap-2 bg-green-50 px-4 py-2 rounded-lg border border-green-200">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="font-medium text-green-700"><span x-text="connectionCount">0</span> Connections</span>
                    </div>
                </div>

            </div>

            <!-- Canvas Area -->
            <div id="mindmap-area" class="relative" style="height: 700px; background: linear-gradient(to right, #f8fafc 1px, transparent 1px), linear-gradient(to bottom, #f8fafc 1px, transparent 1px); background-size: 20px 20px;">
                <canvas id="mindmap-canvas"
                        class="absolute inset-0 w-full h-full cursor-move"
                        @mousedown="startPan($event)"
                        @mousemove="pan($event)"
                        @mouseup="endPan()"
                        @mouseleave="endPan()"
                        @wheel.prevent="handleWheel($event)">
                </canvas>

                <!-- Node Elements -->
                <template x-for="node in nodes" :key="node.id">
                    <div :id="`node-${node.id}`" :data-node-id="node.id" class="absolute transform -translate-x-1/2 -translate-y-1/2 transition-all duration-200"
                         :style="`left: ${node.x * zoomLevel + panX}px; top: ${node.y * zoomLevel + panY}px; transform: scale(${zoomLevel}) translate(-50%, -50%);`"
                         @mousedown.stop="startDrag(node, $event)"
                         @dblclick="editNode(node)"
                         @click="handleNodeClick(node)">

                        <div class="group relative">
                            <!-- Node Card -->
                            <div :class="[
                                'px-6 py-4 rounded-xl shadow-lg hover:shadow-2xl transition-all cursor-move border-2 min-w-48 max-w-64',
                                node.isRoot ? 'bg-gradient-to-br from-blue-600 to-indigo-600 text-white border-blue-700' :
                                node.type === 'idea' ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white border-purple-600' :
                                node.type === 'task' ? 'bg-gradient-to-br from-green-500 to-emerald-500 text-white border-green-600' :
                                'bg-white text-gray-800 border-gray-300',
                                isConnecting && connectionSource?.id === node.id ? 'ring-4 ring-yellow-400 ring-opacity-50' : ''
                            ]">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1">
                                        <div class="font-bold text-lg mb-1 truncate" x-text="node.title"></div>
                                        <div :class="node.isRoot || node.type === 'idea' || node.type === 'task' ? 'text-white/90' : 'text-gray-600'"
                                             class="text-sm line-clamp-2"
                                             x-text="node.description"></div>

                                        <!-- Child Count Badge -->
                                        <div x-show="getChildCount(node.id) > 0" class="mt-2">
                                            <span :class="node.isRoot || node.type === 'idea' || node.type === 'task' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700'"
                                                  class="text-xs px-2 py-1 rounded-full">
                                                <span x-text="getChildCount(node.id)"></span> children
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Node Actions -->
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click.stop="addChildNode(node)" :class="node.isRoot || node.type === 'idea' || node.type === 'task' ? 'text-white hover:bg-white/20' : 'text-gray-600 hover:bg-gray-100'" class="p-1.5 rounded transition" title="Tambah Child Node">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>
                                        <button @click.stop="deleteNode(node)" :class="node.isRoot || node.type === 'idea' || node.type === 'task' ? 'text-white hover:bg-white/20' : 'text-gray-600 hover:bg-gray-100'" class="p-1.5 rounded transition" title="Hapus Node">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Connection Points di Semua Sisi -->
                            <div class="absolute -top-2 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-md hover:bg-blue-600 cursor-pointer"
                                 @click.stop="handleConnectionPointClick(node, 'top')"
                                 :class="isConnecting ? 'animate-pulse ring-2 ring-yellow-400' : ''">
                            </div>
                            <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-md hover:bg-blue-600 cursor-pointer"
                                 @click.stop="handleConnectionPointClick(node, 'bottom')"
                                 :class="isConnecting ? 'animate-pulse ring-2 ring-yellow-400' : ''">
                            </div>
                            <div class="absolute -left-2 top-1/2 transform -translate-y-1/2 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-md hover:bg-blue-600 cursor-pointer"
                                 @click.stop="handleConnectionPointClick(node, 'left')"
                                 :class="isConnecting ? 'animate-pulse ring-2 ring-yellow-400' : ''">
                            </div>
                            <div class="absolute -right-2 top-1/2 transform -translate-y-1/2 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-md hover:bg-blue-600 cursor-pointer"
                                 @click.stop="handleConnectionPointClick(node, 'right')"
                                 :class="isConnecting ? 'animate-pulse ring-2 ring-yellow-400' : ''">
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div x-show="nodes.length === 0" class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Mind Map</h3>
                        <p class="text-gray-500 mb-4">Klik tombol "Tambah Node" untuk memulai</p>
                        <button @click="addNode()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg">
                            Buat Node Pertama
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Node Editor Modal -->
        <div x-show="showModal"
             x-cloak
             @click.self="showModal = false"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div @click.stop class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform transition-all">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 rounded-t-2xl">
                    <h3 class="text-xl font-bold" x-text="editingNode.id ? 'Edit Node' : 'Tambah Node'"></h3>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul</label>
                        <input x-model="editingNode.title" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="Masukkan judul node">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea x-model="editingNode.description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="Masukkan deskripsi node"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Node</label>
                        <select x-model="editingNode.type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="default">Default</option>
                            <option value="idea">Idea</option>
                            <option value="task">Task</option>
                        </select>
                    </div>

                    <!-- Parent Selection (untuk child nodes) -->
                    <div x-show="!editingNode.isRoot">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Parent Node</label>
                        <select x-model="editingNode.parentId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="">Pilih Parent Node</option>
                            <template x-for="parent in nodes.filter(n => n.id !== editingNode.id)" :key="parent.id">
                                <option :value="parent.id" x-text="parent.title"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Connection Side Selection -->
                    <div x-show="editingNode.parentId">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sisi Koneksi dari Parent</label>
                        <select x-model="editingNode.connectionSide" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="auto">Auto</option>
                            <option value="top">Atas</option>
                            <option value="right">Kanan</option>
                            <option value="bottom">Bawah</option>
                            <option value="left">Kiri</option>
                        </select>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end gap-3">
                    <button @click="showModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition">
                        Batal
                    </button>
                    <button @click="saveNode()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
function mindmapApp() {
    return {
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
        dragOffsetX: 0,
        dragOffsetY: 0,
        showModal: false,
        editingNode: {},
        nodeIdCounter: 1,
        animationFrame: null,
        pendingDrag: null, // <--- tambahkan pending drag buffer

        // Undo / Redo
        undoStack: [],
        redoStack: [],
        maxHistory: 60,

        // Connection mode
        isConnecting: false,
        connectionSource: null,
        connectionSourceSide: null,
        tempConnectionTarget: null,
        connectionStyle: 'straight', // 'straight' atau 'curved'

        // Computed property untuk menghitung jumlah koneksi
        get connectionCount() {
            return this.nodes.filter(node => node.parentId).length;
        },

        init() {
            this.canvas = document.getElementById('mindmap-canvas');
            this.ctx = this.canvas.getContext('2d');
            this.resizeCanvas();

            window.addEventListener('resize', () => {
                this.resizeCanvas();
                this.drawConnections();
            });

            // Create initial root node
            this.nodes = [{
                id: this.nodeIdCounter++,
                title: 'Proyek Utama',
                description: 'Node utama mind map',
                x: this.canvas.width / 2,
                y: this.canvas.height / 2,
                isRoot: true,
                type: 'default',
                parentId: null,
                connectionSide: 'auto'
            }];

            // buat snapshot awal
            this.pushHistory('init');

            // Event listener untuk temporary connection line
            this.handleTempConnectionMove = this.handleTempConnectionMove.bind(this);
            document.addEventListener('mousemove', this.handleTempConnectionMove);

            // keyboard shortcuts untuk undo/redo
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

            // Continuous redraw untuk koneksi yang smooth
            this.redrawLoop();

            this.$watch('nodes', () => {
                this.scheduleRedraw();
            });

            this.$watch('zoomLevel', () => {
                this.scheduleRedraw();
            });

            this.$watch('panX', () => {
                this.scheduleRedraw();
            });

            this.$watch('panY', () => {
                this.scheduleRedraw();
            });
        },

         redrawLoop() {
            this.applyPendingDrag(); // <--- terapkan pending drag sebelum menggambar koneksi
            this.drawConnections();
            // simpan id agar bisa dibatalkan pada destroy
            this.animationFrame = requestAnimationFrame(() => this.redrawLoop());
        },

        scheduleRedraw() {
            // Debounced redraw - tidak perlu karena sudah continuous
        },

        resizeCanvas() {
            const rect = this.canvas.getBoundingClientRect();
            this.canvas.width = rect.width;
            this.canvas.height = rect.height;

            this.ctx.imageSmoothingEnabled = true;
            this.ctx.imageSmoothingQuality = 'high';
        },

        // --- HISTORY (UNDO / REDO) ---
        createSnapshot() {
            // snapshot state yang penting untuk undo/redo
            return JSON.parse(JSON.stringify({
                nodes: this.nodes,
                nodeIdCounter: this.nodeIdCounter,
                panX: this.panX,
                panY: this.panY,
                zoomLevel: this.zoomLevel
            }));
        },

        pushHistory(desc = '') {
            // push current state ke undo stack dan clear redo
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
            // replace state from snapshot (preserve reactivity)
            this.nodes = snap.nodes.map(n => ({ ...n }));
            this.nodeIdCounter = snap.nodeIdCounter;
            this.panX = snap.panX;
            this.panY = snap.panY;
            this.zoomLevel = snap.zoomLevel;
            this.pendingDrag = null;
            // redraw agar canvas sinkron
            this.drawConnections();
        },

        undo() {
            if (this.undoStack.length <= 1) {
                // jika hanya snapshot awal, tidak ada undo
                return;
            }
            // pindahkan current ke redo dan apply previous
            const current = this.undoStack.pop();
            this.redoStack.push(current);
            const last = this.undoStack[this.undoStack.length - 1];
            if (last && last.snap) {
                this.applySnapshot(last.snap);
            }
        },

        redo() {
            if (this.redoStack.length === 0) return;
            const next = this.redoStack.pop();
            // simpan current ke undo untuk bisa kembali
            const currentSnap = this.createSnapshot();
            this.undoStack.push({ snap: currentSnap, desc: 'redo-preserve' });
            this.applySnapshot(next.snap);
        },

        // === koneksi, drawing, etc... ===
        drawConnections() {
            if (!this.ctx) return;

            // Clear canvas dengan gradient background
            this.ctx.fillStyle = '#f8fafc';
            this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);

            // Draw grid pattern
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

            // Draw temporary connection line jika sedang mode koneksi
            if (this.isConnecting && this.connectionSource && this.tempConnectionTarget) {
                this.drawTempConnection();
            }
        },

        drawGrid() {
            const gridSize = 20 * this.zoomLevel;
            this.ctx.strokeStyle = '#e2e8f0';
            this.ctx.lineWidth = 0.5;

            // normalisasi offset sehingga bekerja juga untuk nilai negatif
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
            // Usahakan gunakan posisi DOM (getBoundingClientRect) bila tersedia untuk ketepatan
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
                // fallback ke perhitungan world-space
                startCenterX = from.x * this.zoomLevel + this.panX;
                startCenterY = from.y * this.zoomLevel + this.panY;
                endCenterX = to.x * this.zoomLevel + this.panX;
                endCenterY = to.y * this.zoomLevel + this.panY;
            }

            // Hitung warna berdasarkan tipe node
            let strokeColor = '#60a5fa'; // default blue
            if (to.type === 'idea') strokeColor = '#a855f7'; // purple
            if (to.type === 'task') strokeColor = '#10b981'; // green

            // Tentukan sisi koneksi
            const fromSide = this.getConnectionSide(from, to);
            const toSide = this.getConnectionSide(to, from);

            // Hitung titik awal dan akhir berdasarkan sisi dengan ukuran elemen aktual bila ada
            const fromRect = fromEl ? fromEl.getBoundingClientRect() : null;
            const toRect = toEl ? toEl.getBoundingClientRect() : null;

            const startPoint = this.getConnectionPoint(from, fromSide, startCenterX, startCenterY, fromRect);
            const endPoint = this.getConnectionPoint(to, toSide, endCenterX, endCenterY, toRect);

            this.ctx.beginPath();

            if (this.connectionStyle === 'straight') {
                // Garis lurus dengan sudut siku-siku
                this.drawStraightLine(startPoint, endPoint);
            } else {
                // Garis lengkung (seperti sebelumnya)
                this.drawCurvedLine(startPoint, endPoint);
            }

            this.ctx.strokeStyle = strokeColor;
            this.ctx.lineWidth = Math.max(2, 3 * this.zoomLevel);
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.ctx.stroke();

            // Draw arrow head
            this.drawArrow(endPoint.x, endPoint.y, endPoint.angle, strokeColor);
        },

        getConnectionSide(from, to) {
            // Jika node memiliki sisi koneksi yang ditentukan, gunakan itu
            if (from.connectionSide && from.connectionSide !== 'auto') {
                return from.connectionSide;
            }

            // Tentukan sisi otomatis berdasarkan posisi relatif
            const dx = to.x - from.x;
            const dy = to.y - from.y;

            if (Math.abs(dx) > Math.abs(dy)) {
                return dx > 0 ? 'right' : 'left';
            } else {
                return dy > 0 ? 'bottom' : 'top';
            }
        },

        getConnectionPoint(node, side, screenX, screenY, elRect = null) {
            // Jika ada rect dari DOM element, gunakan ukurannya (sudah termasuk scale)
            let offsetX, offsetY;
            if (elRect) {
                offsetX = elRect.width / 2;
                offsetY = elRect.height / 2;
            } else {
                // fallback ukuran aproksimasi (dikalikan zoom)
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
                    angle = -Math.PI/2;
                    break;
                case 'bottom':
                    y = screenY + offsetY;
                    angle = Math.PI/2;
                    break;
                case 'left':
                    x = screenX - offsetX;
                    angle = Math.PI;
                    break;
                case 'right':
                    x = screenX + offsetX;
                    angle = 0;
                    break;
                default:
                    // 'auto' -> gunakan center
                    x = screenX;
                    y = screenY;
                    angle = 0;
                    break;
            }

            return { x, y, angle };
        },

        drawStraightLine(start, end) {
            // Garis lurus dengan sudut siku-siku (seperti di draw.io)
            const midX = (start.x + end.x) / 2;
            const midY = (start.y + end.y) / 2;

            this.ctx.moveTo(start.x, start.y);

            // Horizontal lalu vertikal, atau sebaliknya, tergantung mana yang lebih pendek
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
            // Kurva untuk garis lengkung
            const dx = end.x - start.x;
            const dy = end.y - start.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            const controlOffset = Math.min(distance * 0.3, 100);

            this.ctx.moveTo(start.x, start.y);

            if (Math.abs(dx) > Math.abs(dy)) {
                // Horizontal curve
                const controlX = start.x + dx * 0.5;
                const controlY = start.y + (dy > 0 ? -controlOffset : controlOffset);
                this.ctx.quadraticCurveTo(controlX, controlY, end.x, end.y);
            } else {
                // Vertical curve
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
                // Garis lurus sementara
                this.drawStraightLine(startPoint, {x: endX, y: endY});
            } else {
                // Kurva untuk temporary connection
                this.drawCurvedLine(startPoint, {x: endX, y: endY});
            }

            this.ctx.strokeStyle = '#f59e0b';
            this.ctx.lineWidth = 2;
            this.ctx.setLineDash([5, 5]);
            this.ctx.stroke();
            this.ctx.setLineDash([]);
        },

        // === NODE MANAGEMENT ===
        addNode() {
            const centerX = (this.canvas.width / 2 - this.panX) / this.zoomLevel;
            const centerY = (this.canvas.height / 2 - this.panY) / this.zoomLevel;

            const newNode = {
                id: this.nodeIdCounter++,
                title: `Node ${this.nodeIdCounter - 1}`,
                description: 'Deskripsi node baru',
                x: centerX + (Math.random() * 200 - 100),
                y: centerY + (Math.random() * 200 - 100),
                isRoot: false,
                type: 'default',
                parentId: null,
                connectionSide: 'auto'
            };
            this.nodes.push(newNode);
            this.pushHistory('addNode');
        },

        addChildNode(parent) {
            const angle = Math.random() * Math.PI * 2;
            const distance = 150 / this.zoomLevel;
            const newNode = {
                id: this.nodeIdCounter++,
                title: `Child Node`,
                description: 'Node anak',
                x: parent.x + Math.cos(angle) * distance,
                y: parent.y + Math.sin(angle) * distance,
                isRoot: false,
                type: 'default',
                parentId: parent.id,
                connectionSide: 'auto'
            };
            this.nodes.push(newNode);
            this.pushHistory('addChildNode');
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

            if (this.editingNode.id) {
                // Edit existing node
                const index = this.nodes.findIndex(n => n.id === this.editingNode.id);
                if (index !== -1) {
                    this.nodes[index] = { ...this.editingNode };
                }
                this.pushHistory('editNode');
            } else {
                // Add new node
                this.editingNode.id = this.nodeIdCounter++;
                this.nodes.push({ ...this.editingNode });
                this.pushHistory('addNodeModal');
            }
            this.showModal = false;
            this.editingNode = {};
        },

        deleteNode(node) {
            if (node.isRoot) {
                alert('Tidak dapat menghapus node utama!');
                return;
            }

            if (!confirm(`Hapus node "${node.title}" dan semua node anaknya?`)) {
                return;
            }

            // Delete children recursively
            const deleteRecursive = (nodeId) => {
                const children = this.nodes.filter(n => n.parentId === nodeId);
                children.forEach(child => deleteRecursive(child.id));
                this.nodes = this.nodes.filter(n => n.id !== nodeId);
            };

            deleteRecursive(node.id);
            this.pushHistory('deleteNode');
        },

        // === CONNECTION MANAGEMENT ===
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
                // Create connection
                if (node.parentId === this.connectionSource.id) {
                    alert('Koneksi sudah ada!');
                } else if (this.willCreateCycle(this.connectionSource.id, node.id)) {
                    alert('Tidak dapat membuat koneksi sirkular!');
                } else {
                    this.createConnection(this.connectionSource, node, side);
                    this.pushHistory('createConnection');
                }
            }

            this.toggleConnectionMode();
        },

        createConnection(source, target, sourceSide) {
            // Set parentId tanpa mengubah posisi node
            target.parentId = source.id;
            target.connectionSide = this.getOppositeSide(sourceSide);
        },

        getOppositeSide(side) {
            switch(side) {
                case 'top': return 'bottom';
                case 'bottom': return 'top';
                case 'left': return 'right';
                case 'right': return 'left';
                default: return 'auto';
            }
        },

        handleNodeClick(node) {
            if (this.isConnecting && this.connectionSource && this.connectionSource.id !== node.id) {
                // Jika klik node langsung (bukan connection point), gunakan sisi otomatis
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
            // Check if connecting source to target would create a cycle
            let current = targetId;
            while (current) {
                const node = this.nodes.find(n => n.id === current);
                if (!node || !node.parentId) break;
                if (node.parentId === sourceId) return true;
                current = node.parentId;
            }
            return false;
        },

        // === DRAG & PAN ===
        startDrag(node, event) {
            if (this.isConnecting) return;

            this.draggingNode = node;
            const rect = this.canvas.getBoundingClientRect();

            // Simpan posisi awal untuk perhitungan delta
            this.dragStartX = (event.clientX - rect.left - this.panX) / this.zoomLevel;
            this.dragStartY = (event.clientY - rect.top - this.panY) / this.zoomLevel;

            this.handleDragBound = this.handleDrag.bind(this);
            this.stopDragBound = this.stopDrag.bind(this);

            document.addEventListener('mousemove', this.handleDragBound);
            document.addEventListener('mouseup', this.stopDragBound);

            // tandai elemen sehingga transisi dimatikan untuk menghindari lag visual
            this.draggedElement = event.currentTarget;
            if (this.draggedElement) {
                this.draggedElement.classList.add('node-dragging');
                this.draggedElement.style.zIndex = '1000';
            }

            // Hentikan event propagation untuk mencegah konflik dengan pan
            event.stopPropagation();
        },

        handleDrag(event) {
            if (this.draggingNode && !this.isConnecting) {
                const rect = this.canvas.getBoundingClientRect();

                // Hitung posisi baru dalam koordinat world space
                const newX = (event.clientX - rect.left - this.panX) / this.zoomLevel;
                const newY = (event.clientY - rect.top - this.panY) / this.zoomLevel;

                // BUFFER posisi untuk diterapkan di animation frame berikutnya
                this.pendingDrag = { x: newX, y: newY };
            }
        },

        stopDrag() {
            if (this.draggingNode) {
                this.draggingNode = null;
                document.removeEventListener('mousemove', this.handleDragBound);
                document.removeEventListener('mouseup', this.stopDragBound);

                // Reset z-index dan kelas pada elemen yang sedang didrag
                if (this.draggedElement) {
                    this.draggedElement.classList.remove('node-dragging');
                    this.draggedElement.style.zIndex = '';
                    this.draggedElement = null;
                }

                // Pastikan redraw untuk menyelaraskan koneksi akhir
                this.drawConnections();

                // commit history setelah selesai drag (memastikan posisi final tersimpan)
                this.pushHistory('dragEnd');
            }
        },

        // apply pending drag once per animation frame for smoothness
        applyPendingDrag() {
            if (this.pendingDrag && this.draggingNode) {
                // langsung commit posisi terbaru ke node (Alpine akan update DOM sekali per frame)
                this.draggingNode.x = this.pendingDrag.x;
                this.draggingNode.y = this.pendingDrag.y;
                this.pendingDrag = null;
            }
        },

        startPan(event) {
            // Hanya mulai pan jika tidak sedang drag node dan tidak sedang mode koneksi
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

            // Zoom towards center dengan perhitungan yang lebih smooth
            const zoomFactor = this.zoomLevel / oldZoom;
            this.panX = this.canvas.width/2 - (this.canvas.width/2 - this.panX) * zoomFactor;
            this.panY = this.canvas.height/2 - (this.canvas.height/2 - this.panY) * zoomFactor;

            // Perubahan zoom tidak disimpan ke history (tidak perlu undo/redo)
            // this.pushHistory('zoom');
        },

        handleWheel(event) {
            event.preventDefault();
            const delta = event.deltaY > 0 ? -0.1 : 0.1;
            const zoomPointX = event.clientX - this.canvas.getBoundingClientRect().left;
            const zoomPointY = event.clientY - this.canvas.getBoundingClientRect().top;

            const oldZoom = this.zoomLevel;
            this.zoomLevel = Math.max(0.1, Math.min(3, this.zoomLevel + delta));

            // Smooth zoom towards mouse position
            const zoomFactor = this.zoomLevel / oldZoom;
            this.panX = zoomPointX - (zoomPointX - this.panX) * zoomFactor;
            this.panY = zoomPointY - (zoomPointY - this.panY) * zoomFactor;

            // Jangan simpan zoom ke history supaya undo/redo tidak terisi oleh setiap scroll
            // this.pushHistory('mouseZoom');
        },

        resetView() {
            this.zoomLevel = 1;
            this.panX = 0;
            this.panY = 0;
            this.pushHistory('resetView');
        },

        exportMindmap() {
            const area = document.getElementById('mindmap-area');
            if (!area) return alert('Area mindmap tidak ditemukan.');

            // Pastikan canvas terkini dan ambil image koneksi (grid + garis)
            this.drawConnections();
            const canvas = this.canvas;
            if (!canvas) return alert('Canvas tidak ditemukan.');
            const canvasImg = canvas.toDataURL('image/png');

            // Buat container sementara yang ukurannya sama dengan area
            const tmp = document.createElement('div');
            tmp.id = '__mindmap_export_tmp';
            tmp.style.position = 'absolute';
            tmp.style.left = '0';
            tmp.style.top = '0';
            tmp.style.width = `${area.offsetWidth}px`;
            tmp.style.height = `${area.offsetHeight}px`;
            tmp.style.overflow = 'visible';
            tmp.style.backgroundImage = `url(${canvasImg})`;
            tmp.style.backgroundSize = '100% 100%';
            tmp.style.backgroundRepeat = 'no-repeat';
            tmp.style.zIndex = 99999;
            tmp.style.display = 'block';
            tmp.style.pointerEvents = 'none';

            const areaRect = area.getBoundingClientRect();

            // Clone setiap node ke container sementara, posisikan berdasarkan bounding rect
            this.nodes.forEach(node => {
                const el = document.getElementById(`node-${node.id}`);
                if (!el) return;
                const r = el.getBoundingClientRect();
                const left = r.left - areaRect.left;
                const top = r.top - areaRect.top;

                const clone = el.cloneNode(true);
                // set gaya agar tidak terpengaruh transform/overflow
                clone.style.position = 'absolute';
                clone.style.left = `${left}px`;
                clone.style.top = `${top}px`;
                clone.style.width = `${r.width}px`;
                clone.style.height = `${r.height}px`;
                clone.style.transform = 'none';
                clone.style.transformOrigin = '0 0';
                clone.style.overflow = 'visible';
                clone.style.pointerEvents = 'none';
                clone.style.zIndex = 10;

                // Copy beberapa computed style penting supaya visual sama (font, warna, border, shadow, radius, padding)
                const cs = window.getComputedStyle(el);
                const props = ['fontSize','fontWeight','color','backgroundColor','backgroundImage','borderRadius','boxShadow','paddingTop','paddingBottom','paddingLeft','paddingRight','border','lineHeight','letterSpacing','textAlign'];
                props.forEach(p => {
                    try { if (cs[p]) clone.style[p] = cs[p]; } catch(e){/* ignore */ }
                });

                // Pastikan semua anak tidak di-hidden
                clone.querySelectorAll('*').forEach(c => {
                    c.style.overflow = 'visible';
                });

                tmp.appendChild(clone);
            });

            // Tambahkan ke body (di atas layout) untuk di-capture
            document.body.appendChild(tmp);

            // Tangkap dengan html2canvas
            const scale = Math.min(2, window.devicePixelRatio || 2);
            html2canvas(tmp, {
                scale,
                useCORS: true,
                allowTaint: true,
                backgroundColor: null,
                logging: false
            }).then(canvasResult => {
                const imgData = canvasResult.toDataURL('image/png');
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });

                const pdfW = pdf.internal.pageSize.getWidth();
                const pdfH = pdf.internal.pageSize.getHeight();

                const imgW = canvasResult.width;
                const imgH = canvasResult.height;
                const ratio = Math.min(pdfW / imgW, pdfH / imgH);
                const drawW = imgW * ratio;
                const drawH = imgH * ratio;
                const offsetX = (pdfW - drawW) / 2;
                const offsetY = (pdfH - drawH) / 2;

                pdf.addImage(imgData, 'PNG', offsetX, offsetY, drawW, drawH);
                pdf.save(`mindmap-${Date.now()}.pdf`);
            }).catch(err => {
                console.error('Export error', err);
                alert('Gagal export: ' + (err.message || err));
            }).finally(() => {
                // Hapus container sementara
                const t = document.getElementById('__mindmap_export_tmp');
                if (t) t.remove();
            });
        },

        // Cleanup ketika komponen dihancurkan
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

/* Smooth scrolling dan rendering untuk canvas */
#mindmap-canvas {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
    image-rendering: pixelated;
    transform: translateZ(0);
    backface-visibility: hidden;
    perspective: 1000;
}

/* Improved cursor feedback */
.cursor-move {
    cursor: grab;
    transition: transform 0.15s ease-out;
}

.cursor-move:active {
    cursor: grabbing;
    transform: scale(0.98);
}

/* Connection mode cursor */
#mindmap-canvas[style*="cursor: crosshair"] {
    cursor: crosshair;
}

/* Smooth transitions untuk nodes */
[x-for="node in nodes"] {
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform;
}

/* Enhanced hover effects */
.group:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-out;
}

/* Performance optimizations */
#mindmap-canvas {
    will-change: transform;
    contain: layout style paint;
}

/* Connection points styling */
.absolute.w-4.h-4 {
    transition: all 0.2s ease;
}

.absolute.w-4.h-4:hover {
    transform: scale(1.2);
}
</style>
@endpush

@push('styles')
<style>
[x-cloak] {
    display: none !important;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Smooth scrolling dan rendering untuk canvas */
#mindmap-canvas {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
    image-rendering: pixelated;
    transform: translateZ(0);
    backface-visibility: hidden;
    perspective: 1000;
}

/* Improved cursor feedback */
.cursor-move {
    cursor: grab;
    transition: transform 0.15s ease-out;
}

.cursor-move:active {
    cursor: grabbing;
    transform: scale(0.98);
}

/* Connection mode cursor */
#mindmap-canvas[style*="cursor: crosshair"] {
    cursor: crosshair;
}

/* Smooth transitions untuk nodes - DIPERBAIKI */
[x-for="node in nodes"] {
    transition: transform 0.1s linear;
    will-change: transform;
    transform-origin: center center;
    pointer-events: auto;
}

/* Enhanced hover effects */
.group:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-out;
}

/* Performance optimizations */
#mindmap-canvas {
    will-change: transform;
    contain: layout style paint;
}

/* Connection points styling - DIPERBAIKI */
.absolute.w-4.h-4 {
    transition: all 0.2s ease;
    z-index: 20;
    pointer-events: auto;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.absolute.w-4.h-4:hover {
    transform: scale(1.3);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

/* Connection point positions dengan offset yang tepat */
.absolute.-top-2 {
    top: -8px;
}

.absolute.-bottom-2 {
    bottom: -8px;
}

.absolute.-left-2 {
    left: -8px;
}

.absolute.-right-2 {
    right: -8px;
}

/* Smooth node movement during drag */
.node-dragging {
    transition: none !important;
    z-index: 1000;
    filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.2));
}

/* Connection line styles */
.connection-line {
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* Animation untuk connection points saat mode koneksi aktif */
.animate-pulse {
    animation: connection-pulse 1.5s ease-in-out infinite;
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

/* Node container styling untuk memastikan koneksi tepat */
.group.relative {
    contain: layout style;
}

/* Zoom transition smooth */
.transform {
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Modal backdrop */
.backdrop-blur-sm {
    backdrop-filter: blur(8px);
}

/* Node card shadow improvements */
.shadow-lg {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}

.shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.hover\:shadow-xl:hover {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Gradient improvements */
.bg-gradient-to-br {
    background-size: 200% 200%;
    background-position: 0% 0%;
    transition: background-position 0.5s ease;
}

.bg-gradient-to-br:hover {
    background-position: 100% 100%;
}

/* Toolbar styling */
.bg-gradient-to-r {
    background-size: 200% 100%;
    background-position: 0% 0%;
}

/* Canvas grid pattern enhancement */
.bg-grid-pattern {
    background-image:
        linear-gradient(to right, #f1f5f9 1px, transparent 1px),
        linear-gradient(to bottom, #f1f5f9 1px, transparent 1px);
    background-size: 20px 20px;
}

/* Selection states */
.ring-4 {
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.5);
}

.ring-2 {
    box-shadow: 0 0 0 2px currentColor;
}

/* Connection mode active state */
.bg-red-600 {
    background-color: #dc2626;
}

.bg-red-600:hover {
    background-color: #b91c1c;
}

/* Smooth scrolling untuk container */
.overflow-hidden {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.overflow-hidden::-webkit-scrollbar {
    display: none;
}

/* Node action buttons */
.opacity-0 {
    opacity: 0;
    transition: opacity 0.2s ease;
}

.group:hover .opacity-0 {
    opacity: 1;
}

/* Z-index layering untuk memastikan koneksi di bawah node */
#mindmap-canvas {
    z-index: 1;
}

[x-for="node in nodes"] {
    z-index: 10;
}

.absolute.w-4.h-4 {
    z-index: 20;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }

    .text-4xl {
        font-size: 2rem;
    }

    .min-w-48 {
        min-width: 12rem;
    }

    .max-w-64 {
        max-width: 16rem;
    }
}

/* High DPI screen optimizations */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    #mindmap-canvas {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
}

/* Dark mode support (optional) */
@media (prefers-color-scheme: dark) {
    .bg-white {
        background-color: #1f2937;
    }

    .text-gray-800 {
        color: #f9fafb;
    }

    .text-gray-600 {
        color: #d1d5db;
    }

    .border-gray-200 {
        border-color: #374151;
    }

    .bg-gray-50 {
        background-color: #111827;
    }
}

/* Print styles */
@media print {
    .bg-gradient-to-br,
    .bg-gradient-to-r {
        background: none !important;
    }

    .shadow-lg,
    .shadow-2xl {
        box-shadow: none !important;
        border: 1px solid #000 !important;
    }
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Focus states untuk accessibility */
button:focus,
input:focus,
select:focus,
textarea:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Error states */
.error {
    border-color: #ef4444;
    background-color: #fef2f2;
}

/* Success states */
.success {
    border-color: #10b981;
    background-color: #f0fdf4;
}

/* Custom scrollbar untuk modal */
.modal-content {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

.modal-content::-webkit-scrollbar {
    width: 6px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

@endpush
@endsection
