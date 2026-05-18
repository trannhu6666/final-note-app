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

        /* Adjust color for left menu list (Active Item) */
        .list-group-item.active {
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
        }

        .border-bottom-dashed {
            border-bottom: 1px dashed #dee2e6;
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
            <div class="alert alert-danger mb-3 d-none shadow-sm animate__animated animate__fadeIn" id="offlineStatusAlert"
                role="alert">
                <i class="bi bi-wifi-off me-2"></i> <strong>Working offline!</strong> All your changes will be temporarily
                saved on this device.
            </div>

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

                    <div class="modal-footer bg-body-tertiary d-flex justify-content-between border-top-0">
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

                    <div class="collapse bg-body border-top p-3" id="passwordSection">
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
                                <button type="button" class="btn btn-danger btn-sm w-100" id="btnSavePassword">Lock</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="shareManagerModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-body-tertiary">
                        <h5 class="modal-title fw-bold"><i class="bi bi-people-fill text-primary me-2"></i> Manage Sharing
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group mb-4 shadow-sm">
                            <input type="email" id="shareEmailInput" class="form-control"
                                placeholder="Enter teammate's email...">
                            <select id="sharePermissionSelect" class="form-select" style="max-width: 110px;">
                                <option value="view">View</option>
                                <option value="edit">Edit</option>
                            </select>
                            <button class="btn btn-primary fw-bold" id="btnSubmitShare">Share</button>
                        </div>
                        <h6 class="fw-bold text-muted mb-3 border-bottom pb-2">People with access:</h6>
                        <ul class="list-group list-group-flush" id="sharedUsersList">
                        </ul>
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
            // Register Service Worker to cache UI Shell
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then(reg => console.log('✓ Service Worker registered!'))
                        .catch(err => console.error('Service Worker connection failed:', err));
                });
            }

            // --- INITIALIZE LOCAL INDEXEDDB DATABASE ---
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
                console.log("✓ IndexedDB initialized successfully!");
                // 🌟 TRIGGER SYNC IF DOM IS LOADED
                if (typeof window.syncPendingNotes === 'function') {
                    window.syncPendingNotes();
                }
            };

            function getAuthHeaders(isFormData = false) {
                const token = localStorage.getItem('user_token');
                const headers = {};
                if (!isFormData) headers['Content-Type'] = 'application/json';
                headers['Accept'] = 'application/json';
                if (token) headers['Authorization'] = `Bearer ${token}`;
                return headers;
            }

            let currentEditingNoteId = null;
            let isNotePinned = false;
            let noteElementToDelete = null;
            let noteIdToDelete = null;

            document.addEventListener('DOMContentLoaded', function () {
                if (!localStorage.getItem('user_token')) {
                    window.location.href = '/login';
                    return;
                }
                const notesContainer = document.getElementById('notes-container');
                const searchBox = document.getElementById('search-box');
                const noteImageInput = document.getElementById('noteImageInput');
                const imagePreviewArea = document.getElementById('image-preview-area');
                let searchTimeout;

                // --- HANDLE IMAGE PREVIEW WHEN SELECTING FILES ---
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

                // --- 1. FETCH NOTES API (WITH INDEXEDDB FALLBACK) ---
                function fetchAndRenderNotes(searchKeyword = '', labelId = '') {
                    if (!navigator.onLine) {
                        document.getElementById('offlineStatusAlert')?.classList.remove('d-none');
                        readAllFromIndexedDB("cached_notes", function (offlineNotes) {
                            let filtered = offlineNotes;
                            if (searchKeyword) {
                                filtered = filtered.filter(n =>
                                    (n.title && n.title.toLowerCase().includes(searchKeyword.toLowerCase())) ||
                                    (n.content && n.content.toLowerCase().includes(searchKeyword.toLowerCase()))
                                );
                            }
                            renderNotes(filtered);
                        });
                        return;
                    }

                    let url = '/api/notes';
                    const params = new URLSearchParams();
                    if (searchKeyword) params.append('search', searchKeyword);
                    if (labelId) params.append('label_id', labelId);
                    if (params.toString()) url += `?${params.toString()}`;

                    fetch(url, { method: 'GET', headers: getAuthHeaders() })
                        .then(async res => {
                            if (res.status === 401) {
                                localStorage.removeItem('user_token');
                                localStorage.removeItem('user_name');
                                window.location.href = '/login';
                                return;
                            }
                            const contentType = res.headers.get("content-type");
                            if (!contentType || !contentType.includes("application/json")) {
                                throw new Error("Backend API is returning a 500 error or is incomplete!");
                            }
                            return res.json();
                        })
                        .then(response => {
                            if (response.status === 'success') {
                                const notesArray = response.data || [];
                                renderNotes(notesArray);

                                if (!searchKeyword && !labelId) {
                                    clearObjectStore("cached_notes");
                                    notesArray.forEach(note => saveToIndexedDB("cached_notes", note));
                                }

                                const alertBox = document.getElementById('unverifiedHomepageAlert');
                                if (alertBox && response.user && response.user.is_active === false) {
                                    alertBox.classList.remove('d-none');
                                }
                            } else {
                                throw new Error(response.message || "Backend logic error");
                            }
                        })
                        .catch(err => {
                            console.error("Error loading notes list:", err);
                            readAllFromIndexedDB("cached_notes", renderNotes);
                        });
                }

                // --- 2. RENDER DATA FROM API TO HTML ---
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
                        if (note.is_shared) iconsHtml += '<i class="bi bi-people-fill text-primary me-1" title="Shared"></i>';
                        if (note.images && note.images.length > 0) iconsHtml += '<i class="bi bi-image text-success" title="Contains Images"></i>';

                        let labelsHtml = '';
                        if (note.labels && note.labels.length > 0) {
                            note.labels.forEach(lbl => {
                                labelsHtml += `<span class="badge bg-secondary text-white mt-2 me-1">${lbl.name}</span>`;
                            });
                        }

                        let imagesThumbnailHtml = '';
                        if (note.images && note.images.length > 0) {
                            imagesThumbnailHtml += '<div class="d-flex gap-1 mt-2 flex-wrap">';
                            note.images.forEach(img => {
                                imagesThumbnailHtml += `<img src="${img.image_url}" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">`;
                            });
                            imagesThumbnailHtml += '</div>';
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
                                                                        <p class="card-text mb-1">${displayContent}</p>
                                                                        ${imagesThumbnailHtml}
                                                                        <div class="note-labels-area">${labelsHtml}</div>
                                                                    </div>
                                                                    <div class="card-footer bg-transparent border-top-0 text-muted small d-flex justify-content-between align-items-center">
                                                                        <span><i class="bi bi-clock"></i> ${note.updated_at ? new Date(note.updated_at).toLocaleString() : 'Just now'}</span>
                                                                        <button class="btn btn-sm btn-light btn-delete" data-id="${note.id}"><i class="bi bi-trash text-danger"></i></button>
                                                                    </div>
                                                                </div>`;

                        col.querySelector('.note-card').addEventListener('click', function (e) {
                            if (e.target.closest('.btn-delete')) return;

                            if (note.is_locked) {
                                const pass = prompt("This note is password protected. Please enter the password:");
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
                // HELPER FUNCTION: Render attached images list with quick delete X button
                function renderModalImages(images, isEditMode = true) {
                    if (!imagePreviewArea) return;
                    imagePreviewArea.innerHTML = '';

                    if (images && images.length > 0) {
                        imagePreviewArea.classList.remove('d-none');
                        images.forEach(imgObj => {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'position-relative d-inline-block me-2 mb-2';

                            // Only show X delete button if having Edit permission
                            let deleteBtnHtml = '';
                            if (isEditMode) {
                                deleteBtnHtml = `
                                                                    <button type="button" class="btn btn-danger p-0 d-flex align-items-center justify-content-center rounded-circle position-absolute top-0 end-0" 
                                                                        style="width: 20px; height: 20px; transform: translate(30%, -30%); font-size: 0.75rem; z-index: 10;" 
                                                                        onclick="window.deleteNoteImage(${imgObj.id}, this)">
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
                // --- 3. LOAD NOTE DETAILS INTO MODAL FOR EDITING ---
                function openNoteEditor(note) {
                    currentEditingNoteId = note.id;
                    document.getElementById('noteTitle').value = note.title || '';
                    document.getElementById('noteContent').value = note.content || '';
                    isNotePinned = !!note.is_pinned;
                    updatePinButtonUI();

                    const hasEditPermission = (!note.is_shared || note.permission === 'edit');
                    renderModalImages(note.images, hasEditPermission);
                    if (noteImageInput) noteImageInput.value = '';

                    if (imagePreviewArea) {
                        imagePreviewArea.innerHTML = '';
                        if (note.images && note.images.length > 0) {
                            imagePreviewArea.classList.remove('d-none');
                            note.images.forEach(imgObj => {
                                const img = document.createElement('img');
                                img.src = imgObj.image_url;
                                img.className = 'img-thumbnail';
                                img.style.width = '75px';
                                img.style.height = '75px';
                                img.style.objectFit = 'cover';
                                imagePreviewArea.appendChild(img);
                            });
                        } else {
                            imagePreviewArea.classList.add('d-none');
                        }
                    }

                    if (noteImageInput) noteImageInput.value = '';

                    document.querySelectorAll('#labelDropdownList input[type="checkbox"]').forEach(cb => cb.checked = false);
                    if (note.labels && note.labels.length > 0) {
                        note.labels.forEach(lbl => {
                            const cb = document.getElementById('label_' + lbl.id);
                            if (cb) cb.checked = true;
                        });
                    }

                    if ((note.permission === 'edit' || !note.is_shared) && navigator.onLine) {
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

                // --- 4. COMPLETE LIVE SEARCH ---
                if (searchBox) {
                    searchBox.addEventListener('input', function () {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => fetchAndRenderNotes(this.value.trim()), 300);
                    });
                }

                // --- 5. DELETE NOTE LOGIC ---
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

                // --- 7. COMPLETE AUTO-SAVE LOGIC ---
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
                    const titleVal = document.getElementById('noteTitle') ? document.getElementById('noteTitle').value : '';
                    const contentVal = document.getElementById('noteContent') ? document.getElementById('noteContent').value : '';
                    const pinVal = typeof isNotePinned !== 'undefined' && isNotePinned ? 1 : 0;

                    const indicator = document.getElementById('saveStatusIndicator');
                    if (indicator) indicator.innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span> Saving...';

                    if (!navigator.onLine) {
                        if (typeof db !== 'undefined' && db) {
                            if (!currentEditingNoteId) {
                                currentEditingNoteId = 'temp_' + Date.now();
                            }
                            const offlinePayload = {
                                id: currentEditingNoteId,
                                local_id: currentEditingNoteId,
                                title: titleVal,
                                content: contentVal,
                                is_pinned: pinVal,
                                updated_at: new Date().toISOString()
                            };
                            saveToIndexedDB("pending_sync", offlinePayload);
                            if (indicator) indicator.innerHTML = '<i class="bi bi-hdd text-warning"></i> Saved locally (Offline)';
                        }
                        return;
                    }

                    const formData = new FormData();
                    formData.append('title', titleVal);
                    formData.append('content', contentVal);
                    formData.append('is_pinned', pinVal);
                    formData.append('sync_labels', 1);

                    const checkedLabels = document.querySelectorAll('#labelDropdownList input[type="checkbox"]:checked');
                    checkedLabels.forEach(cb => formData.append('labels[]', cb.value));

                    if (noteImageInput && noteImageInput.files.length > 0) {
                        for (let i = 0; i < noteImageInput.files.length; i++) {
                            formData.append('images[]', noteImageInput.files[i]);
                        }
                    }

                    let isTempId = currentEditingNoteId && String(currentEditingNoteId).startsWith('temp_');
                    let url = (currentEditingNoteId && !isTempId) ? `/api/notes/${currentEditingNoteId}` : '/api/notes';
                    let method = 'POST';
                    if (currentEditingNoteId && !isTempId) {
                        formData.append('_method', 'PUT');
                    }

                    fetch(url, {
                        method: method,
                        headers: getAuthHeaders(true),
                        body: formData
                    })
                        .then(res => res.json())
                        .then(response => {
                            if (response.status === 'success') {
                                if ((!currentEditingNoteId || isTempId) && response.data && response.data.id) {
                                    currentEditingNoteId = response.data.id;
                                }
                                if (indicator) indicator.innerHTML = '<i class="bi bi-cloud-check text-success"></i> Saved to Cloud';
                                // SECURITY CHECKPOINT 1: Clear the file input queue immediately after successful cloud upload!
                                if (noteImageInput) noteImageInput.value = '';

                                // SECURITY CHECKPOINT 2: Re-render the new image gallery from the Server to update real IDs for the delete buttons
                                if (response.data && response.data.images) {
                                    renderModalImages(response.data.images, true);
                                }
                                fetchAndRenderNotes();
                            } else {
                                if (indicator) indicator.innerHTML = '<i class="bi bi-exclamation-triangle text-danger"></i> ' + (response.message || 'Error saving Note');
                            }
                        })
                        .catch(err => {
                            if (typeof db !== 'undefined' && db) {
                                const offlinePayload = { id: currentEditingNoteId, local_id: currentEditingNoteId, title: titleVal, content: contentVal, is_pinned: pinVal, updated_at: new Date().toISOString() };
                                saveToIndexedDB("pending_sync", offlinePayload);
                                if (indicator) indicator.innerHTML = '<i class="bi bi-hdd text-danger"></i> Saved locally (Backend API Error)';
                            }
                        });
                }

                if (noteTitleInput && noteContentInput) {
                    noteTitleInput.addEventListener('input', triggerAutoSave);
                    noteContentInput.addEventListener('input', triggerAutoSave);
                }

                // --- 8. REALTIME COLLABORATION WITH WEBSOCKETS ---
                let socket = null;
                function joinNoteRealtimeChannel(noteId) {
                    if (!navigator.onLine) return;
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
                    if (socket && socket.readyState === 1 && currentEditingNoteId && navigator.onLine) {
                        socket.send(JSON.stringify({ note_id: currentEditingNoteId, title: noteTitleInput.value, content: noteContentInput.value }));
                    }
                }
                noteTitleInput?.addEventListener('input', broadcastTypingStatus);
                noteContentInput?.addEventListener('input', broadcastTypingStatus);

                // --- 9. RESET MODAL ON CREATE NEW BUTTON CLICK ---
                document.querySelector('[data-bs-target="#editorModal"]')?.addEventListener('click', function () {
                    currentEditingNoteId = null;
                    noteTitleInput.value = '';
                    noteContentInput.value = '';
                    isNotePinned = false;
                    updatePinButtonUI();
                    document.querySelectorAll('#labelDropdownList input[type="checkbox"]').forEach(cb => cb.checked = false);
                    if (imagePreviewArea) {
                        imagePreviewArea.innerHTML = '';
                        imagePreviewArea.classList.add('d-none');
                    }
                    if (noteImageInput) noteImageInput.value = '';
                    if (saveStatusIndicator) saveStatusIndicator.innerHTML = '<i class="bi bi-cloud"></i> New Note Ready';
                });

                // 🌟 CREATE A DEDICATED SYNC FUNCTION AND ATTACH TO WINDOW FOR EXTERNAL CALLS
                window.syncPendingNotes = function () {
                    if (!navigator.onLine) return;

                    readAllFromIndexedDB("pending_sync", function (pendingNotes) {
                        if (pendingNotes.length === 0) return;
                        console.log(`🚀 Syncing ${pendingNotes.length} notes to server...`);

                        pendingNotes.forEach(note => {
                            let isNewNote = String(note.id).startsWith('temp_');
                            let url = (!isNewNote && note.id) ? `/api/notes/${note.id}` : '/api/notes';
                            let method = 'POST';

                            const syncData = new FormData();
                            syncData.append('title', note.title || '');
                            syncData.append('content', note.content || '');
                            syncData.append('is_pinned', note.is_pinned || 0);

                            if (!isNewNote && note.id) {
                                syncData.append('_method', 'PUT');
                            }

                            fetch(url, {
                                method: method,
                                headers: getAuthHeaders(true),
                                body: syncData
                            })
                                .then(res => res.json())
                                .then(response => {
                                    if (response.status === 'success') {
                                        deleteFromIndexedDB("pending_sync", note.local_id);
                                        console.log("✅ Successfully synced temporary note: ", note.local_id);
                                    }
                                });
                        });

                        setTimeout(() => fetchAndRenderNotes(), 2000);
                    });
                };

                // --- CATCH NETWORK RESTORE EVENT FOR SYNC (SYNC LOGIC) ---
                window.addEventListener('online', function () {
                    document.getElementById('offlineStatusAlert')?.classList.add('d-none');
                    window.syncPendingNotes();
                });

                window.addEventListener('offline', function () {
                    document.getElementById('offlineStatusAlert')?.classList.remove('d-none');
                });

                // 🌟 TRIGGER SYNC IF DATABASE IS READY BEFORE DOM LOADED
                if (db) {
                    window.syncPendingNotes();
                }

                // --- API INTEGRATION CODE FOR LABELS, PASSWORD, SHARE ---
                function fetchLabels() {
                    if (!navigator.onLine) return;
                    fetch('/api/labels', { method: 'GET', headers: getAuthHeaders() })
                        .then(res => res.json())
                        .then(response => {
                            if (response.status === 'success') {
                                const labels = response.data || [];

                                const sidebar = document.getElementById('labelFilterMenu');
                                if (sidebar) {
                                    sidebar.innerHTML = `<a href="#" class="list-group-item list-group-item-action active border-0 label-filter-item" onclick="filterByLabel('')"><i class="bi bi-collection"></i> All Notes</a>`;
                                    labels.forEach(lbl => {
                                        sidebar.innerHTML += `<a href="#" class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center label-filter-item" onclick="filterByLabel(${lbl.id})">
                                                                                <span><i class="bi bi-tag"></i> ${lbl.name}</span>
                                                                                <span class="badge bg-secondary rounded-pill">${lbl.notes_count || 0}</span>
                                                                            </a>`;
                                    });
                                }

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

                                const dropdown = document.getElementById('labelDropdownList');
                                if (dropdown) {
                                    const checkedLabelIds = Array.from(dropdown.querySelectorAll('input[type="checkbox"]:checked')).map(cb => cb.value);

                                    dropdown.innerHTML = `<li><h6 class="dropdown-header">Assign Label</h6></li>`;
                                    labels.forEach(lbl => {
                                        const isChecked = checkedLabelIds.includes(lbl.id.toString()) ? 'checked' : '';
                                        dropdown.innerHTML += `<li><a class="dropdown-item d-flex align-items-center" href="#"><input class="form-check-input me-2 mt-0" type="checkbox" value="${lbl.id}" id="label_${lbl.id}" ${isChecked}><label class="form-check-label w-100" for="label_${lbl.id}">${lbl.name}</label></a></li>`;
                                    });
                                }
                            }
                        });
                    document.getElementById('labelDropdownList')?.addEventListener('change', function (e) {
                        if (e.target.type === 'checkbox') {
                            triggerAutoSave();
                            setTimeout(() => fetchLabels(), 1500);
                        }
                    });
                }

                window.deleteLabel = function (labelId) {
                    fetch(`/api/labels/${labelId}`, { method: 'DELETE', headers: getAuthHeaders() })
                        .then(res => res.json())
                        .then(response => { if (response.status === 'success') fetchLabels(); });
                };

                window.filterByLabel = function (labelId) {
                    fetchAndRenderNotes('', labelId);
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

                fetchLabels();

                // --- NOTE PASSWORD PROTECTION ---
                document.getElementById('btnSavePassword')?.addEventListener('click', function () {
                    if (!currentEditingNoteId) return alert('Please save the note content before setting a password!');
                    const pass = document.getElementById('notePassword').value;
                    const confirmPass = document.getElementById('noteConfirmPassword').value;

                    fetch(`/api/notes/${currentEditingNoteId}/password`, {
                        method: 'POST',
                        headers: getAuthHeaders(),
                        body: JSON.stringify({ new_password: pass, confirm_password: confirmPass, action: 'enable' })
                    }).then(res => res.json()).then(response => {
                        if (response.status === 'success') {
                            alert('Note password protection enabled successfully!');
                            document.getElementById('notePassword').value = '';
                            document.getElementById('noteConfirmPassword').value = '';
                            fetchAndRenderNotes();
                        } else alert(response.message || 'Passwords do not match or an error occurred.');
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
                            noteObj.is_locked = false;
                            openNoteEditor(noteObj);
                        } else alert("Incorrect password!");
                    });
                }

                // --- NOTE SHARING MANAGEMENT ---
                const shareManagerModalEl = document.getElementById('shareManagerModal');
                let shareModal = null;
                if (shareManagerModalEl) {
                    shareModal = new bootstrap.Modal(shareManagerModalEl);
                }

                document.getElementById('btnShareNote')?.addEventListener('click', function () {
                    if (!currentEditingNoteId) return alert('Please type some content to save the note before sharing!');
                    loadSharedUsers();
                    if (shareModal) shareModal.show();
                });

                function loadSharedUsers() {
                    const list = document.getElementById('sharedUsersList');
                    if (!list) return;
                    list.innerHTML = '<li class="list-group-item text-center border-0"><span class="spinner-border spinner-border-sm text-primary"></span> Loading...</li>';

                    fetch(`/api/notes/${currentEditingNoteId}/shared-users`, { headers: getAuthHeaders() })
                        .then(res => res.json())
                        .then(response => {
                            list.innerHTML = '';
                            if (response.data.length === 0) {
                                list.innerHTML = '<li class="list-group-item text-muted text-center small border-0 py-4"><i class="bi bi-lock" style="font-size:2rem;"></i><br>Private. Not shared with anyone yet.</li>';
                                return;
                            }
                            response.data.forEach(user => {
                                const isView = user.permission === 'view' ? 'selected' : '';
                                const isEdit = user.permission === 'edit' ? 'selected' : '';

                                list.innerHTML += `
                                                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom-dashed">
                                                                            <div class="text-truncate" style="max-width: 60%;" title="${user.recipient_email}">
                                                                                <i class="bi bi-person-circle me-2 fs-5 text-secondary align-middle"></i>
                                                                                <strong class="align-middle">${user.recipient_email}</strong>
                                                                            </div>
                                                                            <div class="d-flex gap-2 align-items-center">
                                                                                <select class="form-select form-select-sm text-secondary border-0 bg-body-tertiary fw-bold" style="width: 85px; cursor: pointer;" onchange="updatePermission('${user.recipient_email}', this.value)">
                                                                                    <option value="view" ${isView}>View</option>
                                                                                    <option value="edit" ${isEdit}>Edit</option>
                                                                                </select>

                                                                                <button class="btn btn-sm btn-outline-danger border-0" onclick="revokeShare('${user.recipient_email}')" title="Revoke Access">
                                                                                    <i class="bi bi-person-x-fill"></i>
                                                                                </button>
                                                                            </div>
                                                                        </li>
                                                                    `;
                            });
                        });
                }

                window.updatePermission = function (email, newPermission) {
                    fetch(`/api/notes/${currentEditingNoteId}/share`, {
                        method: 'POST',
                        headers: getAuthHeaders(),
                        body: JSON.stringify({ recipient_email: email, permission: newPermission })
                    }).then(res => res.json()).then(res => {
                        if (res.status === 'success') {
                            console.log(`Changed permissions for ${email} to ${newPermission}`);
                        } else {
                            alert(res.message);
                            loadSharedUsers();
                        }
                    });
                };

                document.getElementById('btnSubmitShare')?.addEventListener('click', function () {
                    const email = document.getElementById('shareEmailInput').value.trim();
                    const perm = document.getElementById('sharePermissionSelect').value;
                    if (!email) return alert('Please enter an email address!');

                    const btn = this; btn.innerHTML = '...'; btn.disabled = true;

                    fetch(`/api/notes/${currentEditingNoteId}/share`, {
                        method: 'POST',
                        headers: getAuthHeaders(),
                        body: JSON.stringify({ recipient_email: email, permission: perm })
                    }).then(res => res.json()).then(res => {
                        btn.innerHTML = 'Share'; btn.disabled = false;
                        if (res.status === 'success') {
                            document.getElementById('shareEmailInput').value = '';
                            loadSharedUsers();
                        } else alert(res.message);
                    });
                });

                window.revokeShare = function (email) {
                    if (!confirm(`Are you sure you want to revoke access for ${email}?`)) return;
                    fetch(`/api/notes/${currentEditingNoteId}/share/revoke`, {
                        method: 'POST',
                        headers: getAuthHeaders(),
                        body: JSON.stringify({ recipient_email: email })
                    }).then(res => res.json()).then(res => {
                        loadSharedUsers();
                    });
                };
            });

            // ==============================================================
            // HELPER FUNCTIONS FOR INDEPENDENT READ/WRITE TO INDEXEDDB
            // ==============================================================
            function getSafeDB(callback) {
                if (db) {
                    callback(db);
                } else {
                    const req = indexedDB.open("MyNotesOfflineDB", 2);
                    req.onsuccess = (e) => {
                        db = e.target.result;
                        callback(db);
                    };
                    req.onerror = (e) => {
                        console.error("Error opening IndexedDB: ", e);
                        callback(null);
                    };
                }
            }
            function saveToIndexedDB(storeName, data) {
                if (!db) return;
                const tx = db.transaction(storeName, "readwrite");
                tx.objectStore(storeName).put(data);
            }

            function readAllFromIndexedDB(storeName, callback) {
                if (!db) return callback([]);
                const tx = db.transaction(storeName, "readonly");
                const store = tx.objectStore(storeName);
                const req = store.getAll();
                req.onsuccess = function () { callback(req.result || []); };
            }

            function clearObjectStore(storeName) {
                if (!db) return;
                const tx = db.transaction(storeName, "readwrite");
                tx.objectStore(storeName).clear();
            }

            function deleteFromIndexedDB(storeName, key) {
                if (!db) return;
                const tx = db.transaction(storeName, "readwrite");
                tx.objectStore(storeName).delete(key);
            }
            // TRIGGER SO HTML TAGS CAN CALL FUNCTIONS FROM OUTSIDE THE MODAL
            window.deleteNoteImage = function (imageId, btnElement) {
                if (!confirm('Are you sure you want to remove this image from the note?')) return;

                fetch(`/api/notes/images/${imageId}`, {
                    method: 'DELETE',
                    headers: getAuthHeaders()
                })
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            // Remove the image element from the UI immediately
                            btnElement.closest('.position-relative').remove();
                            // Refresh the thumbnail note list on the main screen
                            if (typeof fetchAndRenderNotes === 'function') fetchAndRenderNotes();
                        } else {
                            alert(response.message || 'System error while deleting image.');
                        }
                    })
                    .catch(err => console.error("Error deleting image:", err));
            };
        </script>
@endsection