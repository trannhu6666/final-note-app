@extends('layouts.app')

@section('content')
    <style>
        /* --- CSS FOR GRID / LIST VIEW --- */
        .notes-layout-container.grid-mode .note-wrapper {
            flex: 0 0 auto;
            width: 33.333333%;
        }

        @media (max-width: 991px) {
            .notes-layout-container.grid-mode .note-wrapper {
                width: 50%;
            }
        }

        @media (max-width: 575px) {
            .notes-layout-container.grid-mode .note-wrapper {
                width: 100%;
            }
        }

        /* List Mode: Horizontal cards */
        .notes-layout-container.list-mode .note-wrapper {
            width: 100%;
        }

        .notes-layout-container.list-mode .card {
            flex-direction: row;
            align-items: center;
        }

        .notes-layout-container.list-mode .card-body {
            flex: 1;
        }

        .notes-layout-container.list-mode .card-footer {
            border-left: 1px solid rgba(0, 0, 0, 0.1);
            border-top: none !important;
            flex-direction: column;
            justify-content: center;
            min-width: 150px;
        }

        /* Chỉnh màu cho danh sách menu bên trái (Active Item) */
        .list-group-item.active {
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
        }
    </style>

    <div class="row">
        <div class="col-md-3 d-none d-md-block mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-tags-fill text-primary"></i> Labels</h5>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#labelModal">
                    <i class="bi bi-gear"></i> Manage
                </button>
            </div>
            <div class="list-group shadow-sm" id="labelFilterMenu">
                <a href="#" class="list-group-item list-group-item-action active border-0 label-filter-item" data-label="">
                    <i class="bi bi-collection"></i> All Notes
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="alert alert-warning alert-dismissible fade show d-none mb-3" id="unverifiedHomepageAlert"
                role="alert">
                <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                <strong>Your account is unverified!</strong> Please check your email to complete the activation process.
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#editorModal">
                    <i class="bi bi-plus-lg"></i> Create New Note
                </button>

                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small d-none d-sm-inline">Sort by: Modified</span>
                    <div class="btn-group shadow-sm" role="group">
                        <input type="radio" class="btn-check" name="viewToggle" id="gridView" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary btn-sm" for="gridView" title="Grid View"><i
                                class="bi bi-grid"></i></label>

                        <input type="radio" class="btn-check" name="viewToggle" id="listView" autocomplete="off">
                        <label class="btn btn-outline-secondary btn-sm" for="listView" title="List View"><i
                                class="bi bi-list-task"></i></label>
                    </div>
                </div>
            </div>

            <div id="notes-container" class="row g-3 notes-layout-container grid-mode mb-4"></div>
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
                        <div id="image-preview-area" class="d-flex flex-wrap gap-2 mt-3 d-none"></div>
                    </div>

                    <div class="modal-footer bg-light d-flex justify-content-between border-top-0">
                        <div class="d-flex gap-2 align-items-center">
                            <label class="btn btn-outline-secondary btn-sm mb-0" title="Add Images">
                                <i class="bi bi-image"></i>
                                <input type="file" id="noteImageInput" multiple accept="image/*" class="d-none">
                            </label>
                            <button type="button" id="btnTogglePin" class="btn btn-outline-warning btn-sm" title="Pin Note">
                                <i class="bi bi-pin-angle"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm" title="Password Protect" data-bs-toggle="collapse"
                                data-bs-target="#passwordSection">
                                <i class="bi bi-lock"></i>
                            </button>
                            <button class="btn btn-outline-info btn-sm" title="Share Note" id="btnShareNote">
                                <i class="bi bi-share"></i>
                            </button>

                            <div class="dropdown d-inline-block">
                                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false" title="Add Label">
                                    <i class="bi bi-tag"></i>
                                </button>
                                <ul class="dropdown-menu shadow pb-1" id="labelDropdownList">
                                    <li>
                                        <h6 class="dropdown-header">Assign Label</h6>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <span id="saveStatusIndicator" class="text-muted small"><i class="bi bi-cloud-check"></i>
                            Saved</span>
                    </div>

                    <div class="collapse bg-white border-top p-3" id="passwordSection">
                        <h6 class="text-danger"><i class="bi bi-shield-lock"></i> Note Password Protection</h6>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="password" id="notePassword" class="form-control form-control-sm"
                                    placeholder="Set Password">
                            </div>
                            <div class="col-md-5">
                                <input type="password" id="noteConfirmPassword" class="form-control form-control-sm"
                                    placeholder="Confirm Password">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-sm w-100" id="btnSavePassword">Khóa</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-body text-center pt-4 pb-2">
                        <i class="bi bi-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 fw-bold">Delete Note?</h5>
                        <p class="text-muted small">Are you sure you want to delete this note? This action cannot be undone.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center border-top-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="labelModal" tabindex="-1">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit labels</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <input type="text" id="newLabelInput" class="form-control" placeholder="Create new label">
                            <button class="btn btn-primary" type="button" id="btnAddLabel"><i
                                    class="bi bi-check"></i></button>
                        </div>
                        <ul class="list-group list-group-flush" id="labelListContainer">
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Cấu hình Header dùng chung đính kèm Token xác thực cho mọi Request
            function getAuthHeaders(isFormData = false) {
                const token = localStorage.getItem('user_token');
                const headers = {};
                if (!isFormData) headers['Content-Type'] = 'application/json';
                if (token) headers['Authorization'] = `Bearer ${token}`;
                return headers;
            }

            // Các biến trạng thái toàn cục
            let currentEditingNoteId = null;
            let isNotePinned = false;
            let noteElementToDelete = null;
            let noteIdToDelete = null;

            document.addEventListener('DOMContentLoaded', function () {
                const notesContainer = document.getElementById('notes-container');
                const searchBox = document.getElementById('search-box');
                let searchTimeout;

                // --- 1. API LẤY DANH SÁCH NOTE (GET /api/notes) ---
                function fetchAndRenderNotes(searchKeyword = '', labelId = '') {
                    let url = '/api/notes';
                    const params = new URLSearchParams();
                    if (searchKeyword) params.append('search', searchKeyword);
                    if (labelId) params.append('label_id', labelId);
                    if (params.toString()) url += `?${params.toString()}`;

                    fetch(url, { method: 'GET', headers: getAuthHeaders() })
                        .then(async res => {
                            const contentType = res.headers.get("content-type");
                            if (!contentType || !contentType.includes("application/json")) {
                                throw new Error("Backend API đang bị lỗi 500 hoặc chưa hoàn thiện!");
                            }
                            return res.json();
                        })
                        .then(response => {
                            if (response.status === 'success') {
                                const notesArray = response.data || [];
                                renderNotes(notesArray);

                                const alertBox = document.getElementById('unverifiedHomepageAlert');
                                if (alertBox && response.user && response.user.is_active === false) {
                                    alertBox.classList.remove('d-none');
                                }
                            } else {
                                throw new Error(response.message || "Lỗi logic từ Backend");
                            }
                        })
                        .catch(err => {
                            console.error("Lỗi khi tải danh sách notes:", err);
                            if (notesContainer) {
                                notesContainer.innerHTML = `<div class="col-12 text-center text-danger py-5 border border-danger rounded bg-light mt-3">
                                        <h5><i class="bi bi-bug"></i> Backend API Error</h5><p>${err.message}</p>
                                    </div>`;
                            }
                        });
                }

                // --- 2. ĐỔ DỮ LIỆU TỪ API RA HTML ---
                function renderNotes(notes) {
                    if (!notesContainer) return;
                    notesContainer.innerHTML = '';
                    if (!notes || notes.length === 0) {
                        notesContainer.innerHTML = `<div class="col-12 text-center text-muted py-5"><i class="bi bi-search" style="font-size: 3rem;"></i><p class="mt-3">No notes found!</p></div>`;
                        return;
                    }

                    const sortedNotes = [...notes].sort((a, b) => b.is_pinned - a.is_pinned);

                    sortedNotes.forEach(note => {
                        let iconsHtml = '';
                        if (note.is_locked) iconsHtml += '<i class="bi bi-lock-fill text-danger me-1" title="Locked"></i>';
                        if (note.is_pinned) iconsHtml += '<i class="bi bi-pin-angle-fill text-warning me-1" title="Pinned"></i>';
                        if (note.is_shared) iconsHtml += '<i class="bi bi-people-fill text-primary" title="Shared"></i>';

                        let labelsHtml = '';
                        if (note.labels && note.labels.length > 0) {
                            note.labels.forEach(lbl => {
                                labelsHtml += `<span class="badge bg-secondary text-white mt-2 me-1">${lbl.name}</span>`;
                            });
                        }

                        let displayContent = note.is_locked ? '🔒 Content is password protected...' : (note.content || '');

                        const col = document.createElement('div');
                        col.className = 'col-12 note-wrapper';
                        col.innerHTML = `
                                <div class="card h-100 shadow-sm note-card" style="cursor: pointer;" data-id="${note.id}">
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold d-flex justify-content-between align-items-start">
                                            ${note.title || 'Untitled'}<div>${iconsHtml}</div>
                                        </h5>
                                        <p class="card-text">${displayContent}</p>
                                        <div class="note-labels-area">${labelsHtml}</div>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0 text-muted small d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-clock"></i> ${new Date(note.updated_at).toLocaleString()}</span>
                                        <button class="btn btn-sm btn-light btn-delete" data-id="${note.id}"><i class="bi bi-trash text-danger"></i></button>
                                    </div>
                                </div>`;

                        // Sự kiện click để xem/sửa Note (Xử lý Mở Khóa - Tiêu chí 21-23)
                        col.querySelector('.note-card').addEventListener('click', function (e) {
                            if (e.target.closest('.btn-delete')) return;

                            if (note.is_locked) {
                                const pass = prompt("Ghi chú này đã được khóa bảo mật. Vui lòng nhập mật khẩu:");
                                if (pass) unlockNote(note.id, pass, note);
                            } else {
                                openNoteEditor(note);
                            }
                        });

                        col.querySelector('.btn-delete').addEventListener('click', function (e) {
                            e.stopPropagation();
                            noteIdToDelete = this.getAttribute('data-id');
                            noteElementToDelete = col;
                            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
                        });
                        notesContainer.appendChild(col);
                    });
                }

                // --- 3. ĐỔ CHI TIẾT NOTE VÀO MODAL KHI EDIT ---
                function openNoteEditor(note) {
                    currentEditingNoteId = note.id;
                    document.getElementById('noteTitle').value = note.title || '';
                    document.getElementById('noteContent').value = note.content || '';
                    isNotePinned = !!note.is_pinned;
                    updatePinButtonUI();

                    if (note.permission === 'edit' || !note.is_shared) {
                        joinNoteRealtimeChannel(note.id);
                    }
                    new bootstrap.Modal(document.getElementById('editorModal')).show();
                }

                function updatePinButtonUI() {
                    const btnTogglePin = document.getElementById('btnTogglePin');
                    if (isNotePinned) {
                        btnTogglePin.classList.replace('btn-outline-warning', 'btn-warning');
                        btnTogglePin.classList.add('text-dark');
                    } else {
                        btnTogglePin.classList.replace('btn-warning', 'btn-outline-warning');
                        btnTogglePin.classList.remove('text-dark');
                    }
                }

                fetchAndRenderNotes();

                // --- 4. LIVE SEARCH HOÀN CHỈNH (DELAY 300MS) ---
                if (searchBox) {
                    searchBox.addEventListener('input', function () {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => fetchAndRenderNotes(this.value.trim()), 300);
                    });
                }

                // --- 5. LOGIC XÓA NOTE ---
                document.getElementById('confirmDeleteBtn')?.addEventListener('click', function () {
                    if (!noteIdToDelete) return;
                    fetch(`/api/notes/${noteIdToDelete}`, { method: 'DELETE', headers: getAuthHeaders() })
                        .then(res => res.json())
                        .then(response => {
                            if (response.status === 'success') {
                                if (noteElementToDelete) noteElementToDelete.remove();
                                bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
                            }
                        }).catch(err => console.error(err));
                });

                // --- 6. GRID / LIST LAYOUT VIEW TOGGLE ---
                document.getElementById('gridView')?.addEventListener('change', () => notesContainer.classList.replace('list-mode', 'grid-mode'));
                document.getElementById('listView')?.addEventListener('change', () => notesContainer.classList.replace('grid-mode', 'list-mode'));

                // --- 7. AUTO-SAVE LOGIC THỰC TẾ ---
                const noteTitleInput = document.getElementById('noteTitle');
                const noteContentInput = document.getElementById('noteContent');
                const saveStatusIndicator = document.getElementById('saveStatusIndicator');
                let autoSaveTimeout;

                document.getElementById('btnTogglePin')?.addEventListener('click', function () {
                    isNotePinned = !isNotePinned;
                    updatePinButtonUI();
                    triggerAutoSave();
                });

                function triggerAutoSave() {
                    if (saveStatusIndicator) saveStatusIndicator.innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span> Saving...';
                    clearTimeout(autoSaveTimeout);
                    autoSaveTimeout = setTimeout(() => saveNoteDataToServer(), 1500);
                }

                function saveNoteDataToServer() {
                    const payload = {
                        title: noteTitleInput.value,
                        content: noteContentInput.value,
                        is_pinned: isNotePinned ? 1 : 0
                    };
                    let url = currentEditingNoteId ? `/api/notes/${currentEditingNoteId}` : '/api/notes';
                    let method = currentEditingNoteId ? 'PUT' : 'POST';

                    fetch(url, { method: method, headers: getAuthHeaders(), body: JSON.stringify(payload) })
                        .then(res => res.json())
                        .then(response => {
                            if (response.status === 'success') {
                                if (!currentEditingNoteId && response.data.id) currentEditingNoteId = response.data.id;
                                saveStatusIndicator.innerHTML = '<i class="bi bi-cloud-check text-success"></i> Saved to Cloud';
                                fetchAndRenderNotes();
                            }
                        }).catch(err => {
                            saveStatusIndicator.innerHTML = '<i class="bi bi-x-circle text-danger"></i> Save failed';
                        });
                }

                noteTitleInput?.addEventListener('input', triggerAutoSave);
                noteContentInput?.addEventListener('input', triggerAutoSave);

                // --- 8. REALTIME COLLABORATION VỚI WEBSOCKETS ---
                let socket = null;
                function joinNoteRealtimeChannel(noteId) {
                    socket = new WebSocket('ws://localhost:8080');
                    socket.onopen = () => console.log(`🟢 WebSocket connected for Note: ${noteId}`);
                    socket.onmessage = (event) => {
                        try {
                            const data = JSON.parse(event.data);
                            if (data.note_id === noteId && document.activeElement !== noteContentInput && document.activeElement !== noteTitleInput) {
                                if (data.title !== undefined) noteTitleInput.value = data.title;
                                if (data.content !== undefined) noteContentInput.value = data.content;
                                saveStatusIndicator.innerHTML = '<i class="bi bi-person-check-fill text-info"></i> Updated by collaborator';
                                setTimeout(() => saveStatusIndicator.innerHTML = '<i class="bi bi-cloud-check text-success"></i> Saved', 1500);
                            }
                        } catch (e) { console.error(e); }
                    };
                }
                function broadcastTypingStatus() {
                    if (socket && socket.readyState === 1 && currentEditingNoteId) {
                        socket.send(JSON.stringify({ note_id: currentEditingNoteId, title: noteTitleInput.value, content: noteContentInput.value }));
                    }
                }
                noteTitleInput?.addEventListener('input', broadcastTypingStatus);
                noteContentInput?.addEventListener('input', broadcastTypingStatus);

                // --- 9. RESET MODAL KHI TẠO MỚI ---
                document.querySelector('[data-bs-target="#editorModal"]')?.addEventListener('click', function () {
                    currentEditingNoteId = null;
                    noteTitleInput.value = '';
                    noteContentInput.value = '';
                    isNotePinned = false;
                    updatePinButtonUI();
                    if (saveStatusIndicator) saveStatusIndicator.innerHTML = '<i class="bi bi-cloud"></i> New Note Ready';
                });

                // ==============================================================
                // CÁC ĐOẠN CODE API TÍCH HỢP MỚI THÊM (LABELS, PASSWORD, SHARE)
                // ==============================================================

                // --- API 7: QUẢN LÝ NHÃN (LABELS) ---
                function fetchLabels() {
                    fetch('/api/labels', { method: 'GET', headers: getAuthHeaders() })
                        .then(res => res.json())
                        .then(response => {
                            if (response.status === 'success') {
                                const labels = response.data || [];

                                // 1. Render Menu Sidebar
                                const sidebar = document.getElementById('labelFilterMenu');
                                if (sidebar) {
                                    sidebar.innerHTML = `<a href="#" class="list-group-item list-group-item-action active border-0 label-filter-item" onclick="filterByLabel('')"><i class="bi bi-collection"></i> All Notes</a>`;
                                    labels.forEach(lbl => {
                                        sidebar.innerHTML += `<a href="#" class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center label-filter-item" onclick="filterByLabel(${lbl.id})"><span><i class="bi bi-tag"></i> ${lbl.name}</span></a>`;
                                    });
                                }

                                // 2. Render Modal Xóa Label
                                const manager = document.getElementById('labelListContainer');
                                if (manager) {
                                    manager.innerHTML = '';
                                    labels.forEach(lbl => {
                                        manager.innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <input type="text" class="form-control border-0 shadow-none bg-transparent" value="${lbl.name}" readonly>
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete-label" onclick="window.deleteLabel(${lbl.id})"><i class="bi bi-trash"></i></button>
                                        </li>`;
                                    });
                                }

                                // 3. Render Dropdown gán Label trong Modal Editor
                                const dropdown = document.getElementById('labelDropdownList');
                                if (dropdown) {
                                    dropdown.innerHTML = `<li><h6 class="dropdown-header">Assign Label</h6></li>`;
                                    labels.forEach(lbl => {
                                        dropdown.innerHTML += `<li><a class="dropdown-item d-flex align-items-center" href="#"><input class="form-check-input me-2 mt-0" type="checkbox" value="${lbl.id}" id="label_${lbl.id}"><label class="form-check-label w-100" for="label_${lbl.id}">${lbl.name}</label></a></li>`;
                                    });
                                }
                            }
                        });
                }

                // Gắn hàm xóa label ra biến toàn cục window để gọi được từ thẻ HTML trực tiếp
                window.deleteLabel = function (labelId) {
                    fetch(`/api/labels/${labelId}`, { method: 'DELETE', headers: getAuthHeaders() })
                        .then(res => res.json())
                        .then(response => { if (response.status === 'success') fetchLabels(); });
                };

                window.filterByLabel = function (labelId) {
                    fetchAndRenderNotes('', labelId); // Lọc notes theo nhãn
                };

                document.getElementById('btnAddLabel')?.addEventListener('click', function () {
                    const newLabelInput = document.getElementById('newLabelInput');
                    if (!newLabelInput.value.trim()) return;
                    fetch('/api/labels', { method: 'POST', headers: getAuthHeaders(), body: JSON.stringify({ name: newLabelInput.value.trim() }) })
                        .then(res => res.json())
                        .then(response => {
                            if (response.status === 'success') {
                                newLabelInput.value = '';
                                fetchLabels();
                            } else alert(response.message);
                        });
                });

                fetchLabels(); // Tải danh sách nhãn lần đầu

                // --- API 8 & 9: BẢO MẬT MẬT KHẨU NOTE ---
                document.getElementById('btnSavePassword')?.addEventListener('click', function () {
                    if (!currentEditingNoteId) return alert('Vui lòng lưu nội dung note trước khi đặt mật khẩu!');
                    const pass = document.getElementById('notePassword').value;
                    const confirmPass = document.getElementById('noteConfirmPassword').value;

                    fetch(`/api/notes/${currentEditingNoteId}/password`, {
                        method: 'POST',
                        headers: getAuthHeaders(),
                        body: JSON.stringify({ new_password: pass, confirm_password: confirmPass, action: 'enable' })
                    }).then(res => res.json()).then(response => {
                        if (response.status === 'success') {
                            alert('Khóa bảo mật Note thành công!');
                            document.getElementById('notePassword').value = '';
                            document.getElementById('noteConfirmPassword').value = '';
                            fetchAndRenderNotes();
                        } else alert(response.message || 'Hai mật khẩu không khớp hoặc có lỗi xảy ra.');
                    });
                });

                function unlockNote(noteId, passwordInput, noteObj) {
                    fetch(`/api/notes/${noteId}/unlock`, {
                        method: 'POST',
                        headers: getAuthHeaders(),
                        body: JSON.stringify({ password: passwordInput })
                    }).then(res => res.json()).then(response => {
                        if (response.status === 'success') {
                            noteObj.content = response.data.content;
                            noteObj.is_locked = false; // Tạm tắt cờ lock trên client để cho phép mở Editor
                            openNoteEditor(noteObj);
                        } else alert("Mật khẩu không chính xác!");
                    });
                }

                // --- API 10: CHIA SẺ NOTE ---
                document.getElementById('btnShareNote')?.addEventListener('click', function () {
                    if (!currentEditingNoteId) return alert('Vui lòng gõ nội dung để lưu note trước khi chia sẻ!');
                    const email = prompt("Nhập email người bạn muốn chia sẻ:");
                    if (!email) return;

                    fetch(`/api/notes/${currentEditingNoteId}/share`, {
                        method: 'POST',
                        headers: getAuthHeaders(),
                        body: JSON.stringify({ recipient_email: email, permission: 'edit' })
                    }).then(res => res.json()).then(response => {
                        if (response.status === 'success') alert(`Đã chia sẻ note cho ${email} thành công!`);
                        else alert(response.message || 'Chia sẻ thất bại.');
                    });
                });

            });
        </script>
@endsection