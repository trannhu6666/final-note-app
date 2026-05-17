@extends('layouts.app')

@section('content')
    <div class="container mb-5">
        <h3 class="fw-bold mb-4 text-primary">
            <i class="bi bi-people-fill me-2"></i>Shared with me
        </h3>

        <div class="row g-4" id="shared-notes-container">
        </div>
    </div>

    <div class="modal fade" id="editorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <input type="text" class="form-control form-control-lg border-0 fw-bold fs-4 shadow-none px-0"
                        placeholder="Title..." id="noteTitle">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <textarea class="form-control border-0 shadow-none px-0" rows="8" placeholder="Take a note..."
                        id="noteContent"></textarea>
                </div>

                <div class="modal-footer bg-light d-flex justify-content-between border-top-0">
                    <div class="d-flex gap-2">
                        <label class="btn btn-outline-secondary btn-sm" title="Add Images">
                            <i class="bi bi-image"></i>
                            <input type="file" multiple accept="image/*" class="d-none">
                        </label>
                    </div>
                    <span id="saveStatusIndicator" class="text-muted small"><i class="bi bi-cloud-check"></i> Saved</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Hàm lấy Token chung
        function getAuthHeaders(isFormData = false) {
            const token = localStorage.getItem('user_token');
            const headers = {};
            if (!isFormData) headers['Content-Type'] = 'application/json';
            if (token) headers['Authorization'] = `Bearer ${token}`;
            return headers;
        }

        let currentEditingNoteId = null;

        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('shared-notes-container');

            // --- 1. GỌI API LẤY DANH SÁCH NOTE ĐƯỢC CHIA SẺ ---
            function fetchSharedNotes() {
                fetch('/api/notes/shared', {
                    method: 'GET',
                    headers: getAuthHeaders()
                })
                    .then(async res => {
                        const contentType = res.headers.get("content-type");
                        if (!contentType || !contentType.includes("application/json")) {
                            throw new Error("API /api/notes/shared lỗi 500 hoặc chưa được code!");
                        }
                        return res.json();
                    })
                    .then(response => {
                        if (response.status === 'success') {
                            renderSharedNotes(response.data || []);
                        } else {
                            alert(response.message || "Lấy danh sách thất bại!");
                        }
                    })
                    .catch(err => {
                        console.error("Lỗi fetch shared notes:", err);
                        container.innerHTML = `<div class="col-12 text-center text-danger py-5 border border-danger rounded bg-light mt-3">
                                        <h5><i class="bi bi-bug"></i> Backend API Error</h5><p>${err.message}</p>
                                    </div>`;
                    });
            }

            // --- 2. RENDER DỮ LIỆU RA GIAO DIỆN ---
            function renderSharedNotes(notes) {
                container.innerHTML = '';

                if (notes.length === 0) {
                    container.innerHTML = `<div class="col-12 text-center text-muted py-5"><i class="bi bi-folder-x" style="font-size: 3rem;"></i><p class="mt-3">No shared notes found!</p></div>`;
                    return;
                }

                notes.forEach(note => {
                    // Phân loại giao diện dựa trên quyền (Read-only hay Can Edit)
                    const isEdit = (note.permission === 'edit');
                    const badgeClass = isEdit ? 'bg-primary' : 'bg-secondary';
                    const badgeIcon = isEdit ? 'bi-pencil-square' : 'bi-eye';
                    const badgeText = isEdit ? 'Can Edit' : 'Read-only';
                    const cursorStyle = isEdit ? 'cursor: pointer;' : 'cursor: default;';
                    const borderClass = isEdit ? 'border-primary editable-note shadow-sm' : 'border-0 shadow-sm';
                    const contentSnippet = note.content || '...';

                    const col = document.createElement('div');
                    col.className = 'col-md-4 note-wrapper';
                    col.innerHTML = `
                                        <div class="card h-100 note-card ${borderClass}" style="${cursorStyle}">
                                            <div class="card-body">
                                                <h5 class="card-title fw-bold">${note.title || 'Untitled'}</h5>
                                                <p class="card-text text-muted" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">${contentSnippet}</p>
                                            </div>
                                            <div class="card-footer bg-transparent d-flex flex-column small border-top-0 pt-0">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted text-truncate" style="max-width: 60%;" title="${note.owner_email}">
                                                        <i class="bi bi-person-circle text-primary me-1"></i> ${note.owner_email}
                                                    </span>
                                                    <span class="text-muted" style="font-size: 0.75rem;">
                                                        <i class="bi bi-clock me-1"></i> ${new Date(note.shared_at).toLocaleDateString()}
                                                    </span>
                                                </div>
                                                <span class="badge ${badgeClass} text-white w-auto align-self-start px-3 py-2 rounded-pill">
                                                    <i class="bi ${badgeIcon} me-1"></i> ${badgeText}
                                                </span>
                                            </div>
                                        </div>
                                    `;

                    // Nếu có quyền Edit, cho phép click vào card để mở Editor Modal
                    if (isEdit) {
                        col.querySelector('.note-card').addEventListener('click', () => {
                            openSharedNoteEditor(note);
                        });
                    }

                    container.appendChild(col);
                });
            }

            // Gọi API lần đầu khi load trang
            fetchSharedNotes();


            // --- 3. LOGIC MỞ MODAL VÀ REALTIME ---
            const noteTitleInput = document.getElementById('noteTitle');
            const noteContentInput = document.getElementById('noteContent');
            const saveStatusIndicator = document.getElementById('saveStatusIndicator');

            function openSharedNoteEditor(note) {
                currentEditingNoteId = note.id;
                noteTitleInput.value = note.title || '';
                noteContentInput.value = note.content || '';

                new bootstrap.Modal(document.getElementById('editorModal')).show();

                // Tham gia kênh Realtime WebSocket cho note này
                joinNoteRealtimeChannel(note.id);
            }

            // --- 4. AUTO-SAVE LÊN BACKEND (API PUT /api/notes/{id}) ---
            let autoSaveTimeout;
            function triggerAutoSave() {
                if (!currentEditingNoteId) return; // Note shared không có quyền tạo mới, chỉ update

                if (saveStatusIndicator) {
                    saveStatusIndicator.innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span> Saving...';
                }

                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(() => {
                    fetch(`/api/notes/${currentEditingNoteId}`, {
                        method: 'PUT',
                        headers: getAuthHeaders(),
                        body: JSON.stringify({
                            title: noteTitleInput.value,
                            content: noteContentInput.value
                        })
                    })
                        .then(res => res.json())
                        .then(response => {
                            if (response.status === 'success') {
                                saveStatusIndicator.innerHTML = '<i class="bi bi-cloud-check text-success"></i> Saved';
                                fetchSharedNotes(); // Cập nhật lại list ở ngoài
                            }
                        })
                        .catch(err => console.error(err));
                }, 1500);

                // Phát tín hiệu Realtime ngay khi gõ
                broadcastTypingStatus();
            }

            if (noteTitleInput && noteContentInput) {
                noteTitleInput.addEventListener('input', triggerAutoSave);
                noteContentInput.addEventListener('input', triggerAutoSave);
            }

            // --- 5. LOGIC WEBSOCKET (ĐỒNG BỘ TỪ TRANG CHỦ) ---
            let socket = null;
            function joinNoteRealtimeChannel(noteId) {
                socket = new WebSocket('ws://localhost:8080');
                socket.onopen = () => console.log(`🟢 Đã kết nối WebSocket cho Note chung: ${noteId}`);

                socket.onmessage = (event) => {
                    try {
                        const data = JSON.parse(event.data);
                        if (data.note_id === noteId && document.activeElement !== noteContentInput && document.activeElement !== noteTitleInput) {
                            if (data.title !== undefined) noteTitleInput.value = data.title;
                            if (data.content !== undefined) noteContentInput.value = data.content;

                            saveStatusIndicator.innerHTML = '<i class="bi bi-person-check-fill text-info"></i> Updated by owner/teammate';
                            setTimeout(() => saveStatusIndicator.innerHTML = '<i class="bi bi-cloud-check text-success"></i> Synced', 1500);
                        }
                    } catch (e) { console.error(e); }
                };
            }

            function broadcastTypingStatus() {
                if (socket && socket.readyState === 1 && currentEditingNoteId) {
                    socket.send(JSON.stringify({
                        note_id: currentEditingNoteId,
                        title: noteTitleInput.value,
                        content: noteContentInput.value
                    }));
                }
            }

            // --- 6. LIVE SEARCH (LỌC TRỰC TIẾP TRÊN DOM) ---
            const searchBox = document.getElementById('search-box');
            let searchTimeoutShared;

            if (searchBox) {
                searchBox.addEventListener('input', function () {
                    clearTimeout(searchTimeoutShared);
                    const keyword = this.value.trim().toLowerCase();

                    // Delay 300ms theo tiêu chí 17
                    searchTimeoutShared = setTimeout(() => {
                        const noteWrappers = document.querySelectorAll('.note-wrapper');
                        let hasResult = false;

                        noteWrappers.forEach(wrapper => {
                            const title = wrapper.querySelector('.card-title').innerText.toLowerCase();
                            const content = wrapper.querySelector('.card-text').innerText.toLowerCase();

                            if (title.includes(keyword) || content.includes(keyword)) {
                                wrapper.style.display = 'block';
                                hasResult = true;
                            } else {
                                wrapper.style.display = 'none';
                            }
                        });

                        let noResultMsg = document.getElementById('no-result-msg');
                        if (!hasResult) {
                            if (!noResultMsg) {
                                noResultMsg = document.createElement('div');
                                noResultMsg.id = 'no-result-msg';
                                noResultMsg.className = 'col-12 text-center text-muted py-5';
                                noResultMsg.innerHTML = '<i class="bi bi-search" style="font-size: 3rem;"></i><p class="mt-3">No shared notes match your search!</p>';
                                container.appendChild(noResultMsg);
                            }
                            noResultMsg.style.display = 'block';
                        } else if (noResultMsg) {
                            noResultMsg.style.display = 'none';
                        }
                    }, 300);
                });
            }
        });
    </script>
@endsection