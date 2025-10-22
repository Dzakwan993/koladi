@extends('layouts.app')

@section('title', 'Mind Map')

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
                <button @click="exportMindmap()" class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export
                </button>
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
            <div class="relative" style="height: 700px; background: linear-gradient(to right, #f8fafc 1px, transparent 1px), linear-gradient(to bottom, #f8fafc 1px, transparent 1px); background-size: 20px 20px;">
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
                    <div class="absolute transform -translate-x-1/2 -translate-y-1/2 transition-all duration-200"
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

                            <!-- Connection Point -->
                            <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-md hover:bg-blue-600 cursor-pointer"
                                 @click.stop="handleConnectionPointClick(node)"
                                 :class="isConnecting ? 'animate-pulse ring-2 ring-yellow-400' : ''">
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Temporary Connection Line (saat mode koneksi) -->
                <div x-show="isConnecting && connectionSource && tempConnectionTarget" class="absolute inset-0 pointer-events-none">
                    <svg class="w-full h-full">
                        <line :x1="connectionSource.x * zoomLevel + panX" 
                              :y1="connectionSource.y * zoomLevel + panY"
                              :x2="tempConnectionTarget.x" 
                              :y2="tempConnectionTarget.y"
                              stroke="#f59e0b" 
                              stroke-width="2" 
                              stroke-dasharray="5,5"/>
                    </svg>
                </div>

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
        
        // Connection mode
        isConnecting: false,
        connectionSource: null,
        tempConnectionTarget: null,

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
                parentId: null
            }];
            
            this.drawConnections();
            
            // Event listener untuk temporary connection line
            this.handleTempConnectionMove = this.handleTempConnectionMove.bind(this);
            document.addEventListener('mousemove', this.handleTempConnectionMove);
            
            // Redraw on any change dengan debouncing
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

        scheduleRedraw() {
            if (this.animationFrame) {
                cancelAnimationFrame(this.animationFrame);
            }
            this.animationFrame = requestAnimationFrame(() => {
                this.drawConnections();
            });
        },

        resizeCanvas() {
            const rect = this.canvas.getBoundingClientRect();
            this.canvas.width = rect.width;
            this.canvas.height = rect.height;
        },

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
                        this.drawLine(parent, node);
                    }
                }
            });
        },

        drawGrid() {
            const gridSize = 20 * this.zoomLevel;
            this.ctx.strokeStyle = '#e2e8f0';
            this.ctx.lineWidth = 0.5;
            
            this.ctx.beginPath();
            for (let x = this.panX % gridSize; x < this.canvas.width; x += gridSize) {
                this.ctx.moveTo(x, 0);
                this.ctx.lineTo(x, this.canvas.height);
            }
            for (let y = this.panY % gridSize; y < this.canvas.height; y += gridSize) {
                this.ctx.moveTo(0, y);
                this.ctx.lineTo(this.canvas.width, y);
            }
            this.ctx.stroke();
        },

        drawLine(from, to) {
            const startX = from.x * this.zoomLevel + this.panX;
            const startY = from.y * this.zoomLevel + this.panY;
            const endX = to.x * this.zoomLevel + this.panY;
            const endY = to.y * this.zoomLevel + this.panY;
            
            // Hitung warna berdasarkan tipe node
            let strokeColor = '#60a5fa'; // default blue
            if (to.type === 'idea') strokeColor = '#a855f7'; // purple
            if (to.type === 'task') strokeColor = '#10b981'; // green
            
            this.ctx.beginPath();
            this.ctx.moveTo(startX, startY);
            
            // Curved line dengan kontrol points yang lebih smooth
            const dx = endX - startX;
            const dy = endY - startY;
            const control1X = startX + dx * 0.5;
            const control1Y = startY;
            const control2X = startX + dx * 0.5;
            const control2Y = endY;
            
            this.ctx.bezierCurveTo(control1X, control1Y, control2X, control2Y, endX, endY);
            
            this.ctx.strokeStyle = strokeColor;
            this.ctx.lineWidth = 2 * this.zoomLevel;
            this.ctx.lineCap = 'round';
            this.ctx.stroke();
            
            // Draw arrow head
            const angle = Math.atan2(dy, dx);
            const arrowLength = 10 * this.zoomLevel;
            this.ctx.beginPath();
            this.ctx.moveTo(endX, endY);
            this.ctx.lineTo(
                endX - arrowLength * Math.cos(angle - Math.PI/6),
                endY - arrowLength * Math.sin(angle - Math.PI/6)
            );
            this.ctx.moveTo(endX, endY);
            this.ctx.lineTo(
                endX - arrowLength * Math.cos(angle + Math.PI/6),
                endY - arrowLength * Math.sin(angle + Math.PI/6)
            );
            this.ctx.stroke();
        },

        // === NODE MANAGEMENT ===
        addNode() {
            const centerX = this.canvas.width / 2;
            const centerY = this.canvas.height / 2;
            
            const newNode = {
                id: this.nodeIdCounter++,
                title: `Node ${this.nodeIdCounter - 1}`,
                description: 'Deskripsi node baru',
                x: (centerX - this.panX) / this.zoomLevel + (Math.random() * 200 - 100),
                y: (centerY - this.panY) / this.zoomLevel + (Math.random() * 200 - 100),
                isRoot: false,
                type: 'default',
                parentId: null
            };
            this.nodes.push(newNode);
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
                parentId: parent.id
            };
            this.nodes.push(newNode);
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
            } else {
                // Add new node
                this.editingNode.id = this.nodeIdCounter++;
                this.nodes.push({ ...this.editingNode });
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
        },

        // === CONNECTION MANAGEMENT ===
        toggleConnectionMode() {
            this.isConnecting = !this.isConnecting;
            if (!this.isConnecting) {
                this.connectionSource = null;
                this.tempConnectionTarget = null;
            }
        },

        handleConnectionPointClick(node) {
            if (!this.isConnecting) {
                this.toggleConnectionMode();
                this.connectionSource = node;
                return;
            }

            if (this.connectionSource && this.connectionSource.id !== node.id) {
                // Create connection
                if (node.parentId === this.connectionSource.id) {
                    alert('Koneksi sudah ada!');
                } else if (this.willCreateCycle(this.connectionSource.id, node.id)) {
                    alert('Tidak dapat membuat koneksi sirkular!');
                } else {
                    node.parentId = this.connectionSource.id;
                    // Reposition child node untuk layout yang lebih baik
                    this.repositionChildNode(node, this.connectionSource);
                }
            }
            
            this.toggleConnectionMode();
        },

        handleNodeClick(node) {
            if (this.isConnecting && this.connectionSource && this.connectionSource.id !== node.id) {
                this.handleConnectionPointClick(node);
            }
        },

        handleTempConnectionMove(event) {
            if (this.isConnecting && this.connectionSource) {
                this.tempConnectionTarget = {
                    x: event.clientX,
                    y: event.clientY
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

        repositionChildNode(child, parent) {
            // Reposition child node secara radial di sekitar parent
            const children = this.nodes.filter(n => n.parentId === parent.id);
            const angle = (children.length - 1) * (2 * Math.PI / 6); // Maks 6 children per level
            const distance = 200 / this.zoomLevel;
            
            child.x = parent.x + Math.cos(angle) * distance;
            child.y = parent.y + Math.sin(angle) * distance;
        },

        // === DRAG & PAN ===
        startDrag(node, event) {
            if (this.isConnecting) return;
            
            this.draggingNode = node;
            const rect = event.target.getBoundingClientRect();
            this.dragOffsetX = event.clientX - (node.x * this.zoomLevel + this.panX);
            this.dragOffsetY = event.clientY - (node.y * this.zoomLevel + this.panY);
            
            this.handleDragBound = this.handleDrag.bind(this);
            this.stopDragBound = this.stopDrag.bind(this);
            
            document.addEventListener('mousemove', this.handleDragBound);
            document.addEventListener('mouseup', this.stopDragBound);
        },

        handleDrag(event) {
            if (this.draggingNode && !this.isConnecting) {
                this.draggingNode.x = (event.clientX - this.dragOffsetX - this.panX) / this.zoomLevel;
                this.draggingNode.y = (event.clientY - this.dragOffsetY - this.panY) / this.zoomLevel;
            }
        },

        stopDrag() {
            this.draggingNode = null;
            document.removeEventListener('mousemove', this.handleDragBound);
            document.removeEventListener('mouseup', this.stopDragBound);
        },

        startPan(event) {
            if (event.target === this.canvas && !this.isConnecting) {
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
            
            // Zoom towards center
            const zoomFactor = this.zoomLevel / oldZoom;
            this.panX = this.canvas.width/2 - (this.canvas.width/2 - this.panX) * zoomFactor;
            this.panY = this.canvas.height/2 - (this.canvas.height/2 - this.panY) * zoomFactor;
        },

        handleWheel(event) {
            event.preventDefault();
            const delta = event.deltaY > 0 ? -0.1 : 0.1;
            this.zoom(delta);
        },

        resetView() {
            this.zoomLevel = 1;
            this.panX = 0;
            this.panY = 0;
        },

        exportMindmap() {
            const data = {
                nodes: this.nodes,
                metadata: {
                    version: '1.0',
                    exportedAt: new Date().toISOString(),
                    totalNodes: this.nodes.length,
                    totalConnections: this.connectionCount
                }
            };
            
            try {
                const blob = new Blob([JSON.stringify(data, null, 2)], { 
                    type: 'application/json' 
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `mindmap-${new Date().getTime()}.json`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                
                alert('Mind map berhasil diexport!');
            } catch (error) {
                alert('Error saat mengexport mind map: ' + error.message);
            }
        },

        // Cleanup ketika komponen dihancurkan
        destroy() {
            if (this.animationFrame) {
                cancelAnimationFrame(this.animationFrame);
            }
            document.removeEventListener('mousemove', this.handleTempConnectionMove);
        }
    }
}
</script>
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

/* Smooth scrolling untuk canvas */
#mindmap-canvas {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
}

/* Improved cursor feedback */
.cursor-move {
    cursor: grab;
}

.cursor-move:active {
    cursor: grabbing;
}

/* Connection mode cursor */
#mindmap-canvas[style*="cursor: crosshair"] {
    cursor: crosshair;
}
</style>
@endpush
@endsection