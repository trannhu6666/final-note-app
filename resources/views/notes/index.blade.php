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
                <a href="#" class="list-group-item list-group-item-action active border-0 label-filter-item"
                    data-label="all">
                    <i class="bi bi-collection"></i> All Notes
                </a>
                <a href="#"
                    class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center label-filter-item"
                    data-label="Study">
                    <span><i class="bi bi-tag"></i> Study</span>
                    <span class="badge bg-secondary rounded-pill">2</span>
                </a>
                <a href="#"
                    class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center label-filter-item"
                    data-label="Personal">
                    <span><i class="bi bi-tag"></i> Personal</span>
                    <span class="badge bg-secondary rounded-pill">1</span>
                </a>
            </div>
        </div>

        <div class="col-md-9">
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
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false" title="Add Label">
                                <i class="bi bi-tag"></i>
                            </button>
                            <ul class="dropdown-menu shadow pb-1">
                                <li>
                                    <h6 class="dropdown-header">Assign Label</h6>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="#">
                                        <input class="form-check-input me-2 mt-0" type="checkbox" value="" id="labelStudy">
                                        <label class="form-check-label w-100" for="labelStudy">Study</label>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="#">
                                        <input class="form-check-input me-2 mt-0" type="checkbox" value=""
                                            id="labelPersonal">
                                        <label class="form-check-label w-100" for="labelPersonal">Personal</label>
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item text-primary small fw-bold"
                                        data-bs-toggle="modal" data-bs-target="#labelModal">
                                        <i class="bi bi-plus-circle me-1"></i> Create new label
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <span id="saveStatusIndicator" class="text-muted small"><i class="bi bi-cloud-check"></i> Saved</span>
                </div>

                <div class="collapse bg-white border-top p-3" id="passwordSection">
                    <h6 class="text-danger"><i class="bi bi-shield-lock"></i> Note Password Protection</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="password" class="form-control form-control-sm" placeholder="Set Password">
                        </div>
                        <div class="col-md-6">
                            <input type="password" class="form-control form-control-sm" placeholder="Confirm Password">
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
                    <p class="text-muted small">Are you sure you want to delete this note? This action cannot be undone.</p>
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
                        <button class="btn btn-primary" type="button" id="btnAddLabel"><i class="bi bi-check"></i></button>
                    </div>
                    <ul class="list-group list-group-flush" id="labelListContainer">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <input type="text" class="form-control border-0 shadow-none bg-transparent" value="Study">
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete-label"><i
                                    class="bi bi-trash"></i></button>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <input type="text" class="form-control border-0 shadow-none bg-transparent" value="Personal">
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete-label"><i
                                    class="bi bi-trash"></i></button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- DỮ LIỆU GIẢ (MOCK DATA) ĐỂ TEST GIAO DIỆN ---
        const mockNotes = [
            { id: 1, title: "Account Password", content: "This note is locked. Password: 123", label: "Personal", labelColor: "info", time: "2h ago", isLocked: false, isPinned: false, isShared: false },
            { id: 2, title: "Web Project Plan", content: "Need to complete UI/UX and responsiveness before Friday!", label: "Study", labelColor: "success", time: "Yesterday", isLocked: false, isPinned: false, isShared: false },
            { id: 3, title: "Học PHP & Laravel", content: "Chuẩn bị làm API cho project", label: "Study", labelColor: "success", time: "3 days ago", isLocked: false, isPinned: false, isShared: false }
        ];

        // Các biến toàn cục để theo dõi trạng thái
        let noteElementToDelete = null;
        let currentEditingNoteId = null; // Theo dõi ID của note đang được mở trong Modal
        window.isNotePinned = false; // Theo dõi trạng thái ghim

        document.addEventListener('DOMContentLoaded', function () {
            const notesContainer = document.getElementById('notes-container');
            const searchBox = document.getElementById('search-box');
            let searchTimeout;

            // --- 1. RENDER VÀ SẮP XẾP DỮ LIỆU ---
            function renderNotes(notes) {
                if (!notesContainer) return;

                // Sắp xếp các note được Ghim (isPinned = true) lên đầu
                const sortedNotes = [...notes].sort((a, b) => (b.isPinned === true) - (a.isPinned === true));

                if (sortedNotes.length === 0) {
                    notesContainer.innerHTML = `
                                    <div class="col-12 text-center text-muted py-5">
                                        <i class="bi bi-search" style="font-size: 3rem;"></i>
                                        <p class="mt-3">No notes found!</p>
                                    </div>`;
                    return;
                }

                let html = '';
                sortedNotes.forEach(note => {
                    let iconsHtml = '';
                    if (note.isLocked) iconsHtml += '<i class="bi bi-lock-fill text-danger me-1"></i>';
                    if (note.isPinned) iconsHtml += '<i class="bi bi-pin-angle-fill text-warning me-1"></i>';
                    if (note.isShared) iconsHtml += '<i class="bi bi-people-fill text-primary"></i>';

                    let labelHtml = note.label ? `<span class="badge bg-${note.labelColor} text-dark mt-2">${note.label}</span>` : '';
                    let borderClass = note.isLocked ? 'border-warning' : '';
                    let displayContent = note.isLocked ? 'This note is locked...' : note.content;

                    html += `
                                    <div class="col-12 note-wrapper">
                                        <div class="card h-100 shadow-sm note-card ${borderClass}" style="cursor: pointer;"
                                            data-note-id="${note.id}"
                                            data-note-title="${note.title}" 
                                            data-note-content="${note.content}"
                                            data-note-pinned="${note.isPinned}">
                                            <div class="card-body">
                                                <h5 class="card-title fw-bold d-flex justify-content-between align-items-start">
                                                    ${note.title}
                                                    <div>${iconsHtml}</div>
                                                </h5>
                                                <p class="card-text ${note.isLocked ? 'text-muted' : ''}">${displayContent}</p>
                                                ${labelHtml}
                                            </div>
                                            <div class="card-footer bg-transparent border-top-0 text-muted small d-flex justify-content-between align-items-center">
                                                <span><i class="bi bi-clock"></i> ${note.time}</span>
                                                <button class="btn btn-sm btn-light btn-delete"><i class="bi bi-trash text-danger"></i></button>
                                            </div>
                                        </div>
                                    </div>`;
                });

                notesContainer.innerHTML = html;
                attachNoteCardEvents();
            }

            // --- 2. GẮN SỰ KIỆN CLICK CHO NOTE ---
            function attachNoteCardEvents() {
                document.querySelectorAll('.note-card').forEach(card => {
                    card.addEventListener('click', function (e) {
                        if (e.target.closest('.btn-delete')) {
                            e.stopPropagation();
                            noteElementToDelete = this.closest('.note-wrapper');
                            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
                            deleteModal.show();
                            return;
                        }

                        currentEditingNoteId = parseInt(this.getAttribute('data-note-id'));
                        const title = this.getAttribute('data-note-title');
                        const content = this.getAttribute('data-note-content');

                        document.getElementById('noteTitle').value = title;
                        document.getElementById('noteContent').value = content;

                        isNotePinned = this.getAttribute('data-note-pinned') === 'true';
                        const btnTogglePin = document.getElementById('btnTogglePin');
                        if (btnTogglePin) {
                            if (isNotePinned) {
                                btnTogglePin.classList.remove('btn-outline-warning');
                                btnTogglePin.classList.add('btn-warning', 'text-dark');
                            } else {
                                btnTogglePin.classList.remove('btn-warning', 'text-dark');
                                btnTogglePin.classList.add('btn-outline-warning');
                            }
                        }

                        const editorModal = new bootstrap.Modal(document.getElementById('editorModal'));
                        editorModal.show();
                    });
                });
            }

            renderNotes(mockNotes);

            // --- 3. LIVE SEARCH ---
            if (searchBox) {
                searchBox.addEventListener('input', function () {
                    clearTimeout(searchTimeout);
                    const keyword = this.value.trim().toLowerCase();

                    searchTimeout = setTimeout(() => {
                        const filteredNotes = mockNotes.filter(note =>
                            note.title.toLowerCase().includes(keyword) ||
                            note.content.toLowerCase().includes(keyword)
                        );
                        renderNotes(filteredNotes);
                    }, 300);
                });
            }

            // --- 4. LỌC THEO LABEL ---
            const labelFilterItems = document.querySelectorAll('.label-filter-item');
            if (labelFilterItems.length > 0) {
                labelFilterItems.forEach(item => {
                    item.addEventListener('click', function (e) {
                        e.preventDefault();
                        labelFilterItems.forEach(el => el.classList.remove('active'));
                        this.classList.add('active');

                        const selectedLabel = this.getAttribute('data-label');
                        let filteredNotes = selectedLabel === 'all'
                            ? mockNotes
                            : mockNotes.filter(note => note.label === selectedLabel);

                        renderNotes(filteredNotes);
                    });
                });
            }

            // --- 5. LOGIC XÓA NOTE KHI BẤM NÚT XÁC NHẬN ---
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function () {
                    if (noteElementToDelete) {
                        noteElementToDelete.remove();
                        const deleteModalEl = document.getElementById('deleteConfirmModal');
                        const deleteModal = bootstrap.Modal.getInstance(deleteModalEl) || new bootstrap.Modal(deleteModalEl);
                        deleteModal.hide();
                        noteElementToDelete = null;
                    }
                });
            }

            // --- 6. GRID / LIST VIEW TOGGLE ---
            const gridViewBtn = document.getElementById('gridView');
            const listViewBtn = document.getElementById('listView');

            if (gridViewBtn && listViewBtn && notesContainer) {
                gridViewBtn.addEventListener('change', () => {
                    if (gridViewBtn.checked) {
                        notesContainer.classList.remove('list-mode');
                        notesContainer.classList.add('grid-mode');
                    }
                });

                listViewBtn.addEventListener('change', () => {
                    if (listViewBtn.checked) {
                        notesContainer.classList.remove('grid-mode');
                        notesContainer.classList.add('list-mode');
                    }
                });
            }

            // --- 7. QUẢN LÝ GHIM (PIN NOTE) ---
            const btnTogglePin = document.getElementById('btnTogglePin');
            if (btnTogglePin) {
                btnTogglePin.addEventListener('click', function () {
                    isNotePinned = !isNotePinned;

                    if (isNotePinned) {
                        this.classList.remove('btn-outline-warning');
                        this.classList.add('btn-warning', 'text-dark');
                    } else {
                        this.classList.remove('btn-warning', 'text-dark');
                        this.classList.add('btn-outline-warning');
                    }
                    this.blur();

                    if (currentEditingNoteId) {
                        const noteIndex = mockNotes.findIndex(n => n.id === currentEditingNoteId);
                        if (noteIndex !== -1) {
                            mockNotes[noteIndex].isPinned = isNotePinned;
                            renderNotes(mockNotes);
                        }
                    }
                });
            }

            // --- 8. PREVIEW ẢNH ĐÍNH KÈM ---
            const imageInput = document.getElementById('noteImageInput');
            const previewArea = document.getElementById('image-preview-area');

            if (imageInput && previewArea) {
                imageInput.addEventListener('change', function (e) {
                    const files = e.target.files;
                    if (files.length > 0) previewArea.classList.remove('d-none');

                    Array.from(files).forEach(file => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function (event) {
                                const imgContainer = document.createElement('div');
                                imgContainer.className = 'position-relative d-inline-block';
                                imgContainer.innerHTML = `
                                                <img src="${event.target.result}" class="img-thumbnail shadow-sm border-0" style="height: 70px; width: auto; border-radius: 8px; object-fit: cover;">
                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 start-100 translate-middle rounded-circle p-0" style="width: 20px; height: 20px; line-height: 1;" onclick="this.parentElement.remove()">
                                                    &times;
                                                </button>
                                            `;
                                previewArea.appendChild(imgContainer);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                    this.value = '';
                });
            }

            // --- 9. QUẢN LÝ NHÃN (ADD/DELETE) TRONG MODAL ---
            const btnAddLabel = document.getElementById('btnAddLabel');
            const newLabelInput = document.getElementById('newLabelInput');
            const labelListContainer = document.getElementById('labelListContainer');

            if (btnAddLabel && newLabelInput && labelListContainer) {
                btnAddLabel.addEventListener('click', function () {
                    const labelName = newLabelInput.value.trim();
                    if (labelName === '') return;

                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center px-0';
                    li.innerHTML = `
                                    <input type="text" class="form-control border-0 shadow-none bg-transparent" value="${labelName}">
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete-label"><i class="bi bi-trash"></i></button>
                                `;

                    labelListContainer.appendChild(li);
                    newLabelInput.value = '';
                    attachDeleteLabelEvent(li.querySelector('.btn-delete-label'));
                });

                function attachDeleteLabelEvent(button) {
                    button.addEventListener('click', function () {
                        this.closest('li').remove();
                    });
                }

                document.querySelectorAll('.btn-delete-label').forEach(btn => {
                    attachDeleteLabelEvent(btn);
                });
            }

            // --- 10. CLEAR EDITOR KHI TẠO NOTE MỚI ---
            const btnCreateNew = document.querySelector('[data-bs-target="#editorModal"]');
            if (btnCreateNew) {
                btnCreateNew.addEventListener('click', function () {
                    currentEditingNoteId = null;
                    document.getElementById('noteTitle').value = '';
                    document.getElementById('noteContent').value = '';

                    isNotePinned = false;
                    if (btnTogglePin) {
                        btnTogglePin.classList.remove('btn-warning', 'text-dark');
                        btnTogglePin.classList.add('btn-outline-warning');
                    }

                    if (previewArea) {
                        previewArea.innerHTML = '';
                        previewArea.classList.add('d-none');
                    }

                    // Clear password fields
                    const passInput = document.getElementById('notePassword');
                    const confirmInput = document.getElementById('noteConfirmPassword');
                    if (passInput) passInput.value = '';
                    if (confirmInput) confirmInput.value = '';
                });
            }
        });

        // --- 11. AUTO-SAVE LOGIC ---
        const noteTitleInput = document.getElementById('noteTitle');
        const noteContentInput = document.getElementById('noteContent');
        const notePasswordInput = document.getElementById('notePassword');
        const noteConfirmInput = document.getElementById('noteConfirmPassword');
        const passwordError = document.getElementById('passwordError');
        const saveStatusIndicator = document.getElementById('saveStatusIndicator');
        let autoSaveTimeout;

        function triggerAutoSave() {
            if (saveStatusIndicator) {
                saveStatusIndicator.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span> Saving...';
            }
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                saveNoteData();
            }, 1500);
        }

        function saveNoteData() {
            let pass = notePasswordInput ? notePasswordInput.value : '';
            let confirmPass = noteConfirmInput ? noteConfirmInput.value : '';
            let isNoteLocked = false;

            // Kiểm tra mật khẩu nếu người dùng có nhập
            if (pass !== '' || confirmPass !== '') {
                if (pass !== confirmPass) {
                    if (passwordError) passwordError.classList.remove('d-none');
                    if (saveStatusIndicator) saveStatusIndicator.innerHTML = '<i class="bi bi-x-circle text-danger"></i> Save failed';
                    return;
                } else {
                    if (passwordError) passwordError.classList.add('d-none');
                    isNoteLocked = true;
                }
            } else {
                if (passwordError) passwordError.classList.add('d-none');
            }

            const noteData = {
                id: currentEditingNoteId || Date.now(),
                title: noteTitleInput.value,
                content: noteContentInput.value,
                isPinned: isNotePinned,
                isLocked: isNoteLocked,
                password: pass, // Gửi pass text cho Dev B mã hóa
                updated_at: new Date().toISOString(),
                sync_status: navigator.onLine ? 1 : 0
            };

            if (navigator.onLine) {
                console.log("Saving Note Data:", noteData);
                setTimeout(() => {
                    if (saveStatusIndicator) saveStatusIndicator.innerHTML = '<i class="bi bi-cloud-check text-success"></i> Saved to Cloud';
                }, 500);
            } else {
                if (typeof db !== 'undefined' && db) {
                    const transaction = db.transaction(["offline_notes"], "readwrite");
                    const store = transaction.objectStore("offline_notes");
                    store.put(noteData);
                    transaction.oncomplete = () => {
                        if (saveStatusIndicator) saveStatusIndicator.innerHTML = '<i class="bi bi-hdd-fill text-warning"></i> Saved Locally (Offline)';
                    };
                }
            }
        }

        if (noteTitleInput && noteContentInput) {
            noteTitleInput.addEventListener('input', triggerAutoSave);
            noteContentInput.addEventListener('input', triggerAutoSave);
        }

        if (notePasswordInput && noteConfirmInput) {
            notePasswordInput.addEventListener('input', triggerAutoSave);
            noteConfirmInput.addEventListener('input', triggerAutoSave);
        }

        // --- 12. REALTIME COLLABORATION ---
        const ECHO_INSTANCE = new Echo({
            broadcaster: 'pusher',
            key: 'your-pusher-key',
            cluster: 'mt1',
            forceTLS: true
        });

        let currentRealtimeNoteId = null;

        function joinNoteChannel(noteId) {
            currentRealtimeNoteId = noteId;
            ECHO_INSTANCE.private(`note.${noteId}`)
                .listen('.NoteUpdated', (data) => {
                    if (document.activeElement !== noteContentInput && document.activeElement !== noteTitleInput) {
                        if (data.title) noteTitleInput.value = data.title;
                        if (data.content) noteContentInput.value = data.content;
                        if (saveStatusIndicator) saveStatusIndicator.innerHTML = '<i class="bi bi-person-check-fill text-info"></i> Updated by collaborator';
                        setTimeout(() => {
                            if (saveStatusIndicator) saveStatusIndicator.innerHTML = '<i class="bi bi-cloud-check text-success"></i> Saved';
                        }, 2000);
                    }
                })
                .whisper('typing', {
                    user: 'Another User',
                    isTyping: true
                });
        }

        function broadcastTyping() {
            if (currentRealtimeNoteId) {
                ECHO_INSTANCE.private(`note.${currentRealtimeNoteId}`)
                    .whisper('typing', {
                        title: noteTitleInput.value,
                        content: noteContentInput.value
                    });
            }
        }

        if (noteTitleInput && noteContentInput) {
            noteTitleInput.addEventListener('input', broadcastTyping);
            noteContentInput.addEventListener('input', broadcastTyping);
        }
    </script>
@endsection