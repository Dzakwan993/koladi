@extends('layouts.app')

@section('title', 'Mind Map')

<style>
    [x-cloak] {
        display: none !important;
    }

    #mindmap-stage {
        position: relative;
        width: 100%;
        height: calc(100vh - 130px);
        background-color: #f8fafc;

         /* buat macam di draw io */
        background-image:
        linear-gradient(rgba(100,116,139,0.15) 1px, transparent 1px),
        linear-gradient(90deg, rgba(100,116,139,0.15) 1px, transparent 1px);

    background-size: 20px 20px; /* ⬅️ lebih kecil */
        overflow: auto;
        border-top: 1px solid #e2e8f0;
    }

    #mindmap-area {
        position: relative;
        width: 2400px;
        height: 1400px;
    }

    #mindmap-canvas {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 1;
    }

    .mindmap-node {
        position: absolute;
        transform: translate(-50%, -50%);
        width: 240px;
        min-height: 90px;
        background: #ffffff;
        color: #0f172a;
        border: 2px solid #cbd5e1;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        padding: 14px;
        z-index: 10;
        user-select: none;
    }

    .mindmap-node.selected {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
    }

    .mindmap-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 6px;
        word-break: break-word;
    }

    .mindmap-description {
        font-size: 13px;
        color: #475569;
        word-break: break-word;
    }

    .toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        flex-wrap: wrap;
    }

    .toolbar button {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #0f172a;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }

    .toolbar button:hover {
        background: #f8fafc;
    }

    .toolbar button.primary {
        background: #2563eb;
        color: white;
        border-color: #2563eb;
    }

    .toolbar .status {
        margin-left: auto;
        font-size: 13px;
        color: #64748b;
    }

    .port {
        position: absolute;
        width: 12px;
        height: 12px;
        background: #2563eb;
        border: 2px solid white;
        border-radius: 999px;
        cursor: crosshair;
        z-index: 20;
        opacity: 0;
        transition: 0.15s ease;
    }

    .mindmap-node:hover .port,
    .mindmap-node.selected .port,
    .mindmap-node.connecting .port {
        opacity: 1;
    }

    .mindmap-node.ui-hidden .port {
        opacity: 0 !important;
        pointer-events: none !important;
    }

    .port.top {
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
    }

    .port.bottom {
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
    }

    .port.left {
        left: -8px;
        top: 50%;
        transform: translateY(-50%);
    }

    .port.right {
        right: -8px;
        top: 50%;
        transform: translateY(-50%);
    }

    .node-icons {
        position: absolute;
        top: 8px;
        right: 8px;
        display: flex;
        gap: 6px;
        opacity: 0;
        transition: 0.15s ease;
        z-index: 25;
    }

    .mindmap-node:hover .node-icons,
    .mindmap-node.selected .node-icons {
        opacity: 1;
    }

    .mindmap-node.ui-hidden .node-icons {
        opacity: 0 !important;
        pointer-events: none !important;
    }

    .node-icon-btn {
        width: 28px;
        height: 28px;
        border: 1px solid #cbd5e1;
        background: white;
        color: #0f172a;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
        line-height: 1;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
    }

    .node-icon-btn:hover {
        background: #f8fafc;
    }

    .node-icon-btn.danger {
        color: #b91c1c;
        border-color: #fecaca;
        background: #fff5f5;
    }

    .inline-input,
    .inline-textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 8px 10px;
        font-size: 14px;
        color: #0f172a;
        background: white;
    }

    .inline-input {
        font-weight: 700;
        margin-bottom: 8px;
    }

    .inline-textarea {
        min-height: 90px;
        resize: vertical;
        color: #334155;
    }

    .inline-actions {
        display: flex;
        gap: 8px;
        margin-top: 10px;
    }

    .inline-actions button {
        border: 1px solid #cbd5e1;
        background: white;
        color: #0f172a;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .inline-actions button.primary {
        background: #2563eb;
        color: white;
        border-color: #2563eb;
    }
</style>

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div x-data="simpleMindmap('{{ $mindmap->id }}')" x-init="init()" @mouseup.window="stopDragging()"
        @mousemove.window="onMouseMove($event)">
        @include('components.workspace-nav', ['active' => 'mindmap'])

        <div class="toolbar" x-show="!uiHidden" x-cloak>
            <button class="primary" @click="addNode()">+ Tambah Node</button>
            <button @click="deleteNode()" :disabled="!selectedNode">Hapus Node</button>

            <button @click="lineMode = 'straight'; drawConnections()" :class="lineMode === 'straight' ? 'primary' : ''">
                Mode Lurus
            </button>

            <button @click="lineMode = 'orthogonal'; drawConnections()" :class="lineMode === 'orthogonal' ? 'primary' : ''">
                Mode Siku
            </button>

            <button @click="uiHidden = true; resetConnect(); cancelInlineEdit()">Hide UI</button>

            <div class="status">
                <span x-show="isSaving">💾 Menyimpan...</span>

                <span x-show="!isSaving">
                    Total node: <span x-text="nodes.length"></span> |
                    Mode: <span x-text="lineMode === 'straight' ? 'Lurus' : 'Siku'"></span>
                </span>
            </div>
        </div>

        <div class="toolbar" x-show="uiHidden" x-cloak>
            <button @click="uiHidden = false">Show UI</button>
        </div>

        <div id="mindmap-stage">
            <div id="mindmap-area">
                <canvas id="mindmap-canvas" width="2400" height="1400"></canvas>

                <template x-for="node in nodes" :key="node.id">
                    <div class="mindmap-node"
                        :class="{
                            'selected': selectedId === node.id,
                            'ui-hidden': uiHidden,
                            'connecting': connectMode && connectSourceId === node.id
                        }"
                        :style="`left:${node.x}px; top:${node.y}px;`" @click.stop="selectNode(node)"
                        @mousedown.stop="startDragging(node, $event)">
                        <div class="port top" @mousedown.stop.prevent="handlePortClick(node, 'top')"></div>
                        <div class="port right" @mousedown.stop.prevent="handlePortClick(node, 'right')"></div>
                        <div class="port bottom" @mousedown.stop.prevent="handlePortClick(node, 'bottom')"></div>
                        <div class="port left" @mousedown.stop.prevent="handlePortClick(node, 'left')"></div>

                        <div class="node-icons">
                            <button class="node-icon-btn" title="Edit" @click.stop="startInlineEdit(node)">✏️</button>
                            <button class="node-icon-btn" title="Putus Kabel" @click.stop="unlinkNode(node)"
                                x-show="node.parentId" x-cloak>🔗</button>
                            <button class="node-icon-btn danger" title="Hapus"
                                @click.stop="deleteSingleNode(node.id)">🗑</button>
                        </div>

                        <template x-if="editingNodeId !== node.id">
                            <div>
                                <div class="mindmap-title" x-text="node.title"></div>
                                <div class="mindmap-description" x-text="node.description || 'Tanpa deskripsi'"></div>
                            </div>
                        </template>

                        <template x-if="editingNodeId === node.id">
                            <div @mousedown.stop>
                                <input type="text" class="inline-input" x-model="editForm.title" placeholder="Title">
                                <textarea class="inline-textarea" x-model="editForm.description" placeholder="Description"></textarea>

                                <div class="inline-actions">
                                    <button class="primary" @click.stop="saveInlineEdit()">Simpan</button>
                                    <button @click.stop="cancelInlineEdit()">Batal</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        @push('scripts')
            <script>
                function simpleMindmap(mindmapId) {
                    return {
                        mindmapId,
                        nodes: [],
                        selectedId: null,
                        isSaving: false,
                        uiHidden: false,
                        lineMode: 'orthogonal',

                        canvas: null,
                        ctx: null,

                        draggingNodeId: null,
                        dragOffsetX: 0,
                        dragOffsetY: 0,

                        connectMode: false,
                        connectSourceId: null,
                        connectSourcePort: 'right',

                        tempMouseX: 0,
                        tempMouseY: 0,

                        editingNodeId: null,
                        editForm: {
                            title: '',
                            description: ''
                        },

                        get selectedNode() {
                            return this.nodes.find(n => n.id === this.selectedId) || null;
                        },

                        async init() {
                            this.canvas = document.getElementById('mindmap-canvas');
                            this.ctx = this.canvas.getContext('2d');
                            await this.loadData();
                            this.drawConnections();
                        },

                        async loadData() {
                            try {
                                const response = await fetch(`/mindmap/${this.mindmapId}/data`);
                                const data = await response.json();

                                this.nodes = (data.nodes || []).map(node => {
                                    const parsed = this.parseConnectionSide(node.connectionSide);

                                    return {
                                        id: String(node.id),
                                        title: node.title || 'Untitled',
                                        description: node.description || '',
                                        x: Number(node.x || 200),
                                        y: Number(node.y || 200),
                                        isRoot: !!node.isRoot,
                                        type: 'default',
                                        parentId: node.parentId ? String(node.parentId) : null,
                                        parentPort: parsed.parentPort,
                                        childPort: parsed.childPort,
                                    };
                                });

                                this.$nextTick(() => this.drawConnections());
                            } catch (error) {
                                console.error(error);
                            }
                        },

                        parseConnectionSide(value) {
                            if (!value || value === 'auto' || value === 'auto:auto') {
                                return {
                                    parentPort: 'right',
                                    childPort: 'left'
                                };
                            }

                            if (value.includes(':')) {
                                const parts = value.split(':');
                                return {
                                    parentPort: parts[0] || 'right',
                                    childPort: parts[1] || 'left'
                                };
                            }

                            return {
                                parentPort: value,
                                childPort: 'left'
                            };
                        },

                        buildConnectionSide(node) {
                            return `${node.parentPort || 'right'}:${node.childPort || 'left'}`;
                        },

                        selectNode(node) {
                            if (this.connectMode) return;
                            this.selectedId = node.id;
                        },

                        startInlineEdit(node) {
                            this.resetConnect();
                            this.selectedId = node.id;
                            this.editingNodeId = node.id;
                            this.editForm.title = node.title || '';
                            this.editForm.description = node.description || '';
                        },

                        cancelInlineEdit() {
                            this.editingNodeId = null;
                            this.editForm.title = '';
                            this.editForm.description = '';
                        },

                        async saveInlineEdit() {
                            const node = this.nodes.find(n => n.id === this.editingNodeId);
                            if (!node) return;

                            node.title = this.editForm.title || 'Untitled';
                            node.description = this.editForm.description || '';

                            await this.saveAll();
                            this.cancelInlineEdit();
                            this.drawConnections();
                        },

                        addNode() {
                            const total = this.nodes.length;

                            const newNode = {
                                id: this.generateId(),
                                title: 'Node Baru',
                                description: '',
                                x: total === 0 ? 400 : 300 + (total * 180),
                                y: total === 0 ? 180 : 320,
                                isRoot: total === 0,
                                type: 'default',
                                parentId: null,
                                parentPort: 'right',
                                childPort: 'left',
                            };

                            this.nodes.push(newNode);
                            this.selectedId = newNode.id;

                            this.saveAll();
                            this.$nextTick(() => {
                                this.drawConnections();
                                this.startInlineEdit(newNode);
                            });
                        },

                        startDragging(node, event) {
                            if (this.connectMode || this.editingNodeId === node.id) return;

                            this.selectNode(node);

                            const nodeEl = event.currentTarget;
                            const rect = nodeEl.getBoundingClientRect();

                            this.draggingNodeId = node.id;
                            this.dragOffsetX = event.clientX - rect.left - (rect.width / 2);
                            this.dragOffsetY = event.clientY - rect.top - (rect.height / 2);
                        },

                        async stopDragging() {
                            if (!this.draggingNodeId) return;
                            this.draggingNodeId = null;
                            await this.saveAll();
                            this.drawConnections();
                        },

                        onMouseMove(event) {
                            const stage = document.getElementById('mindmap-stage');
                            const stageRect = stage.getBoundingClientRect();

                            this.tempMouseX = event.clientX - stageRect.left + stage.scrollLeft;
                            this.tempMouseY = event.clientY - stageRect.top + stage.scrollTop;

                            if (this.draggingNodeId) {
                                const node = this.nodes.find(n => n.id === this.draggingNodeId);
                                if (!node) return;

                                node.x = this.tempMouseX - this.dragOffsetX;
                                node.y = this.tempMouseY - this.dragOffsetY;
                                this.drawConnections();
                            }

                            if (this.connectMode) {
                                this.drawConnections();
                            }
                        },

                        handlePortClick(node, side) {
                            if (this.uiHidden) return;

                            if (!this.connectMode) {
                                this.startConnect(node, side);
                                return;
                            }

                            this.finishConnect(node, side);
                        },

                        startConnect(node, side) {
                            if (this.uiHidden) return;

                            this.cancelInlineEdit();
                            this.selectedId = node.id;
                            this.connectMode = true;
                            this.connectSourceId = node.id;
                            this.connectSourcePort = side;
                            this.drawConnections();
                        },

                        async finishConnect(targetNode, targetPort) {
                            const sourceNode = this.nodes.find(n => n.id === this.connectSourceId);

                            if (!sourceNode || !targetNode) {
                                this.resetConnect();
                                return;
                            }

                            if (sourceNode.id === targetNode.id) {
                                this.resetConnect();
                                return;
                            }

                            if (this.willCreateCycle(sourceNode.id, targetNode.id)) {
                                alert('Tidak bisa membuat hubungan melingkar.');
                                this.resetConnect();
                                return;
                            }

                            targetNode.parentId = sourceNode.id;
                            targetNode.isRoot = false;
                            targetNode.parentPort = this.connectSourcePort;
                            targetNode.childPort = targetPort;

                            await this.saveAll();
                            this.drawConnections();
                            this.resetConnect();
                        },

                        resetConnect() {
                            this.connectMode = false;
                            this.connectSourceId = null;
                            this.connectSourcePort = 'right';
                            this.drawConnections();
                        },

                        willCreateCycle(sourceId, targetId) {
                            let current = sourceId;

                            while (current) {
                                if (current === targetId) return true;
                                const node = this.nodes.find(n => n.id === current);
                                current = node ? node.parentId : null;
                            }

                            return false;
                        },

                        async unlinkNode(node) {
                            if (!node || !node.parentId) return;

                            node.parentId = null;
                            node.parentPort = 'right';
                            node.childPort = 'left';
                            node.isRoot = false;

                            if (!this.nodes.some(n => n.parentId === null && n.id !== node.id)) {
                                node.isRoot = true;
                            }

                            await this.saveAll();
                            this.drawConnections();
                        },

                        async deleteNode() {
                            if (!this.selectedNode) return;
                            await this.deleteSingleNode(this.selectedNode.id);
                        },

                        async deleteSingleNode(nodeId) {
                            if (!confirm('Hapus node ini?')) return;

                            const deleteIds = new Set();
                            this.collectChildren(nodeId, deleteIds);

                            this.nodes = this.nodes.filter(node => !deleteIds.has(node.id));

                            if (this.nodes.length > 0 && !this.nodes.some(node => node.parentId === null)) {
                                this.nodes[0].isRoot = true;
                                this.nodes[0].parentId = null;
                                this.nodes[0].parentPort = 'right';
                                this.nodes[0].childPort = 'left';
                            }

                            if (this.selectedId === nodeId) {
                                this.selectedId = null;
                            }

                            if (this.editingNodeId === nodeId) {
                                this.cancelInlineEdit();
                            }

                            await this.saveAll();
                            this.drawConnections();
                        },

                        collectChildren(parentId, deleteIds) {
                            deleteIds.add(parentId);

                            this.nodes
                                .filter(node => node.parentId === parentId)
                                .forEach(child => this.collectChildren(child.id, deleteIds));
                        },

                        getPortPosition(node, side, gap = 0) {
                            const width = 240;
                            const height = 90;

                            switch (side) {
                                case 'top':
                                    return {
                                        x: node.x, y: node.y - (height / 2) - gap
                                    };
                                case 'bottom':
                                    return {
                                        x: node.x, y: node.y + (height / 2) + gap
                                    };
                                case 'left':
                                    return {
                                        x: node.x - (width / 2) - gap, y: node.y
                                    };
                                case 'right':
                                default:
                                    return {
                                        x: node.x + (width / 2) + gap, y: node.y
                                    };
                            }
                        },

                        isStraightLine(start, end) {
                            return start.x === end.x || start.y === end.y;
                        },

                        drawArrow(from, to) {
                            const headLength = 12;
                            const angle = Math.atan2(to.y - from.y, to.x - from.x);

                            this.ctx.beginPath();
                            this.ctx.moveTo(to.x, to.y);
                            this.ctx.lineTo(
                                to.x - headLength * Math.cos(angle - Math.PI / 6),
                                to.y - headLength * Math.sin(angle - Math.PI / 6)
                            );
                            this.ctx.lineTo(
                                to.x - headLength * Math.cos(angle + Math.PI / 6),
                                to.y - headLength * Math.sin(angle + Math.PI / 6)
                            );
                            this.ctx.closePath();
                            this.ctx.fillStyle = '#2563eb';
                            this.ctx.fill();
                        },

                        drawStraightConnection(startNode, startSide, endNode, endSide) {
                            const start = this.getPortPosition(startNode, startSide, 0);
                            const end = this.getPortPosition(endNode, endSide, 0);

                            this.ctx.beginPath();
                            this.ctx.moveTo(start.x, start.y);
                            this.ctx.lineTo(end.x, end.y);
                            this.ctx.stroke();

                            this.drawArrow(start, end);
                        },

                        drawOrthogonalConnection(startNode, startSide, endNode, endSide) {
                            const gap = 22;

                            const startEdge = this.getPortPosition(startNode, startSide, 0);
                            const endEdge = this.getPortPosition(endNode, endSide, 0);

                            const start = this.getPortPosition(startNode, startSide, gap);
                            const end = this.getPortPosition(endNode, endSide, gap);

                            const points = [startEdge, start];

                            if (this.isStraightLine(start, end)) {
                                points.push(end, endEdge);
                            } else {
                                const startHorizontal = startSide === 'left' || startSide === 'right';
                                const endHorizontal = endSide === 'left' || endSide === 'right';

                                if (startHorizontal && endHorizontal) {
                                    const midX = (start.x + end.x) / 2;
                                    points.push({
                                        x: midX,
                                        y: start.y
                                    }, {
                                        x: midX,
                                        y: end.y
                                    });
                                } else if (!startHorizontal && !endHorizontal) {
                                    const midY = (start.y + end.y) / 2;
                                    points.push({
                                        x: start.x,
                                        y: midY
                                    }, {
                                        x: end.x,
                                        y: midY
                                    });
                                } else if (startHorizontal && !endHorizontal) {
                                    points.push({
                                        x: end.x,
                                        y: start.y
                                    });
                                } else {
                                    points.push({
                                        x: start.x,
                                        y: end.y
                                    });
                                }

                                points.push(end, endEdge);
                            }

                            this.ctx.beginPath();
                            this.ctx.moveTo(points[0].x, points[0].y);

                            for (let i = 1; i < points.length; i++) {
                                this.ctx.lineTo(points[i].x, points[i].y);
                            }

                            this.ctx.stroke();

                            const arrowFrom = points[points.length - 2];
                            const arrowTo = points[points.length - 1];
                            this.drawArrow(arrowFrom, arrowTo);
                        },

                        drawStraightPreview(startNode, startSide, mouseX, mouseY) {
                            const start = this.getPortPosition(startNode, startSide, 0);

                            this.ctx.save();
                            this.ctx.setLineDash([6, 6]);
                            this.ctx.beginPath();
                            this.ctx.moveTo(start.x, start.y);
                            this.ctx.lineTo(mouseX, mouseY);
                            this.ctx.stroke();
                            this.ctx.restore();

                            this.drawArrow(start, {
                                x: mouseX,
                                y: mouseY
                            });
                        },

                        drawPreviewConnection(startNode, startSide, mouseX, mouseY) {
                            const gap = 22;
                            const startEdge = this.getPortPosition(startNode, startSide, 0);
                            const start = this.getPortPosition(startNode, startSide, gap);

                            const points = [startEdge, start];

                            if (start.x === mouseX || start.y === mouseY) {
                                points.push({
                                    x: mouseX,
                                    y: mouseY
                                });
                            } else {
                                const startHorizontal = startSide === 'left' || startSide === 'right';

                                if (startHorizontal) {
                                    points.push({
                                        x: mouseX,
                                        y: start.y
                                    });
                                } else {
                                    points.push({
                                        x: start.x,
                                        y: mouseY
                                    });
                                }

                                points.push({
                                    x: mouseX,
                                    y: mouseY
                                });
                            }

                            this.ctx.save();
                            this.ctx.setLineDash([6, 6]);
                            this.ctx.beginPath();
                            this.ctx.moveTo(points[0].x, points[0].y);

                            for (let i = 1; i < points.length; i++) {
                                this.ctx.lineTo(points[i].x, points[i].y);
                            }

                            this.ctx.stroke();
                            this.ctx.restore();

                            const arrowFrom = points[points.length - 2];
                            const arrowTo = points[points.length - 1];
                            this.drawArrow(arrowFrom, arrowTo);
                        },

                        drawConnections() {
                            if (!this.ctx) return;

                            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                            this.ctx.strokeStyle = '#2563eb';
                            this.ctx.lineWidth = 2;
                            this.ctx.fillStyle = '#2563eb';
                            this.ctx.lineJoin = 'round';
                            this.ctx.lineCap = 'round';

                            this.nodes.forEach(node => {
                                if (!node.parentId) return;

                                const parent = this.nodes.find(n => n.id === node.parentId);
                                if (!parent) return;

                                if (this.lineMode === 'straight') {
                                    this.drawStraightConnection(
                                        parent,
                                        node.parentPort || 'right',
                                        node,
                                        node.childPort || 'left'
                                    );
                                } else {
                                    this.drawOrthogonalConnection(
                                        parent,
                                        node.parentPort || 'right',
                                        node,
                                        node.childPort || 'left'
                                    );
                                }
                            });

                            if (this.connectMode && this.connectSourceId) {
                                const sourceNode = this.nodes.find(n => n.id === this.connectSourceId);
                                if (sourceNode) {
                                    if (this.lineMode === 'straight') {
                                        this.drawStraightPreview(
                                            sourceNode,
                                            this.connectSourcePort,
                                            this.tempMouseX,
                                            this.tempMouseY
                                        );
                                    } else {
                                        this.drawPreviewConnection(
                                            sourceNode,
                                            this.connectSourcePort,
                                            this.tempMouseX,
                                            this.tempMouseY
                                        );
                                    }
                                }
                            }
                        },

                        async saveAll() {
                            this.isSaving = true; // ⬅️ mulai loading
                            try {
                                await fetch(`/mindmap/${this.mindmapId}/save`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                            'content')
                                    },
                                    body: JSON.stringify({
                                        nodes: this.nodes.map((node) => ({
                                            id: node.id,
                                            title: node.title,
                                            description: node.description,
                                            x: node.x,
                                            y: node.y,
                                            parentId: node.parentId,
                                            type: 'default',
                                            connectionSide: this.buildConnectionSide(node)
                                        }))
                                    })
                                });
                            } catch (error) {
                                console.error('Save failed:', error);
                            } finally {
                                this.isSaving = false; // selesai loding
                            }
                        },

                        generateId() {
                            if (window.crypto && crypto.randomUUID) {
                                return crypto.randomUUID();
                            }

                            return 'node-' + Date.now() + '-' + Math.floor(Math.random() * 100000);
                        }
                    }
                }
            </script>
        @endpush
    </div>
@endsection
