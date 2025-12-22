@extends('layouts.app')

@section('title', 'Mind Map')

<style>
    [x-cloak] { display:none !important; }

    /* ===== Stage ===== */
    #mm-stage {
        background: #f8fafc;
        position: relative;
        overflow: hidden;
    }

    /* Canvas is only for grid + connections (never blocks mouse) */
    #mm-canvas { pointer-events: none; }

    /* Viewport holds nodes, transformed together */
    #mm-viewport {
        position: absolute;
        inset: 0;
        transform-origin: 0 0;
        pointer-events: none; /* nodes re-enable */
    }

    /* ===== Nodes ===== */
    .mm-node { position:absolute; pointer-events:auto; user-select:none; }
    .mm-node.selected .mm-card {
        outline: 3px solid rgba(59,130,246,.55);
        outline-offset: 3px;
    }

    .mm-card {
        box-shadow: 0 10px 30px rgba(2,6,23,.10);
        transition: box-shadow .18s ease, transform .18s ease;
    }
    .mm-node:hover .mm-card {
        box-shadow: 0 18px 50px rgba(2,6,23,.14);
        transform: translateY(-1px);
    }

    /* ===== Ports (handles) ===== */
    .mm-port {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #2563eb;
        border: 2px solid white;
        box-shadow: 0 6px 18px rgba(2,6,23,.18);
        opacity: 0;
        transform: scale(.9);
        transition: all .12s ease;
        pointer-events: auto;
    }
    .mm-node:hover .mm-port,
    .mm-node.selected .mm-port {
        opacity: 1;
        transform: scale(1);
    }
    .mm-port:hover { transform: scale(1.25); }

    /* ===== Floating UI ===== */
    .mm-glass { backdrop-filter: blur(10px); background: rgba(255,255,255,.82); }
    .mm-shadow { box-shadow: 0 12px 36px rgba(2,6,23,.14); }

    .mm-iconbtn {
        width: 40px; height: 40px;
        border-radius: 14px;
        display:flex; align-items:center; justify-content:center;
        border: 1px solid rgba(148,163,184,.55);
        background: rgba(255,255,255,.86);
        color: #0f172a;
        transition: transform .12s ease, background .12s ease;
    }
    .mm-iconbtn:hover { transform: translateY(-1px); background: rgba(255,255,255,1); }
    .mm-iconbtn.active { background: #2563eb; border-color:#2563eb; color:white; }

    .mm-pill {
        border-radius: 999px;
        padding: 6px 10px;
        border: 1px solid rgba(148,163,184,.55);
        background: rgba(255,255,255,.86);
    }

    .mm-drawer {
        width: 340px;
        max-width: 92vw;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid rgba(148,163,184,.55);
    }

    .mm-scroll::-webkit-scrollbar { width: 10px; }
    .mm-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    .mm-scroll::-webkit-scrollbar-track { background: transparent; }
</style>

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="min-h-screen bg-slate-50"
     x-data="mindmapPro('{{ $mindmap->id }}')"
     x-init="init()"
     @keydown.window="onKeyDown($event)"
     @keyup.window="onKeyUp($event)"
>
    @include('components.workspace-nav', ['active' => 'mindmap'])

    {{-- Minimal top bar --}}
    <div class="px-4 md:px-6 py-3 flex items-center justify-between border-b border-slate-200 bg-white">
        <div class="flex items-center gap-3">
            <div class="font-bold text-slate-800 text-lg">Mind Map</div>
            <div class="hidden md:flex items-center gap-2 text-xs text-slate-500">
                <span class="mm-pill">Spasi = Geser</span>
                <span class="mm-pill">Wheel = Perbesar</span>
                <span class="mm-pill">Drag port = Sambungkan</span>
                <span class="mm-pill">Klik dua kali = Tambah</span>
                <span class="mm-pill">H = Sembunyi UI</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <div x-show="isSaving" x-cloak class="mm-pill text-xs text-yellow-700 bg-yellow-50 border-yellow-200 flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Saving...
            </div>

            <div class="mm-pill text-xs text-slate-600 hidden md:flex items-center gap-3">
                <span>Nodes: <b class="text-slate-800" x-text="nodes.length"></b></span>
                <span class="w-px h-4 bg-slate-200"></span>
                <span>Links: <b class="text-slate-800" x-text="connectionCount"></b></span>
            </div>

            <button @click="uiHidden = !uiHidden"
                    class="mm-pill text-xs text-slate-700 hover:bg-white transition">
                <span x-text="uiHidden ? 'Show UI' : 'Hide UI'"></span>
            </button>
        </div>
    </div>

    {{-- Stage --}}
    <div class="relative">
        <div id="mm-stage"
             class="w-full"
             style="height: calc(100vh - 110px);"
             @mousedown="stageMouseDown($event)"
             @mousemove="stageMouseMove($event)"
             @mouseup="stageMouseUp($event)"
             @mouseleave="stageMouseUp($event)"
             @dblclick.prevent="stageDoubleClick($event)"
             @wheel.prevent="handleWheel($event)"
        >
            <canvas id="mm-canvas" class="absolute inset-0 w-full h-full"></canvas>

            <div id="mm-viewport" :style="viewportStyle()">
                <template x-for="node in nodes" :key="node.id">
                    <div class="mm-node"
                         :data-node-id="node.id"
                         :class="isSelected(node.id) ? 'selected' : ''"
                         :style="nodeStyle(node)"
                         @mousedown.stop="nodeMouseDown(node, $event)"
                         @click.stop="nodeClick(node, $event)"
                         @dblclick.stop="startInlineEdit(node)"
                    >
                        <div class="relative group">
                            <div class="mm-card px-4 py-3 rounded-2xl border-2 min-w-[240px] max-w-[460px]"
                                 :class="nodeCardClass(node)">
                                <div class="flex items-start gap-2">
                                    <div class="flex-1">
                                        <template x-if="inlineEditId !== node.id">
                                            <div class="font-bold text-base leading-tight truncate" x-text="node.title"></div>
                                        </template>

                                        <template x-if="inlineEditId === node.id">
                                            <input type="text"
                                                   x-model="inlineEditTitle"
                                                   @keydown.enter.prevent="commitInlineEdit()"
                                                   @keydown.escape.prevent="cancelInlineEdit()"
                                                   class="w-full px-2 py-1 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900"
                                                   placeholder="Title" />
                                        </template>

                                        <div class="text-sm mt-1"
                                             :class="nodeTextClass(node)"
                                             x-text="node.description"></div>

                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span class="text-xs px-2 py-1 rounded-full" :class="badgeClass(node)" x-text="node.type"></span>
                                            <span class="text-xs px-2 py-1 rounded-full"
                                                  x-show="getChildCount(node.id)>0"
                                                  x-cloak
                                                  :class="badgeClass(node)">
                                                <span x-text="getChildCount(node.id)"></span> child
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition">
                                        <button @click.stop="addChildQuick(node)"
                                                class="p-2 rounded-xl text-slate-600 hover:bg-slate-100"
                                                title="Add child">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>

                                        <button @click.stop="removeParent(node)"
                                                x-show="!!node.parentId"
                                                x-cloak
                                                class="p-2 rounded-xl text-slate-600 hover:bg-slate-100"
                                                title="Unlink">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M18.364 5.636l-1.414 1.414M7.05 16.95l-1.414 1.414M16.95 16.95l1.414 1.414M5.636 5.636l1.414 1.414M9 12h6"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Ports --}}
                            <div class="mm-port absolute -top-2 left-1/2 -translate-x-1/2 cursor-crosshair"
                                 title="Connect"
                                 @mousedown.stop.prevent="startConnect(node, 'top', $event)"></div>
                            <div class="mm-port absolute -bottom-2 left-1/2 -translate-x-1/2 cursor-crosshair"
                                 title="Connect"
                                 @mousedown.stop.prevent="startConnect(node, 'bottom', $event)"></div>
                            <div class="mm-port absolute -left-2 top-1/2 -translate-y-1/2 cursor-crosshair"
                                 title="Connect"
                                 @mousedown.stop.prevent="startConnect(node, 'left', $event)"></div>
                            <div class="mm-port absolute -right-2 top-1/2 -translate-y-1/2 cursor-crosshair"
                                 title="Connect"
                                 @mousedown.stop.prevent="startConnect(node, 'right', $event)"></div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Marquee selection --}}
            <div x-show="isMarquee" x-cloak
                 class="absolute border-2 border-blue-500 bg-blue-400/10 rounded-lg pointer-events-none z-[40]"
                 :style="`left:${marquee.x}px; top:${marquee.y}px; width:${marquee.w}px; height:${marquee.h}px;`"></div>

            {{-- UI LAYER --}}
            <div x-show="!uiHidden" x-cloak>
                <div class="absolute left-4 top-4 z-[80] mm-glass mm-shadow rounded-2xl border border-slate-200 p-2 flex flex-col gap-2">
                    <button class="mm-iconbtn" :class="tool==='select' ? 'active' : ''" @click="tool='select'" title="Select (V)">
                        <span class="text-sm font-black">V</span>
                    </button>
                    <button class="mm-iconbtn" :class="tool==='pan' ? 'active' : ''" @click="tool='pan'" title="Pan (Space)">
                        ✋
                    </button>

                    <div class="h-px w-full bg-slate-200 my-1"></div>

                    <button class="mm-iconbtn active" @click="quickAddNode()" title="Add node (N)">
                        +
                    </button>

                    <button class="mm-iconbtn" @click="fitView()" title="Fit view (F)">
                        ⤢
                    </button>

                    <div class="h-px w-full bg-slate-200 my-1"></div>

                    <button class="mm-iconbtn" @click="toggleTemplates()" title="Templates">
                        ◧
                    </button>

                    <button class="mm-iconbtn" @click="toggleInspector()" title="Inspector">
                        ⚙
                    </button>
                </div>

                {{-- Zoom pills --}}
                <div class="absolute left-4 bottom-4 z-[80] flex items-center gap-2">
                    <button class="mm-pill text-sm hover:bg-white" @click="zoomBy(-0.1)">−</button>
                    <div class="mm-pill text-sm font-semibold text-slate-700" x-text="Math.round(zoom*100)+'%'"></div>
                    <button class="mm-pill text-sm hover:bg-white" @click="zoomBy(0.1)">+</button>

                    <div class="mm-pill text-xs text-slate-600">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="rounded border-slate-300" x-model="snapEnabled">
                            Snap
                        </label>
                    </div>

                    <button class="mm-pill text-xs text-slate-700 hover:bg-white" @click="toggleRouter()" title="Toggle cable style">
                        <span x-text="router==='orthogonal' ? 'Orthogonal' : 'Curved'"></span>
                    </button>
                </div>

                {{-- Templates --}}
                <div x-show="templatesOpen" x-cloak
                     class="absolute left-[72px] top-[220px] z-[90] mm-glass mm-shadow rounded-2xl border border-slate-200 p-3 w-[220px]"
                     @mousedown.stop
                >
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-extrabold text-slate-700">Templates</div>
                        <button class="text-xs text-slate-600 hover:text-slate-900" @click="templatesOpen=false">✕</button>
                    </div>

                    <div class="space-y-2">
                        <button class="w-full text-left px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition"
                                @click="quickAddNode({type:'default', title:'Topic'})">
                            <div class="text-sm font-semibold text-slate-800">Topic</div>
                            <div class="text-xs text-slate-500">default</div>
                        </button>
                        <button class="w-full text-left px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition"
                                @click="quickAddNode({type:'idea', title:'Idea'})">
                            <div class="text-sm font-semibold text-slate-800">Idea</div>
                            <div class="text-xs text-slate-500">purple</div>
                        </button>
                        <button class="w-full text-left px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition"
                                @click="quickAddNode({type:'task', title:'Task'})">
                            <div class="text-sm font-semibold text-slate-800">Task</div>
                            <div class="text-xs text-slate-500">green</div>
                        </button>
                    </div>
                </div>

                {{-- Inspector --}}
                <div class="absolute right-4 top-4 bottom-4 z-[90]">
                    <button x-show="!inspectorOpen" x-cloak
                            class="mm-glass mm-shadow border border-slate-200 rounded-2xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-white transition"
                            @click="inspectorOpen=true"
                            style="position:absolute; right:0; top:0;">
                        Inspector
                    </button>

                    <div x-show="inspectorOpen" x-cloak
                         class="mm-drawer mm-glass mm-shadow h-full"
                         x-transition:enter="transition ease-out duration-180"
                         x-transition:enter-start="opacity-0 translate-x-2"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-140"
                         x-transition:leave-start="opacity-100 translate-x-0"
                         x-transition:leave-end="opacity-0 translate-x-2"
                         @mousedown.stop
                    >
                        <div class="px-4 py-3 border-b border-slate-200 bg-white/50 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-extrabold text-slate-800">Inspector</div>
                                <div class="text-xs text-slate-500" x-show="selectedIds.length===0">Select node untuk edit</div>
                                <div class="text-xs text-slate-500" x-show="selectedIds.length>1" x-cloak>Multi-select: <span x-text="selectedIds.length"></span></div>
                            </div>
                            <button class="px-2 py-1 rounded-lg hover:bg-slate-100 text-slate-700" @click="inspectorOpen=false">✕</button>
                        </div>

                        <div class="p-4 space-y-4 overflow-auto mm-scroll" style="height: calc(100% - 56px);">
                            <template x-if="selectedIds.length===1">
                                <div x-cloak>
                                    <template x-if="selectedNode()">
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-xs font-extrabold text-slate-700 mb-2">Title</label>
                                                <input type="text"
                                                       class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                                                       :value="selectedNode().title"
                                                       @input="updateSelectedField('title', $event.target.value)">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-extrabold text-slate-700 mb-2">Description</label>
                                                <textarea rows="6"
                                                          class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                                                          @input="updateSelectedField('description', $event.target.value)"
                                                          x-text="selectedNode().description"></textarea>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-extrabold text-slate-700 mb-2">Type</label>
                                                <select class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                                                        :value="selectedNode().type"
                                                        @change="updateSelectedField('type', $event.target.value)">
                                                    <option value="default">default</option>
                                                    <option value="idea">idea</option>
                                                    <option value="task">task</option>
                                                </select>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2">
                                                <button @click="addChildQuick(selectedNode())"
                                                        class="px-3 py-2 rounded-xl bg-blue-600 text-white text-sm font-extrabold hover:bg-blue-700 transition">
                                                    + Child
                                                </button>
                                                <button @click="removeParent(selectedNode())"
                                                        :disabled="!selectedNode().parentId"
                                                        :class="selectedNode().parentId ? 'bg-white text-slate-700 hover:bg-slate-50' : 'bg-slate-50 text-slate-300 cursor-not-allowed'"
                                                        class="px-3 py-2 rounded-xl border border-slate-200 text-sm font-extrabold transition">
                                                    Unlink
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2">
                                                <button @click="centerOnSelection()"
                                                        class="px-3 py-2 rounded-xl bg-white text-slate-700 border border-slate-200 text-sm font-extrabold hover:bg-slate-50 transition">
                                                    Center
                                                </button>
                                                <button @click="deleteSelection()"
                                                        class="px-3 py-2 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-sm font-extrabold hover:bg-rose-100 transition">
                                                    Delete
                                                </button>
                                            </div>

                                            <div class="pt-2 border-t border-slate-200 text-xs text-slate-600">
                                                <div class="font-extrabold text-slate-700 mb-1">Shortcuts</div>
                                                <div>V select • Space pan • N add</div>
                                                <div>F fit • H hide UI</div>
                                                <div>Ctrl+Z/Y undo/redo • Ctrl+C/V copy/paste</div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="selectedIds.length>1">
                                <div class="space-y-3" x-cloak>
                                    <div class="text-sm font-extrabold text-slate-800">Batch actions</div>
                                    <button @click="duplicateSelection()"
                                            class="w-full px-3 py-2 rounded-xl bg-white text-slate-700 border border-slate-200 text-sm font-extrabold hover:bg-slate-50 transition">
                                        Duplicate selected
                                    </button>
                                    <button @click="deleteSelection()"
                                            class="w-full px-3 py-2 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-sm font-extrabold hover:bg-rose-100 transition">
                                        Delete selected
                                    </button>
                                    <button @click="centerOnSelection()"
                                            class="w-full px-3 py-2 rounded-xl bg-white text-slate-700 border border-slate-200 text-sm font-extrabold hover:bg-slate-50 transition">
                                        Center view
                                    </button>
                                </div>
                            </template>

                            <template x-if="selectedIds.length===0">
                                <div class="text-sm text-slate-600">
                                    Pilih node untuk edit. Drag kosong untuk multi-select.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function mindmapPro(mindmapId) {
            return {
                mindmapId,
                nodes: [],
                canvas: null,
                ctx: null,

                // transform
                zoom: 1,
                panX: 0,
                panY: 0,

                // UI state
                uiHidden: false,
                inspectorOpen: true,
                templatesOpen: false,
                snapEnabled: true,
                tool: 'select', // select|pan
                isSpaceDown: false,

                // routing style
                router: 'orthogonal', // orthogonal|curved

                // saving
                isSaving: false,
                isLoading: false,
                autoSaveTimeout: null,

                // selection
                selectedIds: [],
                isMarquee: false,
                marqueeStart: { x: 0, y: 0 },
                marquee: { x: 0, y: 0, w: 0, h: 0 },

                // pan
                isPanning: false,
                panStart: { x: 0, y: 0 },

                // drag
                isDragging: false,
                dragStartWorld: { x: 0, y: 0 },
                dragStartNodes: new Map(),

                // connect
                isConnecting: false,
                connectSourceId: null,
                connectSourceSide: 'right',
                connectMouseWorld: { x: 0, y: 0 },

                // edit
                inlineEditId: null,
                inlineEditTitle: '',

                // history
                undoStack: [],
                redoStack: [],
                maxHistory: 80,

                // clipboard
                clipboard: null,

                // grid
                gridSize: 20,

                // cache DOM rects (world units)
                nodeRects: new Map(), // id -> {hw, hh}

                get connectionCount() { return this.nodes.filter(n => n.parentId).length; },

                async init() {
                    this.canvas = document.getElementById('mm-canvas');
                    this.ctx = this.canvas.getContext('2d');

                    this.resizeCanvas();

                    window.addEventListener('resize', () => {
                        // Keep world center locked when stage size changes (prevents "jump" on small/large screen)
                        const prevW = this.canvas.width;
                        const prevH = this.canvas.height;
                        const centerWorld = this.screenToWorld(prevW / 2, prevH / 2);

                        this.resizeCanvas();

                        const newW = this.canvas.width;
                        const newH = this.canvas.height;
                        this.panX = newW / 2 - centerWorld.x * this.zoom;
                        this.panY = newH / 2 - centerWorld.y * this.zoom;

                        this.recalcNodeRects();
                        this.draw();
                    });

                    await this.loadMindmapData();

                    this.$nextTick(() => {
                        this.recalcNodeRects();
                        if (this.nodes.length) this.fitView();
                        else { this.zoom = 1; this.panX = 120; this.panY = 120; }
                        this.pushHistory('init');
                        this.loop();
                    });
                },

                loop() { this.draw(); requestAnimationFrame(() => this.loop()); },

                resizeCanvas() {
                    const stage = document.getElementById('mm-stage');
                    const rect = stage.getBoundingClientRect();
                    this.canvas.width = rect.width;
                    this.canvas.height = rect.height;
                },

                recalcNodeRects() {
                    this.nodeRects = new Map();
                    const viewport = document.getElementById('mm-viewport');
                    if (!viewport) return;

                    const els = viewport.querySelectorAll('.mm-node');
                    els.forEach(el => {
                        const id = el.getAttribute('data-node-id');
                        const card = el.querySelector('.mm-card');
                        if (!id || !card) return;

                        // card size in px, but in world space the nodeStyle uses left/top as world units.
                        // Our world == px at zoom 1. So the size is still "world units" baseline.
                        const r = card.getBoundingClientRect();

                        // convert screen px to world units
                        // width_world = width_screen / zoom
                        const hw = (r.width / this.zoom) / 2;
                        const hh = (r.height / this.zoom) / 2;

                        this.nodeRects.set(id, { hw, hh });
                    });
                },

                // ====== API ======
                async loadMindmapData() {
                    this.isLoading = true;
                    try {
                        const res = await fetch(`/mindmap/${this.mindmapId}/data`);
                        const data = await res.json();
                        if (data.nodes && data.nodes.length) {
                            this.nodes = data.nodes.map(n => ({
                                id: String(n.id),
                                title: n.title ?? 'Untitled',
                                description: n.description ?? '',
                                x: Number(n.x ?? 0),
                                y: Number(n.y ?? 0),
                                isRoot: !!n.isRoot,
                                type: n.type ?? 'default',
                                parentId: n.parentId ? String(n.parentId) : null,

                                // NEW: persist chosen ports (draw.io style)
                                parentPort: n.parentPort ?? 'auto', // port on parent
                                childPort: n.childPort ?? 'auto',   // port on child
                            }));
                        } else {
                            this.nodes = [];
                        }
                    } catch (e) {
                        console.error(e);
                        this.nodes = [];
                    } finally {
                        this.isLoading = false;
                    }
                },

                async saveToDatabase() {
                    if (this.isSaving || this.isLoading) return;
                    this.isSaving = true;
                    try {
                        const res = await fetch(`/mindmap/${this.mindmapId}/save`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                nodes: this.nodes.map(n => ({
                                    id: String(n.id),
                                    title: n.title,
                                    description: n.description,
                                    x: n.x,
                                    y: n.y,
                                    isRoot: !!n.isRoot,
                                    type: n.type,
                                    parentId: n.parentId ? String(n.parentId) : null,

                                    // NEW: save ports
                                    parentPort: n.parentPort ?? 'auto',
                                    childPort: n.childPort ?? 'auto',
                                }))
                            })
                        });
                        if (!res.ok) throw new Error('Failed to save');
                        await res.json().catch(()=>({}));
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.isSaving = false;
                    }
                },

                autoSave() {
                    clearTimeout(this.autoSaveTimeout);
                    this.autoSaveTimeout = setTimeout(() => this.saveToDatabase(), 650);
                },

                uuid() {
                    if (window.crypto?.randomUUID) return crypto.randomUUID();
                    return 'id-' + Math.random().toString(16).slice(2) + Date.now().toString(16);
                },

                // ====== UI toggles ======
                toggleInspector() { this.inspectorOpen = !this.inspectorOpen; },
                toggleTemplates() { this.templatesOpen = !this.templatesOpen; },
                toggleRouter() { this.router = (this.router === 'orthogonal') ? 'curved' : 'orthogonal'; },

                // ====== transforms ======
                viewportStyle() { return `transform: translate(${this.panX}px, ${this.panY}px) scale(${this.zoom});`; },
                screenToWorld(sx, sy) { return { x: (sx - this.panX) / this.zoom, y: (sy - this.panY) / this.zoom }; },
                clampZoom(z) { return Math.max(0.2, Math.min(3, z)); },

                // ====== drawing ======
                draw() {
                    if (!this.ctx) return;

                    // reset
                    this.ctx.setTransform(1,0,0,1,0,0);
                    this.ctx.clearRect(0,0,this.canvas.width,this.canvas.height);

                    // world transform
                    this.ctx.setTransform(this.zoom, 0, 0, this.zoom, this.panX, this.panY);

                    this.drawGrid();

                    // connections
                    for (const node of this.nodes) {
                        if (!node.parentId) continue;
                        const parent = this.nodes.find(n => n.id === node.parentId);
                        if (!parent) continue;

                        const highlight = this.isSelected(node.id) || this.isSelected(parent.id);
                        this.drawLink(parent, node, highlight);
                    }

                    // temp connect
                    if (this.isConnecting && this.connectSourceId) {
                        const src = this.nodes.find(n => n.id === this.connectSourceId);
                        if (src) this.drawTempLink(src);
                    }
                },

                drawGrid() {
                    const step = this.gridSize;
                    const w0 = this.screenToWorld(0,0);
                    const w1 = this.screenToWorld(this.canvas.width, this.canvas.height);

                    const minX = Math.floor(Math.min(w0.x, w1.x) / step) * step;
                    const maxX = Math.ceil(Math.max(w0.x, w1.x) / step) * step;
                    const minY = Math.floor(Math.min(w0.y, w1.y) / step) * step;
                    const maxY = Math.ceil(Math.max(w0.y, w1.y) / step) * step;

                    this.ctx.lineWidth = 1 / this.zoom;
                    this.ctx.strokeStyle = '#e2e8f0';

                    this.ctx.beginPath();
                    for (let x = minX; x <= maxX; x += step) { this.ctx.moveTo(x, minY); this.ctx.lineTo(x, maxY); }
                    for (let y = minY; y <= maxY; y += step) { this.ctx.moveTo(minX, y); this.ctx.lineTo(maxX, y); }
                    this.ctx.stroke();
                },

                nodeColor(node) {
                    if (node.isRoot) return '#2563eb';
                    if (node.type === 'idea') return '#a855f7';
                    if (node.type === 'task') return '#10b981';
                    return '#60a5fa';
                },

                // choose side by vector (fallback)
                autoSide(from, to) {
                    const dx = to.x - from.x;
                    const dy = to.y - from.y;
                    if (Math.abs(dx) > Math.abs(dy)) return dx >= 0 ? 'right' : 'left';
                    return dy >= 0 ? 'bottom' : 'top';
                },

                // choose side by cursor position inside node (target side)
                sideFromPoint(node, wx, wy) {
                    const r = this.nodeRects.get(node.id) || { hw: 150, hh: 58 };
                    const dx = wx - node.x;
                    const dy = wy - node.y;

                    // normalize to rectangle
                    const nx = dx / (r.hw || 1);
                    const ny = dy / (r.hh || 1);

                    if (Math.abs(nx) > Math.abs(ny)) return nx >= 0 ? 'right' : 'left';
                    return ny >= 0 ? 'bottom' : 'top';
                },

                portPoint(node, side) {
                    const r = this.nodeRects.get(node.id) || { hw: 150, hh: 58 };
                    const w = r.hw;
                    const h = r.hh;

                    switch (side) {
                        case 'top': return { x: node.x, y: node.y - h, side };
                        case 'bottom': return { x: node.x, y: node.y + h, side };
                        case 'left': return { x: node.x - w, y: node.y, side };
                        case 'right': return { x: node.x + w, y: node.y, side };
                        default: return { x: node.x + w, y: node.y, side: 'right' };
                    }
                },

                lastTangentAngle: 0,

                drawLink(from, to, highlight=false) {
                    const color = this.nodeColor(to);

                    // IMPORTANT:
                    // use stored ports if available, fallback to auto
                    const fromSide = (to.parentPort && to.parentPort !== 'auto')
                        ? to.parentPort
                        : this.autoSide(from, to);

                    const toSide = (to.childPort && to.childPort !== 'auto')
                        ? to.childPort
                        : this.autoSide(to, from);

                    const p1 = this.portPoint(from, fromSide);
                    const p2 = this.portPoint(to, toSide);

                    const baseW = (highlight ? 4 : 3) / this.zoom;
                    this.ctx.lineWidth = baseW;
                    this.ctx.lineCap = 'round';
                    this.ctx.lineJoin = 'round';
                    this.ctx.strokeStyle = highlight ? color : this.hexAlpha(color, 0.85);

                    if (this.router === 'curved') this.drawCurved(p1, p2);
                    else this.drawOrthogonalRounded(p1, p2);

                    const ang = this.lastTangentAngle;
                    this.drawArrow(p2.x, p2.y, ang, color, highlight);
                },

                drawCurved(a, b) {
                    const dx = b.x - a.x;
                    const dy = b.y - a.y;
                    const dist = Math.sqrt(dx*dx + dy*dy);
                    const bend = Math.min(220, dist * 0.45);

                    const c1 = { x: a.x, y: a.y };
                    const c2 = { x: b.x, y: b.y };

                    const push = (pt, side, amount) => {
                        if (side === 'left') pt.x -= amount;
                        if (side === 'right') pt.x += amount;
                        if (side === 'top') pt.y -= amount;
                        if (side === 'bottom') pt.y += amount;
                    };

                    push(c1, a.side, bend);
                    push(c2, b.side, bend);

                    this.ctx.beginPath();
                    this.ctx.moveTo(a.x, a.y);
                    this.ctx.bezierCurveTo(c1.x, c1.y, c2.x, c2.y, b.x, b.y);
                    this.ctx.stroke();

                    this.lastTangentAngle = Math.atan2(b.y - c2.y, b.x - c2.x);
                },

                drawOrthogonalRounded(a, b) {
                    const r = 18;
                    const pts = [];
                    const out = 22;

                    const nudge = (p, side, d) => {
                        const q = { x: p.x, y: p.y };
                        if (side === 'left') q.x -= d;
                        if (side === 'right') q.x += d;
                        if (side === 'top') q.y -= d;
                        if (side === 'bottom') q.y += d;
                        return q;
                    };

                    const pStart = { x: a.x, y: a.y };
                    const pA = nudge(a, a.side, out);
                    const pB = nudge(b, b.side, out);
                    const pEnd = { x: b.x, y: b.y };

                    if (a.side === 'left' || a.side === 'right') {
                        const midX = (pA.x + pB.x) / 2;
                        pts.push(pStart, pA, { x: midX, y: pA.y }, { x: midX, y: pB.y }, pB, pEnd);
                    } else {
                        const midY = (pA.y + pB.y) / 2;
                        pts.push(pStart, pA, { x: pA.x, y: midY }, { x: pB.x, y: midY }, pB, pEnd);
                    }

                    this.ctx.beginPath();
                    this.ctx.moveTo(pts[0].x, pts[0].y);

                    for (let i = 1; i < pts.length - 1; i++) {
                        const prev = pts[i - 1];
                        const curr = pts[i];
                        const next = pts[i + 1];

                        const v1 = { x: curr.x - prev.x, y: curr.y - prev.y };
                        const v2 = { x: next.x - curr.x, y: next.y - curr.y };

                        const len1 = Math.max(1e-6, Math.hypot(v1.x, v1.y));
                        const len2 = Math.max(1e-6, Math.hypot(v2.x, v2.y));

                        const rr = Math.min(r, len1 / 2, len2 / 2);

                        const p1 = { x: curr.x - (v1.x / len1) * rr, y: curr.y - (v1.y / len1) * rr };
                        const p2 = { x: curr.x + (v2.x / len2) * rr, y: curr.y + (v2.y / len2) * rr };

                        this.ctx.lineTo(p1.x, p1.y);
                        this.ctx.quadraticCurveTo(curr.x, curr.y, p2.x, p2.y);
                    }

                    this.ctx.lineTo(pts[pts.length - 1].x, pts[pts.length - 1].y);
                    this.ctx.stroke();

                    const last = pts[pts.length - 1];
                    const prev = pts[pts.length - 2];
                    this.lastTangentAngle = Math.atan2(last.y - prev.y, last.x - prev.x);
                },

                drawArrow(x, y, angle, color, highlight=false) {
                    const len = (highlight ? 14 : 12) / this.zoom;
                    const w = (highlight ? 8 : 7) / this.zoom;

                    this.ctx.beginPath();
                    this.ctx.moveTo(x, y);
                    this.ctx.lineTo(x - len*Math.cos(angle) - w*Math.sin(angle), y - len*Math.sin(angle) + w*Math.cos(angle));
                    this.ctx.lineTo(x - len*Math.cos(angle) + w*Math.sin(angle), y - len*Math.sin(angle) - w*Math.cos(angle));
                    this.ctx.closePath();
                    this.ctx.fillStyle = color;
                    this.ctx.fill();
                },

                drawTempLink(src) {
                    const aSide = this.connectSourceSide || 'right';
                    const a = this.portPoint(src, aSide);
                    const b = { x: this.connectMouseWorld.x, y: this.connectMouseWorld.y };

                    this.ctx.save();
                    this.ctx.lineWidth = 3 / this.zoom;
                    this.ctx.setLineDash([10 / this.zoom, 10 / this.zoom]);
                    this.ctx.strokeStyle = this.hexAlpha('#2563eb', 0.85);
                    this.ctx.lineCap = 'round';
                    this.ctx.lineJoin = 'round';

                    this.ctx.beginPath();
                    this.ctx.moveTo(a.x, a.y);
                    this.ctx.lineTo(b.x, b.y);
                    this.ctx.stroke();

                    this.ctx.restore();
                },

                hexAlpha(hex, a) {
                    const r = parseInt(hex.slice(1,3), 16);
                    const g = parseInt(hex.slice(3,5), 16);
                    const b = parseInt(hex.slice(5,7), 16);
                    return `rgba(${r},${g},${b},${a})`;
                },

                // ===== DOM styling =====
                nodeStyle(node) { return `left:${node.x}px; top:${node.y}px; transform: translate(-50%, -50%);`; },

                nodeCardClass(node) {
                    if (node.isRoot) return 'bg-gradient-to-br from-blue-600 to-indigo-700 text-white border-blue-800';
                    if (node.type === 'idea') return 'bg-gradient-to-br from-purple-500 to-fuchsia-600 text-white border-purple-700';
                    if (node.type === 'task') return 'bg-gradient-to-br from-emerald-500 to-green-600 text-white border-emerald-700';
                    return 'bg-white text-slate-800 border-slate-300';
                },
                nodeTextClass(node) {
                    if (node.isRoot || node.type === 'idea' || node.type === 'task') return 'text-white/90';
                    return 'text-slate-600';
                },
                badgeClass(node) {
                    if (node.isRoot || node.type === 'idea' || node.type === 'task') return 'bg-white/20 text-white';
                    return 'bg-slate-100 text-slate-700';
                },

                // ===== selection =====
                isSelected(id) { return this.selectedIds.includes(id); },
                selectedNode() { return this.selectedIds.length === 1 ? (this.nodes.find(n => n.id === this.selectedIds[0]) || null) : null; },
                updateSelectedField(field, value) {
                    const n = this.selectedNode(); if (!n) return;
                    n[field] = value;
                    this.pushHistory('edit');
                    this.autoSave();
                    this.$nextTick(() => this.recalcNodeRects());
                },
                getChildCount(id) { return this.nodes.filter(n => n.parentId === id).length; },

                // ===== stage events =====
                stageDoubleClick(e) {
                    const onPanel = e.target.closest('.mm-glass, .mm-drawer, .mm-node');
                    if (onPanel) return;

                    const stage = document.getElementById('mm-stage');
                    const rect = stage.getBoundingClientRect();
                    const sx = e.clientX - rect.left;
                    const sy = e.clientY - rect.top;
                    const w = this.screenToWorld(sx, sy);

                    this.quickAddNode({ title: 'New Node' }, w);
                },

                stageMouseDown(e) {
                    if (e.button !== 0) return;
                    if (this.isConnecting) return;

                    const onPanel = e.target.closest('.mm-glass, .mm-drawer');
                    if (onPanel) return;

                    if (this.tool === 'pan' || this.isSpaceDown) {
                        this.isPanning = true;
                        this.panStart = { x: e.clientX - this.panX, y: e.clientY - this.panY };
                        return;
                    }

                    if (!e.shiftKey) this.selectedIds = [];
                    this.isMarquee = true;

                    const rect = e.currentTarget.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    this.marqueeStart = { x, y };
                    this.marquee = { x, y, w: 0, h: 0 };
                },

                stageMouseMove(e) {
                    const stage = document.getElementById('mm-stage');
                    const rect = stage.getBoundingClientRect();
                    const sx = e.clientX - rect.left;
                    const sy = e.clientY - rect.top;

                    if (this.isConnecting) {
                        this.connectMouseWorld = this.screenToWorld(sx, sy);
                        return;
                    }

                    if (this.isPanning) {
                        this.panX = e.clientX - this.panStart.x;
                        this.panY = e.clientY - this.panStart.y;
                        return;
                    }

                    if (this.isMarquee) {
                        const x1 = Math.min(this.marqueeStart.x, sx);
                        const y1 = Math.min(this.marqueeStart.y, sy);
                        const x2 = Math.max(this.marqueeStart.x, sx);
                        const y2 = Math.max(this.marqueeStart.y, sy);
                        this.marquee = { x: x1, y: y1, w: x2-x1, h: y2-y1 };

                        const wA = this.screenToWorld(x1, y1);
                        const wB = this.screenToWorld(x2, y2);
                        const minX = Math.min(wA.x, wB.x), maxX = Math.max(wA.x, wB.x);
                        const minY = Math.min(wA.y, wB.y), maxY = Math.max(wA.y, wB.y);

                        // selection by center point
                        const ids = this.nodes
                            .filter(n => n.x >= minX && n.x <= maxX && n.y >= minY && n.y <= maxY)
                            .map(n => n.id);

                        if (e.shiftKey) {
                            const set = new Set([...this.selectedIds, ...ids]);
                            this.selectedIds = [...set];
                        } else {
                            this.selectedIds = ids;
                        }
                    }
                },

                stageMouseUp() {
                    if (this.isPanning) { this.isPanning = false; this.pushHistory('pan'); return; }
                    if (this.isMarquee) { this.isMarquee = false; this.marquee = {x:0,y:0,w:0,h:0}; return; }
                    if (this.isConnecting) { this.finishConnect(); return; }
                },

                // ===== node events =====
                nodeClick(node, e) {
                    if (this.isConnecting) return;
                    if (e.shiftKey) {
                        if (this.isSelected(node.id)) this.selectedIds = this.selectedIds.filter(id => id !== node.id);
                        else this.selectedIds = [...this.selectedIds, node.id];
                    } else {
                        if (!(this.selectedIds.length > 1 && this.isSelected(node.id))) this.selectedIds = [node.id];
                    }
                },

                nodeMouseDown(node, e) {
                    if (e.button !== 0) return;
                    if (this.isConnecting) return;

                    if (this.tool === 'pan' || this.isSpaceDown) {
                        this.isPanning = true;
                        this.panStart = { x: e.clientX - this.panX, y: e.clientY - this.panY };
                        return;
                    }

                    if (!this.isSelected(node.id)) {
                        if (!e.shiftKey) this.selectedIds = [node.id];
                        else this.selectedIds = [...this.selectedIds, node.id];
                    }

                    const stage = document.getElementById('mm-stage');
                    const rect = stage.getBoundingClientRect();
                    const w = this.screenToWorld(e.clientX - rect.left, e.clientY - rect.top);

                    this.isDragging = true;
                    this.dragStartWorld = w;
                    this.dragStartNodes = new Map();
                    this.selectedIds.forEach(id => {
                        const n = this.nodes.find(nn => nn.id === id);
                        if (n) this.dragStartNodes.set(id, { x: n.x, y: n.y });
                    });

                    const move = (ev) => this.dragMove(ev);
                    const up = () => {
                        document.removeEventListener('mousemove', move);
                        document.removeEventListener('mouseup', up);
                        this.dragEnd();
                    };
                    document.addEventListener('mousemove', move);
                    document.addEventListener('mouseup', up);
                },

                dragMove(e) {
                    if (!this.isDragging) return;
                    const stage = document.getElementById('mm-stage');
                    const rect = stage.getBoundingClientRect();
                    const w = this.screenToWorld(e.clientX - rect.left, e.clientY - rect.top);

                    const dx = w.x - this.dragStartWorld.x;
                    const dy = w.y - this.dragStartWorld.y;

                    for (const id of this.selectedIds) {
                        const start = this.dragStartNodes.get(id);
                        const node = this.nodes.find(n => n.id === id);
                        if (!start || !node) continue;

                        let nx = start.x + dx;
                        let ny = start.y + dy;

                        if (this.snapEnabled) {
                            nx = Math.round(nx / this.gridSize) * this.gridSize;
                            ny = Math.round(ny / this.gridSize) * this.gridSize;
                        }

                        node.x = nx; node.y = ny;
                    }
                    this.autoSave();
                },

                dragEnd() {
                    if (!this.isDragging) return;
                    this.isDragging = false;
                    this.dragStartNodes = new Map();
                    this.pushHistory('drag');
                    this.autoSave();
                    this.$nextTick(() => this.recalcNodeRects());
                },

                // ===== connect =====
                startConnect(node, side, e) {
                    const stage = document.getElementById('mm-stage');
                    const rect = stage.getBoundingClientRect();
                    const w = this.screenToWorld(e.clientX - rect.left, e.clientY - rect.top);

                    this.isConnecting = true;
                    this.connectSourceId = node.id;
                    this.connectSourceSide = side;
                    this.connectMouseWorld = w;

                    const move = (ev) => {
                        const r = stage.getBoundingClientRect();
                        this.connectMouseWorld = this.screenToWorld(ev.clientX - r.left, ev.clientY - r.top);
                    };
                    const up = () => {
                        document.removeEventListener('mousemove', move);
                        document.removeEventListener('mouseup', up);
                        this.finishConnect();
                    };
                    document.addEventListener('mousemove', move);
                    document.addEventListener('mouseup', up);
                },

                finishConnect() {
                    if (!this.isConnecting || !this.connectSourceId) return;

                    const src = this.nodes.find(n => n.id === this.connectSourceId);
                    if (!src) { this.isConnecting=false; this.connectSourceId=null; return; }

                    // find target under mouse
                    const target = this.findNodeAtWorld(this.connectMouseWorld.x, this.connectMouseWorld.y, this.connectSourceId);

                    if (target) {
                        if (target.id === this.connectSourceId) {
                            // no-op
                        } else if (this.willCreateCycle(this.connectSourceId, target.id)) {
                            alert('Tidak bisa membuat koneksi cycle.');
                        } else {
                            // IMPORTANT: store ports based on actual drag + drop cursor
                            target.parentId = this.connectSourceId;

                            // start port = the one user dragged
                            target.parentPort = this.connectSourceSide || 'auto';

                            // end port = based on cursor position over target
                            target.childPort = this.sideFromPoint(target, this.connectMouseWorld.x, this.connectMouseWorld.y);

                            if (target.isRoot) target.isRoot = false;

                            this.pushHistory('connect');
                            this.autoSave();
                        }
                    }

                    this.isConnecting = false;
                    this.connectSourceId = null;
                },

                findNodeAtWorld(wx, wy, excludeId=null) {
                    let best = null;
                    let bestD = Infinity;

                    for (const n of this.nodes) {
                        if (excludeId && n.id === excludeId) continue;

                        const r = this.nodeRects.get(n.id) || { hw: 150, hh: 58 };
                        const hw = r.hw, hh = r.hh;

                        if (Math.abs(wx - n.x) <= hw && Math.abs(wy - n.y) <= hh) {
                            const dx = wx - n.x, dy = wy - n.y;
                            const d = dx*dx + dy*dy;
                            if (d < bestD) { bestD = d; best = n; }
                        }
                    }
                    return best;
                },

                willCreateCycle(sourceId, targetId) {
                    let cur = sourceId;
                    const visited = new Set();
                    while (cur) {
                        if (visited.has(cur)) break;
                        visited.add(cur);
                        if (cur === targetId) return true;
                        const n = this.nodes.find(x => x.id === cur);
                        cur = n?.parentId || null;
                    }
                    return false;
                },

                // ===== actions =====
                quickAddNode(preset=null, worldPos=null) {
                    const stage = document.getElementById('mm-stage');
                    const rect = stage.getBoundingClientRect();
                    const centerW = worldPos ?? this.screenToWorld(rect.width/2, rect.height/2);

                    const isFirst = this.nodes.length === 0;

                    let x = centerW.x;
                    let y = centerW.y;

                    if (this.snapEnabled) {
                        x = Math.round(x/this.gridSize)*this.gridSize;
                        y = Math.round(y/this.gridSize)*this.gridSize;
                    }

                    const node = {
                        id: this.uuid(),
                        title: preset?.title ?? (isFirst ? 'Main Topic' : 'New Node'),
                        description: preset?.description ?? '',
                        x, y,
                        isRoot: isFirst,
                        type: preset?.type ?? 'default',
                        parentId: null,

                        parentPort: 'auto',
                        childPort: 'auto',
                    };

                    this.nodes.push(node);
                    this.selectedIds = [node.id];
                    this.pushHistory('addNode');
                    this.autoSave();

                    this.$nextTick(() => {
                        this.recalcNodeRects();
                        this.inlineEditId = node.id;
                        this.inlineEditTitle = node.title;
                    });
                },

                addChildQuick(parent) {
                    if (!parent) return;

                    let x = parent.x + 360;
                    let y = parent.y;

                    const siblings = this.nodes.filter(n => n.parentId === parent.id);
                    if (siblings.length) y = parent.y + (siblings.length * 140);

                    if (this.snapEnabled) {
                        x = Math.round(x/this.gridSize)*this.gridSize;
                        y = Math.round(y/this.gridSize)*this.gridSize;
                    }

                    const node = {
                        id: this.uuid(),
                        title: 'Child Node',
                        description: '',
                        x, y,
                        isRoot: false,
                        type: 'default',
                        parentId: parent.id,

                        // default ports (can change by re-connect)
                        parentPort: 'right',
                        childPort: 'left',
                    };

                    this.nodes.push(node);
                    this.selectedIds = [node.id];
                    this.pushHistory('addChild');
                    this.autoSave();

                    this.$nextTick(() => {
                        this.recalcNodeRects();
                        this.inlineEditId = node.id;
                        this.inlineEditTitle = node.title;
                    });
                },

                removeParent(node) {
                    if (!node || !node.parentId) return;
                    node.parentId = null;
                    node.parentPort = 'auto';
                    node.childPort = 'auto';
                    this.pushHistory('unlink');
                    this.autoSave();
                },

                deleteSelection() {
                    if (!this.selectedIds.length) return;

                    const del = new Set(this.selectedIds);
                    this.nodes = this.nodes.filter(n => !del.has(n.id));
                    this.nodes.forEach(n => {
                        if (n.parentId && del.has(n.parentId)) {
                            n.parentId = null;
                            n.parentPort = 'auto';
                            n.childPort = 'auto';
                        }
                    });

                    if (this.nodes.length && !this.nodes.some(n => n.isRoot)) {
                        this.nodes[0].isRoot = true;
                        this.nodes[0].parentId = null;
                    }

                    this.selectedIds = [];
                    this.pushHistory('delete');
                    this.autoSave();
                    this.$nextTick(() => this.recalcNodeRects());
                },

                duplicateSelection() {
                    if (!this.selectedIds.length) return;
                    const selected = this.nodes.filter(n => this.selectedIds.includes(n.id));
                    if (!selected.length) return;

                    const map = new Map();
                    const clones = selected.map(n => {
                        const id = this.uuid();
                        map.set(n.id, id);
                        return { ...JSON.parse(JSON.stringify(n)), id, x: n.x + 60, y: n.y + 60, isRoot:false };
                    });
                    clones.forEach(c => { if (c.parentId && map.has(c.parentId)) c.parentId = map.get(c.parentId); });

                    this.nodes.push(...clones);
                    this.selectedIds = clones.map(c => c.id);
                    this.pushHistory('duplicate');
                    this.autoSave();
                    this.$nextTick(() => this.recalcNodeRects());
                },

                copySelection() {
                    if (!this.selectedIds.length) return;
                    this.clipboard = JSON.parse(JSON.stringify(this.nodes.filter(n => this.selectedIds.includes(n.id))));
                },
                pasteSelection() {
                    if (!this.clipboard || !this.clipboard.length) return;
                    const map = new Map();
                    const clones = this.clipboard.map(n => {
                        const id = this.uuid();
                        map.set(n.id, id);
                        return { ...JSON.parse(JSON.stringify(n)), id, x: n.x + 70, y: n.y + 70, isRoot:false };
                    });
                    clones.forEach(c => { if (c.parentId && map.has(c.parentId)) c.parentId = map.get(c.parentId); });

                    this.nodes.push(...clones);
                    this.selectedIds = clones.map(c => c.id);
                    this.pushHistory('paste');
                    this.autoSave();
                    this.$nextTick(() => this.recalcNodeRects());
                },

                // ===== view =====
                zoomBy(delta) {
                    const rect = { width: this.canvas.width, height: this.canvas.height };
                    const cx = rect.width/2, cy = rect.height/2;
                    this.zoomAtScreen(cx, cy, delta);
                },

                zoomAtScreen(sx, sy, delta) {
                    const before = this.screenToWorld(sx, sy);
                    const nextZoom = this.clampZoom(this.zoom + delta);
                    if (nextZoom === this.zoom) return;

                    this.zoom = nextZoom;

                    // lock the world point under cursor
                    this.panX = sx - before.x * this.zoom;
                    this.panY = sy - before.y * this.zoom;

                    this.pushHistory('zoom');
                    this.$nextTick(() => this.recalcNodeRects());
                },

                handleWheel(e) {
                    const stage = document.getElementById('mm-stage');
                    const rect = stage.getBoundingClientRect();
                    const sx = e.clientX - rect.left;
                    const sy = e.clientY - rect.top;

                    const delta = e.deltaY > 0 ? -0.1 : 0.1;
                    this.zoomAtScreen(sx, sy, delta);
                },

                fitView() {
                    if (!this.nodes.length) return;

                    const stage = document.getElementById('mm-stage');
                    const rect = stage.getBoundingClientRect();

                    const xs = this.nodes.map(n => n.x), ys = this.nodes.map(n => n.y);
                    const minX = Math.min(...xs), maxX = Math.max(...xs);
                    const minY = Math.min(...ys), maxY = Math.max(...ys);

                    const padding = 320;
                    const width = (maxX - minX) + padding;
                    const height = (maxY - minY) + padding;

                    const zx = rect.width / width;
                    const zy = rect.height / height;
                    this.zoom = this.clampZoom(Math.min(zx, zy));

                    const cx = (minX + maxX)/2;
                    const cy = (minY + maxY)/2;
                    this.panX = rect.width/2 - cx * this.zoom;
                    this.panY = rect.height/2 - cy * this.zoom;

                    this.pushHistory('fit');
                    this.$nextTick(() => this.recalcNodeRects());
                },

                centerOnSelection() {
                    if (!this.selectedIds.length) return;
                    const stage = document.getElementById('mm-stage');
                    const rect = stage.getBoundingClientRect();
                    const selected = this.nodes.filter(n => this.selectedIds.includes(n.id));
                    if (!selected.length) return;

                    const minX = Math.min(...selected.map(n=>n.x));
                    const maxX = Math.max(...selected.map(n=>n.x));
                    const minY = Math.min(...selected.map(n=>n.y));
                    const maxY = Math.max(...selected.map(n=>n.y));

                    const cx = (minX + maxX)/2;
                    const cy = (minY + maxY)/2;

                    this.panX = rect.width/2 - cx * this.zoom;
                    this.panY = rect.height/2 - cy * this.zoom;
                    this.pushHistory('center');
                },

                // ===== inline edit =====
                startInlineEdit(node) { this.inlineEditId = node.id; this.inlineEditTitle = node.title || ''; },
                commitInlineEdit() {
                    const n = this.nodes.find(x => x.id === this.inlineEditId);
                    if (n) {
                        const v = (this.inlineEditTitle || '').trim();
                        if (v) n.title = v;
                        this.pushHistory('inlineEdit');
                        this.autoSave();
                        this.$nextTick(() => this.recalcNodeRects());
                    }
                    this.inlineEditId = null;
                    this.inlineEditTitle = '';
                },
                cancelInlineEdit() { this.inlineEditId = null; this.inlineEditTitle = ''; },

                // ===== history =====
                createSnapshot() {
                    return JSON.parse(JSON.stringify({
                        nodes: this.nodes,
                        zoom: this.zoom,
                        panX: this.panX,
                        panY: this.panY,
                        selectedIds: this.selectedIds
                    }));
                },
                pushHistory(desc='') {
                    try {
                        const snap = this.createSnapshot();
                        this.undoStack.push({ snap, desc });
                        if (this.undoStack.length > this.maxHistory) this.undoStack.shift();
                        this.redoStack = [];
                    } catch (e) { console.warn('pushHistory', e); }
                },
                applySnapshot(snap) {
                    this.nodes = snap.nodes.map(n => ({...n}));
                    this.zoom = snap.zoom; this.panX = snap.panX; this.panY = snap.panY;
                    this.selectedIds = snap.selectedIds || [];
                    this.$nextTick(() => this.recalcNodeRects());
                },
                undo() {
                    if (this.undoStack.length <= 1) return;
                    const cur = this.undoStack.pop();
                    this.redoStack.push(cur);
                    this.applySnapshot(this.undoStack[this.undoStack.length-1].snap);
                    this.autoSave();
                },
                redo() {
                    if (!this.redoStack.length) return;
                    const next = this.redoStack.pop();
                    const curSnap = this.createSnapshot();
                    this.undoStack.push({ snap: curSnap, desc:'redo-preserve' });
                    this.applySnapshot(next.snap);
                    this.autoSave();
                },

                // ===== keyboard =====
                onKeyDown(e) {
                    const tag = e.target?.tagName?.toLowerCase() || '';
                    const typing = tag === 'input' || tag === 'textarea' || e.target?.isContentEditable;
                    if (typing) return;

                    if (e.code === 'Space') { this.isSpaceDown = true; return; }

                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') { e.preventDefault(); this.undo(); return; }
                    if ((e.ctrlKey || e.metaKey) && (e.key.toLowerCase() === 'y' || (e.shiftKey && e.key.toLowerCase() === 'z'))) { e.preventDefault(); this.redo(); return; }

                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'c') { e.preventDefault(); this.copySelection(); return; }
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'v') { e.preventDefault(); this.pasteSelection(); return; }

                    if (e.key === 'Delete' || e.key === 'Backspace') { e.preventDefault(); this.deleteSelection(); return; }

                    if (e.key.toLowerCase() === 'v') this.tool = 'select';
                    if (e.key.toLowerCase() === 'n') this.quickAddNode();
                    if (e.key.toLowerCase() === 'f') this.fitView();
                    if (e.key.toLowerCase() === 'h') this.uiHidden = !this.uiHidden;

                    if (e.key === 'Escape') {
                        this.selectedIds = [];
                        this.cancelInlineEdit();
                        this.isConnecting = false;
                        this.connectSourceId = null;
                        this.templatesOpen = false;
                    }
                },
                onKeyUp(e) { if (e.code === 'Space') this.isSpaceDown = false; },
            };
        }
    </script>
    @endpush
</div>
@endsection
