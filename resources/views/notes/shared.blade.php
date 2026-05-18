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
                    <div id="image-preview-area" class="d-flex flex-wrap gap-2 mt-3 d-none"></div>
                </div>

                <div class="modal-footer bg-body-tertiary d-flex justify-content-between border-top-0">
                    <div class="d-flex gap-2">
                        <label class="btn btn-outline-secondary btn-sm mb-0" title="Add Images">
                            <i class="bi bi-image"></i>
                            <input type="file" id="noteImageInput" multiple accept="image/*" class="d-none">
                        </label>
                    </div>
                    <span id="saveStatusIndicator" class="text-muted small"><i class="bi bi-cloud-check"></i> Saved</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        let db = null;
        const request = indexedDB.open("MyNotesOfflineDB", 2);

        request.onupgradeneeded = function (e) {
            let localDb = e.target.result;
            if (!localDb.objectStoreNames.contains("cached_notes")) {
                localDb.createObjectStore("cached_notes", { keyPath: "id" });
            }
            if (!localDb.objectStoreNames.contains("pending_sync")) {
                localDb.createObjectStore("pending_sync", { keyPath: "local_id", autoIncrement: true });
            }
        };

        request.onsuccess = function (e) {
            db = e.target.result;
        };

        // Function to get common Auth Headers
        function getAuthHeaders(isFormData = false) {
            const token = localStorage.getItem('user_token');
            const headers = {};
            if (!isFormData) headers['Content-Type'] = 'application/json';
            headers['Accept'] = 'application/json';
            if (token) headers['Authorization'] = `Bearer ${token}`;
            return headers;
        }

        let currentEditingNoteId = null;

        document.addEventListener('DOMContentLoaded', function () {
            if (!localStorage.getItem('user_token')) {
                window.location.href = '/login';
                return;
            }
            const container = document.getElementById('shared-notes-container');
            const noteImageInput = document.getElementById('noteImageInput');
            const imagePreviewArea = document.getElementById('image-preview-area');
            const saveStatusIndicator = document.getElementById('saveStatusIndicator');
            const noteTitleInput = document.getElementById('noteTitle');
            const noteContentInput = document.getElementById('noteContent');

            // --- 🌟 HANDLE IMAGE PREVIEW WHEN SHARED USER SELECTS A FILE ---
            noteImageInput?.addEventListener('change', function () {
                if (!imagePreviewArea) return;
                imagePreviewArea.innerHTML = '';

                if (this.files.length > 0) {
                    imagePreviewArea.classList.remove('d-none');
                    Array.from(this.files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'img-thumbnail';
                            img.style.width = '75px';
                            img.style.height = '75px';
                            img.style.objectFit = 'cover';
                            imagePreviewArea.appendChild(img);
                        }
                        reader.readAsDataURL(file);
                    });
                    triggerAutoSave();
                } else {
                    imagePreviewArea.classList.add('d-none');
                }
            });

            // --- 1. CALL API TO FETCH SHARED NOTES LIST ---
            function fetchSharedNotes() {
                fetch('/api/notes?type=shared', {
                    method: 'GET',
                    headers: getAuthHeaders()
                })
                    .then(async res => {
                        const contentType = res.headers.get("content-type");
                        if (!contentType || !contentType.includes("application/json")) {
                            throw new Error("API /api/notes/shared is returning a 500 error or is not yet implemented!");
                        }
                        return res.json();
                    })
                    .then(response => {
                        if (response.status === 'success') {
                            renderSharedNotes(response.data || []);
                        } else {
                            alert(response.message || "Failed to fetch list!");
                        }
                    })
                    .catch(err => {
                        console.error("Error fetching shared notes:", err);
                        container.innerHTML = `<div class="col-12 text-center text-danger py-5 border border-danger rounded bg-body-tertiary mt-3">
                                                            <h5><i class="bi bi-bug"></i> Backend API Error</h5><p>${err.message}</p>
                                                        </div>`;
                    });
            }
            // 🌟 EXPOSE FUNCTION TO WINDOW so external delete image function can call to refresh public data
            window.fetchSharedNotes = fetchSharedNotes;

            // --- 2. RENDER DATA TO UI ---
            function renderSharedNotes(notes) {
                container.innerHTML = '';

                if (notes.length === 0) {
                    container.innerHTML = `<div class="col-12 text-center text-muted py-5"><i class="bi bi-folder-x" style="font-size: 3rem;"></i><p class="mt-3">No shared notes found!</p></div>`;
                    return;
                }

                notes.forEach(note => {
                    const isEdit = (note.permission === 'edit');
                    const badgeClass = isEdit ? 'bg-primary' : 'bg-secondary';
                    const badgeIcon = isEdit ? 'bi-pencil-square' : 'bi-eye';
                    const badgeText = isEdit ? 'Can Edit' : 'Read-only';
                    const cursorStyle = isEdit ? 'cursor: pointer;' : 'cursor: default;';
                    const borderClass = isEdit ? 'border-primary editable-note shadow-sm' : 'border-0 shadow-sm';
                    const contentSnippet = note.content || '...';

                    let imagesThumbnailHtml = '';
                    if (note.images && note.images.length > 0) {
                        imagesThumbnailHtml += '<div class="d-flex gap-1 mt-2 flex-wrap">';
                        note.images.forEach(img => {
                            imagesThumbnailHtml += `<img src="${img.image_url}" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">`;
                        });
                        imagesThumbnailHtml += '</div>';
                    }

                    const col = document.createElement('div');
                    col.className = 'col-md-4 note-wrapper';
                    col.innerHTML = `
                                    <div class="card h-100 note-card ${borderClass}" style="${cursorStyle}">
                                        <div class="card-body">
                                            <h5 class="card-title fw-bold">${note.title || 'Untitled'}</h5>
                                            <p class="card-text text-muted" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">${contentSnippet}</p>
                                            ${imagesThumbnailHtml}
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
                                    </div>`;

                    if (isEdit) {
                        col.querySelector('.note-card').addEventListener('click', () => {
                            if (note.is_locked) {
                                const pass = prompt("This note is password protected by the Owner. Please enter the password:");
                                if (pass) unlockSharedNote(note.id, pass, note, isEdit);
                            } else {
                                openSharedNoteEditor(note, isEdit);
                            }
                        });
                    }

                    container.appendChild(col);
                });
            }

            fetchSharedNotes();

            // --- 3. MODAL OPEN LOGIC AND EDITOR PERMISSIONS ---
            function openSharedNoteEditor(note, isEdit) {
                currentEditingNoteId = note.id;

                noteTitleInput.value = note.title || '';
                noteContentInput.value = note.content || '';

                noteTitleInput.readOnly = !isEdit;
                noteContentInput.readOnly = !isEdit;

                const modalFooter = document.querySelector('#editorModal .modal-footer');
                if (!isEdit) {
                    if (modalFooter) modalFooter.style.display = 'none';
                } else {
                    if (modalFooter) modalFooter.style.display = 'flex';
                }

                renderSharedModalImages(note.images, isEdit);

                if (noteImageInput) noteImageInput.value = '';

                new bootstrap.Modal(document.getElementById('editorModal')).show();

                if (isEdit) joinNoteRealtimeChannel(note.id);
            }

            function unlockSharedNote(noteId, passwordInput, noteObj, isEdit) {
                fetch(`/api/notes/${noteId}/unlock`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({ password: passwordInput })
                }).then(res => res.json()).then(response => {
                    if (response.status === 'success') {
                        noteObj.content = response.data.content;
                        noteObj.is_locked = false;
                        openSharedNoteEditor(noteObj, isEdit);
                    } else {
                        alert("Incorrect password!");
                    }
                });
            }

            // --- 4. AUTO-SAVE TO BACKEND USING FORMDATA ---
            let autoSaveTimeout;
            function triggerAutoSave() {
                if (!currentEditingNoteId) return;

                if (saveStatusIndicator) {
                    saveStatusIndicator.innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span> Saving...';
                }

                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(() => {
                    const formData = new FormData();
                    formData.append('title', noteTitleInput.value);
                    formData.append('content', noteContentInput.value);
                    formData.append('_method', 'PUT');

                    if (noteImageInput && noteImageInput.files.length > 0) {
                        for (let i = 0; i < noteImageInput.files.length; i++) {
                            formData.append('images[]', noteImageInput.files[i]);
                        }
                    }

                    fetch(`/api/notes/${currentEditingNoteId}`, {
                        method: 'POST',
                        headers: getAuthHeaders(true),
                        body: formData
                    })
                        .then(res => res.json())
                        .then(response => {
                            if (response.status === 'success') {
                                // 🌟 FIXED CRITICAL BUG: Changed from 'indicator' to 'saveStatusIndicator' to prevent JS crash
                                if (saveStatusIndicator) {
                                    saveStatusIndicator.innerHTML = '<i class="bi bi-cloud-check text-success"></i> Saved to Cloud';
                                }

                                if (noteImageInput) noteImageInput.value = '';

                                if (response.data && response.data.images) {
                                    renderSharedModalImages(response.data.images, true);
                                }

                                fetchSharedNotes();
                            }
                        })
                        .catch(err => console.error(err));
                }, 1500);

                broadcastTypingStatus();
            }

            if (noteTitleInput && noteContentInput) {
                noteTitleInput.addEventListener('input', triggerAutoSave);
                noteContentInput.addEventListener('input', triggerAutoSave);
            }

            // --- 5. WEBSOCKET REALTIME LOGIC ---
            let socket = null;
            function joinNoteRealtimeChannel(noteId) {
                socket = new WebSocket('ws://localhost:8080');
                socket.onopen = () => console.log(`🟢 WebSocket connected for Shared Note: ${noteId}`);

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

            // --- 6. LIVE SEARCH ---
            const searchBox = document.getElementById('search-box');
            let searchTimeoutShared;

            if (searchBox) {
                searchBox.addEventListener('input', function () {
                    clearTimeout(searchTimeoutShared);
                    const keyword = this.value.trim().toLowerCase();

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

        // --- INDEPENDENT FUNCTIONS OUTSIDE DOM SCOPE ---
        function renderSharedModalImages(images, isEditMode) {
            const imagePreviewArea = document.getElementById('image-preview-area');
            if (!imagePreviewArea) return;
            imagePreviewArea.innerHTML = '';

            if (images && images.length > 0) {
                imagePreviewArea.classList.remove('d-none');
                images.forEach(imgObj => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'position-relative d-inline-block me-2 mb-2';

                    let deleteBtnHtml = '';
                    if (isEditMode) {
                        deleteBtnHtml = `
                                    <button type="button" class="btn btn-danger p-0 d-flex align-items-center justify-content-center rounded-circle position-absolute top-0 end-0" 
                                        style="width: 20px; height: 20px; transform: translate(30%, -30%); font-size: 0.75rem; z-index: 10;" 
                                        onclick="window.deleteSharedNoteImage(${imgObj.id}, this)">
                                        <i class="bi bi-x"></i>
                                    </button>`;
                    }

                    wrapper.innerHTML = `
                                <img src="${imgObj.image_url}" class="img-thumbnail" style="width: 75px; height: 75px; object-fit: cover;">
                                ${deleteBtnHtml}
                            `;
                    imagePreviewArea.appendChild(wrapper);
                });
            } else {
                imagePreviewArea.classList.add('d-none');
            }
        }

        window.deleteSharedNoteImage = function (imageId, btnElement) {
            if (!confirm('Are you sure you want to delete this image from the note?')) return;

            fetch(`/api/notes/images/${imageId}`, {
                method: 'DELETE',
                headers: getAuthHeaders()
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success') {
                        btnElement.closest('.position-relative').remove();
                        if (typeof window.fetchSharedNotes === 'function') {
                            window.fetchSharedNotes();
                        }
                    } else {
                        alert(response.message || 'Error occurred while deleting the image.');
                    }
                })
                .catch(err => console.error("Error deleting image on shared page:", err));
        };
    </script>
@endsection