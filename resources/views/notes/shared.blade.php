@extends('layouts.app')

@section('content')
    <div class="container mb-5">
        <h3 class="fw-bold mb-4 text-primary">
            <i class="bi bi-people-fill me-2"></i>Shared with me
        </h3>

        <div class="row g-4">
            <div class="col-md-4 note-wrapper">
                <div class="card h-100 shadow-sm border-0 note-card">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Tài liệu tham khảo</h5>
                        <p class="card-text text-muted">File PDF hướng dẫn làm đồ án...</p>
                    </div>
                    <div class="card-footer bg-transparent d-flex flex-column small border-top-0 pt-0">
                        <span class="text-muted mb-2">
                            <i class="bi bi-person-circle text-secondary me-1"></i> Shared by: teacher@tdtu.edu.vn
                        </span>
                        <span
                            class="badge bg-secondary text-white w-auto align-self-start px-3 py-2 rounded-pill shadow-sm">
                            <i class="bi bi-eye me-1"></i> Read-only
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-4 note-wrapper">
                <div class="card h-100 shadow-sm border-primary note-card editable-note" style="cursor: pointer;"
                    data-note-id="shared_123" data-note-title="Brainstorming UX/UI"
                    data-note-content="Các ý tưởng thiết kế giao diện...">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Brainstorming UX/UI</h5>
                        <p class="card-text">Các ý tưởng thiết kế giao diện...</p>
                    </div>
                    <div class="card-footer bg-transparent d-flex flex-column small border-top-0 pt-0">
                        <span class="text-muted mb-2">
                            <i class="bi bi-person-circle text-primary me-1"></i> Shared by: teammate@tdtu.edu.vn
                        </span>
                        <span class="badge bg-primary text-white w-auto align-self-start px-3 py-2 rounded-pill shadow-sm">
                            <i class="bi bi-pencil-square me-1"></i> Can Edit
                        </span>
                    </div>
                </div>
            </div>
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
        document.addEventListener('DOMContentLoaded', function () {

            // 1. Xử lý khi click vào ghi chú được phép Edit
            document.querySelectorAll('.editable-note').forEach(card => {
                card.addEventListener('click', function () {
                    const title = this.getAttribute('data-note-title');
                    const content = this.getAttribute('data-note-content');
                    const noteId = this.getAttribute('data-note-id');

                    // Đổ dữ liệu vào Modal
                    document.getElementById('noteTitle').value = title;
                    document.getElementById('noteContent').value = content;

                    // Mở Modal
                    const editorModal = new bootstrap.Modal(document.getElementById('editorModal'));
                    editorModal.show();

                    // Tham gia phòng WebSocket cho Note này (Realtime)
                    if (typeof joinNoteChannel === 'function') {
                        joinNoteChannel(noteId);
                    }
                });
            });

            // 2. Logic Auto-Save (Tiêu chí 14)
            const noteTitleInput = document.getElementById('noteTitle');
            const noteContentInput = document.getElementById('noteContent');
            const saveStatusIndicator = document.getElementById('saveStatusIndicator');
            let autoSaveTimeout;

            function triggerAutoSave() {
                if (saveStatusIndicator) {
                    saveStatusIndicator.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span> Saving...';
                }

                clearTimeout(autoSaveTimeout);

                autoSaveTimeout = setTimeout(() => {
                    if (navigator.onLine) {
                        // Giả lập lưu lên Server
                        setTimeout(() => {
                            saveStatusIndicator.innerHTML = '<i class="bi bi-cloud-check text-success"></i> Saved to Cloud';
                        }, 500);
                    }
                }, 1500);

                // Gửi tín hiệu Realtime cho người khác thấy mình đang gõ
                if (typeof broadcastTyping === 'function') {
                    broadcastTyping();
                }
            }

            if (noteTitleInput && noteContentInput) {
                noteTitleInput.addEventListener('input', triggerAutoSave);
                noteContentInput.addEventListener('input', triggerAutoSave);
            }

            // 3. Khung Logic Realtime WebSocket (Tiêu chí 24)
            let currentNoteId = null;
            window.joinNoteChannel = function (noteId) {
                currentNoteId = noteId;
                // Nếu đã cài đặt Laravel Echo, code bắt sự kiện Realtime sẽ nằm ở đây
                console.log(`Đã tham gia phòng chỉnh sửa Realtime cho Note: ${noteId}`);
            }

            window.broadcastTyping = function () {
                if (currentNoteId) {
                    // Giả lập gửi tín hiệu WebSocket
                    console.log(`Đang gửi dữ liệu Realtime... Title: ${noteTitleInput.value}`);
                }
            }
        });
    </script>
@endsection