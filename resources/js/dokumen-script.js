console.log('✅ dokumen-script.js loaded');

// ===== DOKUMEN EDITOR FUNCTIONS =====
window.documentEditors = {};

// ===== DOKUMEN COMMENT SECTION =====
window.documentCommentSection = function() {
    return {
        replyView: {
            active: false,
            parentComment: null
        },

        init() {
            this.$nextTick(() => {
                setTimeout(() => {
                    this.createEditorForDocument('document-main-comment-editor', {
                        placeholder: 'Ketik komentar Anda di sini...'
                    });
                }, 300);
            });
        },

        toggleReply(comment) {
            if (this.replyView.active && this.replyView.parentComment?.id === comment.id) {
                this.closeReplyView();
                return;
            }
            
            if (this.replyView.active && this.replyView.parentComment) {
                this.destroyReplyEditorForDocument(this.replyView.parentComment.id);
            }
            
            this.replyView.active = true;
            this.replyView.parentComment = comment;

            setTimeout(() => {
                this.initReplyEditorForDocument(comment.id);
            }, 150);
        },

        closeReplyView() {
            if (this.replyView.parentComment) {
                this.destroyReplyEditorForDocument(this.replyView.parentComment.id);
            }
            this.replyView.active = false;
            this.replyView.parentComment = null;
        },

        submitReplyFromEditor() {
            if (!this.replyView.parentComment) {
                alert('Komentar induk tidak ditemukan');
                return;
            }
            
            const parentId = this.replyView.parentComment.id;
            const content = this.getDocumentReplyEditorDataFor(parentId).trim();
            
            if (!content) {
                alert('Komentar balasan tidak boleh kosong!');
                return;
            }

            const alpineComponent = document.querySelector('[x-data]').__x.$data;
            alpineComponent.addReply(parentId, content);
            this.closeReplyView();
        },

        submitMainComment() {
            const content = this.getDocumentEditorData('document-main-comment-editor').trim();
            if (!content) {
                alert('Komentar tidak boleh kosong!');
                return;
            }

            const alpineComponent = document.querySelector('[x-data]').__x.$data;
            alpineComponent.addComment(alpineComponent.currentFile, content);

            const editor = window.documentEditors['document-main-comment-editor'];
            if (editor) editor.setData('');
        },

        // Editor Functions
        async createEditorForDocument(containerId, options = {}) {
            const el = document.getElementById(containerId);
            if (!el) {
                console.warn('Editor container not found:', containerId);
                return null;
            }

            el.innerHTML = '';

            const baseConfig = {
                toolbar: {
                    items: [
                        'undo', 'redo', '|',
                        'heading', '|',
                        'bold', 'italic', 'underline', 'strikethrough', '|',
                        'link', 'blockQuote', '|',
                        'bulletedList', 'numberedList', '|',
                        'insertTable'
                    ],
                    shouldNotGroupWhenFull: true
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraf', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                    ]
                },
                placeholder: options.placeholder || ''
            };

            try {
                const editor = await ClassicEditor.create(el, baseConfig);
                window.documentEditors[containerId] = editor;

                editor.model.document.on('change:data', () => {
                    const data = editor.getData();
                    const ev = new CustomEvent('editor-change', {
                        detail: { id: containerId, data }
                    });
                    window.dispatchEvent(ev);
                });

                return editor;
            } catch (err) {
                console.error('Editor creation error:', err);
                el.innerHTML = `<textarea id="${containerId}-fallback" class="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg bg-white resize-none">${options.initial || ''}</textarea>`;
                return null;
            }
        },

        destroyEditorForDocument(containerId) {
            const ed = window.documentEditors[containerId];
            if (ed) {
                ed.destroy().then(() => {
                    delete window.documentEditors[containerId];
                }).catch((e) => {
                    console.warn('Destroy editor error:', e);
                    delete window.documentEditors[containerId];
                });
            } else {
                const ta = document.getElementById(containerId + '-fallback');
                if (ta) ta.remove();
            }
        },

        getDocumentEditorData(containerId) {
            const ed = window.documentEditors[containerId];
            if (ed) return ed.getData();
            const ta = document.getElementById(containerId + '-fallback');
            return ta ? ta.value : '';
        },

        initReplyEditorForDocument(commentId) {
            const containerId = 'document-reply-editor-' + commentId;
            return this.createEditorForDocument(containerId, {
                placeholder: 'Ketik balasan Anda di sini...'
            });
        },

        destroyReplyEditorForDocument(commentId) {
            const containerId = 'document-reply-editor-' + commentId;
            this.destroyEditorForDocument(containerId);
        },

        getDocumentReplyEditorDataFor(commentId) {
            return this.getDocumentEditorData('document-reply-editor-' + commentId);
        },

        destroyDocumentMainEditor() {
            this.destroyEditorForDocument('document-main-comment-editor');
        }
    };
};

// Export functions untuk akses global
window.createEditorForDocument = window.documentCommentSection.prototype.createEditorForDocument;
window.destroyEditorForDocument = window.documentCommentSection.prototype.destroyEditorForDocument;
window.getDocumentEditorData = window.documentCommentSection.prototype.getDocumentEditorData;
window.initReplyEditorForDocument = window.documentCommentSection.prototype.initReplyEditorForDocument;
window.destroyReplyEditorForDocument = window.documentCommentSection.prototype.destroyReplyEditorForDocument;
window.getDocumentReplyEditorDataFor = window.documentCommentSection.prototype.getDocumentReplyEditorDataFor;