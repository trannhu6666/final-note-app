@extends('layouts.app')

@section('content')
    <style>
        /* --- CSS FOR GRID / LIST VIEW --- */
        .notes-layout-container.grid-mode .note-wrapper {
            flex: 0 0 auto;
            width: 33.333333%;
            /* 3 columns on PC */
        }

        @media (max-width: 991px) {
            .notes-layout-container.grid-mode .note-wrapper {
                width: 50%;
            }

            /* 2 columns on Tablet */
        }

        @media (max-width: 575px) {
            .notes-layout-container.grid-mode .note-wrapper {
                width: 100%;
            }

            /* 1 column on Mobile */
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

        /* --- PINK THEME ENHANCEMENTS FOR NOTE CARDS --- */
        /* Thêm hiệu ứng chuyển động mượt mà cho thẻ note */
        .note-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease !important;
        }

        /* Hiệu ứng nổi bật khi rê chuột vào thẻ Note */
        .note-card:hover {
            transform: translateY(-3px);
            /* Nổi nhẹ lên trên */
            box-shadow: 0 .5rem 1.5rem rgba(232, 62, 140, 0.15) !important;
            /* Bóng đổ màu hồng nhạt */
            border-color: rgba(232, 62, 140, 0.4) !important;
            /* Đổi viền sang màu hồng nhạt */
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
            <div class="list-group shadow-sm">
                <a href="#" class="list-group-item list-group-item-action active border-0"><i class="bi bi-collection"></i>
                    All Notes</a>
                <a href="#"
                    class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-tag"></i> Study</span>
                    <span class="badge bg-secondary rounded-pill">4</span>
                </a>
                <a href="#"
                    class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-tag"></i> Personal</span>
                    <span class="badge bg-secondary rounded-pill">2</span>
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

            <div id="notes-container" class="row g-3 notes-layout-container grid-mode mb-4">

                <div class="col-12 note-wrapper">
                    <div class="card h-100 shadow-sm note-card border-warning" style="cursor: pointer;"
                        data-note-title="Account Password" data-note-content="This note is locked. Password: 123">
                        <div class="card-body">
                            <h5 class="card-title fw-bold d-flex justify-content-between align-items-start">
                                Account Password
                                <div>
                                    <i class="bi bi-lock-fill text-danger me-1"></i>
                                    <i class="bi bi-pin-angle-fill text-warning"></i>
                                </div>
                            </h5>
                            <p class="card-text text-muted">This note is locked...</p>
                            <span class="badge bg-info text-dark mt-2">Personal</span>
                        </div>
                        <div
                            class="card-footer bg-transparent border-top-0 text-muted small d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-clock"></i> 2h ago</span>
                            <button class="btn btn-sm btn-light btn-delete"><i class="bi bi-trash text-danger"></i></button>
                        </div>
                    </div>
                </div>

                <div class="col-12 note-wrapper">
                    <div class="card h-100 shadow-sm note-card" style="cursor: pointer;" data-note-title="Web Project Plan"
                        data-note-content="Need to complete UI/UX and responsiveness before Friday!">
                        <div class="card-body">
                            <h5 class="card-title fw-bold d-flex justify-content-between align-items-start">
                                Web Project Plan
                                <i class="bi bi-people-fill text-primary"></i>
                            </h5>
                            <p class="card-text">Need to complete UI/UX and responsiveness before Friday...</p>
                            <span class="badge bg-success mt-2">Study</span>
                        </div>
                        <div
                            class="card-footer bg-transparent border-top-0 text-muted small d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-clock"></i> Yesterday</span>
                            <button class="btn btn-sm btn-light btn-delete"><i class="bi bi-trash text-danger"></i></button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="editorModal" tabindex="-1" aria-labelledby="editorModalLabel" aria-hidden="true">
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
                    <div class="d-flex gap-2">
                        <label class="btn btn-outline-secondary btn-sm" title="Add Images">
                            <i class="bi bi-image"></i>
                            <input type="file" multiple accept="image/*" class="d-none">
                        </label>
                        <button class="btn btn-outline-danger btn-sm" title="Password Protect" data-bs-toggle="collapse"
                            data-bs-target="#passwordSection">
                            <i class="bi bi-lock"></i>
                        </button>
                        <button class="btn btn-outline-primary btn-sm" title="Add Label">
                            <i class="bi bi-tag"></i>
                        </button>
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
                    <button type="button" class="btn btn-danger px-4">Delete</button>
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
                        <input type="text" class="form-control" placeholder="Create new label">
                        <button class="btn btn-primary" type="button"><i class="bi bi-check"></i></button>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <input type="text" class="form-control border-0 shadow-none bg-transparent" value="Study">
                            <button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i></button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- GRID / LIST VIEW TOGGLE ---
            const gridViewBtn = document.getElementById('gridView');
            const listViewBtn = document.getElementById('listView');
            const notesContainer = document.getElementById('notes-container');

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

            // --- NOTE CARD CLICK EVENTS (EDIT & DELETE) ---
            document.querySelectorAll('.note-card').forEach(card => {
                card.addEventListener('click', function (e) {

                    // 1. If user clicks on the trash button
                    if (e.target.closest('.btn-delete')) {
                        e.stopPropagation(); // Prevent opening the editor modal
                        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
                        deleteModal.show();
                        return;
                    }

                    // 2. If user clicks on the card body (to Edit)
                    const title = this.getAttribute('data-note-title');
                    const content = this.getAttribute('data-note-content');

                    // Populate data into Editor Modal
                    document.getElementById('noteTitle').value = title;
                    document.getElementById('noteContent').value = content;

                    // Show Editor Modal
                    const editorModal = new bootstrap.Modal(document.getElementById('editorModal'));
                    editorModal.show();
                });
            });

            // --- CLEAR EDITOR WHEN CREATING NEW NOTE ---
            const btnCreateNew = document.querySelector('[data-bs-target="#editorModal"]');
            if (btnCreateNew) {
                btnCreateNew.addEventListener('click', function () {
                    document.getElementById('noteTitle').value = '';
                    document.getElementById('noteContent').value = '';
                });
            }
        });

        // AUTO-SAVE & OFFLINE STORAGE
        const noteTitleInput = document.getElementById('noteTitle');
        const noteContentInput = document.getElementById('noteContent');
        const saveStatusIndicator = document.getElementById('saveStatusIndicator');
        let autoSaveTimeout;

        // Hàm kích hoạt khi người dùng gõ phím
        function triggerAutoSave() {
            // 1. Đổi trạng thái thành "Đang lưu..."
            if (saveStatusIndicator) {
                saveStatusIndicator.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span> Saving...';
            }

            // 2. Xóa timeout cũ nếu người dùng vẫn đang gõ (Debounce)
            clearTimeout(autoSaveTimeout);

            // 3. Đặt timeout mới (Đợi người dùng ngừng gõ 1.5 giây thì mới lưu)
            autoSaveTimeout = setTimeout(() => {
                saveNoteData();
            }, 1500);
        }

        // Hàm thực hiện việc lưu dữ liệu
        function saveNoteData() {
            const noteData = {
                // Lưu tạm ID giả (thực tế sẽ lấy ID của Note đang Edit)
                id: Date.now(),
                title: noteTitleInput.value,
                content: noteContentInput.value,
                updated_at: new Date().toISOString(),
                // Nếu có mạng đánh dấu là 1 (đã đồng bộ), mất mạng là 0 (chưa đồng bộ)
                sync_status: navigator.onLine ? 1 : 0
            };

            if (navigator.onLine) {
                // --- TRƯỜNG HỢP ONLINE: Gọi API lưu lên Server ---
                console.log("Online: Đang gọi API gửi lên Server...", noteData);

                // Giả lập API delay 0.5s (Sau này Backend sẽ đưa API Fetch vào đây)
                setTimeout(() => {
                    saveStatusIndicator.innerHTML = '<i class="bi bi-cloud-check text-success"></i> Saved to Cloud';
                }, 500);

            } else {
                // --- TRƯỜNG HỢP OFFLINE: Lưu vào IndexedDB (Tiêu chí 27) ---
                console.log("Offline: Đang lưu cục bộ vào IndexedDB...", noteData);

                if (typeof db !== 'undefined' && db) {
                    const transaction = db.transaction(["offline_notes"], "readwrite");
                    const store = transaction.objectStore("offline_notes");

                    store.put(noteData); // Đẩy dữ liệu vào Local DB

                    transaction.oncomplete = () => {
                        saveStatusIndicator.innerHTML = '<i class="bi bi-hdd-fill text-warning"></i> Saved Locally (Offline)';
                    };

                    transaction.onerror = (e) => {
                        console.error("Lỗi khi lưu IndexedDB", e);
                        saveStatusIndicator.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Save Failed';
                    };
                }
            }
        }

        // Gắn sự kiện lắng nghe thao tác gõ phím của người dùng
        if (noteTitleInput && noteContentInput) {
            noteTitleInput.addEventListener('input', triggerAutoSave);
            noteContentInput.addEventListener('input', triggerAutoSave);
        }
        // 8. REALTIME COLLABORATION (CRITERIA 24)

        // Note: In a real project, these values come from your Backend (Pusher/Socket settings)
        const ECHO_INSTANCE = new Echo({
            broadcaster: 'pusher',
            key: 'your-pusher-key', // Replace with real key from Backend
            cluster: 'mt1',
            forceTLS: true
        });

        let currentNoteId = null; // Track which note is currently open in Editor

        // Function to join a Realtime Room for a specific note
        function joinNoteChannel(noteId) {
            currentNoteId = noteId;

            // Join a private channel for this specific note
            ECHO_INSTANCE.private(`note.${noteId}`)
                .listen('.NoteUpdated', (data) => {
                    console.log("Realtime Update Received:", data);

                    // Only update the Editor content if the user isn't currently typing to avoid cursor jumping
                    if (document.activeElement !== noteContentInput && document.activeElement !== noteTitleInput) {
                        if (data.title) noteTitleInput.value = data.title;
                        if (data.content) noteContentInput.value = data.content;

                        // Flash the status to inform user
                        saveStatusIndicator.innerHTML = '<i class="bi bi-person-check-fill text-info"></i> Updated by collaborator';
                        setTimeout(() => {
                            saveStatusIndicator.innerHTML = '<i class="bi bi-cloud-check text-success"></i> Saved';
                        }, 2000);
                    }
                })
                .whisper('typing', {
                    user: 'Another User', // This would be the actual collaborator's name
                    isTyping: true
                });
        }

        // Update the triggerAutoSave function we wrote earlier
        // to also "whisper" changes to others via WebSocket
        function broadcastTyping() {
            if (currentNoteId) {
                ECHO_INSTANCE.private(`note.${currentNoteId}`)
                    .whisper('typing', {
                        title: noteTitleInput.value,
                        content: noteContentInput.value
                    });
            }
        }

        // Attach the broadcast logic to the inputs
        noteTitleInput.addEventListener('input', broadcastTyping);
        noteContentInput.addEventListener('input', broadcastTyping);
    </script>
@endsection