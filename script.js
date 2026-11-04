/**
 * script.js — Student Task Management Dashboard
 * =============================================================
 * Responsibilities:
 *  - Fetch all tasks from api.php on page load
 *  - Render task cards into the correct Kanban column
 *  - Handle Create, Edit, Delete via modal + Fetch API
 *  - Status quick-change from the card dropdown menu
 *  - Live search filtering across rendered cards
 *  - Priority chip filtering
 *  - Update stats row & sidebar badges
 *  - Toast notification system
 *  - Mobile sidebar toggle
 * =============================================================
 */

'use strict';

// =============================================================
// CONFIGURATION
// =============================================================

/** Base URL of the PHP API (same directory as index.html). */
const API_BASE = 'api.php';

// Priority & status display maps
const PRIORITY_LABEL = { high: 'Prioritas Tinggi', medium: 'Prioritas Sedang', low: 'Prioritas Rendah' };
const STATUS_LABEL   = { todo: 'To Do', inprogress: 'In Progress', completed: 'Completed', overdue: 'Overdue' };

// SVG icon snippets reused in card rendering
const ICON = {
  calendar: `<svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M20 3h-1V1h-2v2H7V1H5v2H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm0 18H4V8h16v13z"/></svg>`,
  bell:     `<svg viewBox="0 0 24 24" width="11" height="11" fill="currentColor"><path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 0 0 2 2zm6-6V11c0-3.07-1.63-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>`,
  warn:     `<svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>`,
  check:    `<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`,
  dots:     `<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>`,
  edit:     `<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>`,
  trash:    `<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>`,
};

// =============================================================
// STATE
// =============================================================
let allTasks       = [];      // full dataset returned by API
let activeFilter   = 'all';   // current priority filter chip
let deleteTargetId = null;    // id pending delete confirmation
let currentPage    = 'dashboard'; // active sidebar page
let calYear        = new Date().getFullYear();
let calMonth       = new Date().getMonth();   // 0-based

// =============================================================
// DOM REFERENCES
// =============================================================
const $ = id => document.getElementById(id);

// Kanban column bodies
const colEl = {
  todo:       $('col-todo'),
  inprogress: $('col-inprogress'),
  completed:  $('col-completed'),
  overdue:    $('col-overdue'),
};

// Stats counters
const statEl = {
  todo:       $('stat-todo'),
  inprogress: $('stat-inprogress'),
  completed:  $('stat-completed'),
  overdue:    $('stat-overdue'),
};

// Column card-count badges
const countEl = {
  todo:       $('count-todo'),
  inprogress: $('count-inprogress'),
  completed:  $('count-completed'),
  overdue:    $('count-overdue'),
};


// =============================================================
// INITIALISE
// =============================================================
document.addEventListener('DOMContentLoaded', () => {
  loadTasks();
  initEventListeners();
});


// =============================================================
// API CALLS
// =============================================================

/**
 * Fetch all tasks and render the board.
 */
async function loadTasks() {
  showLoading(true);
  try {
    const res  = await fetch(`${API_BASE}?action=all`);
    const json = await res.json();

    if (!json.success) throw new Error(json.message || 'Failed to load tasks.');

    allTasks = json.data || [];
    renderBoard();
  } catch (err) {
    showToast(err.message, 'error');
  } finally {
    showLoading(false);
  }
}

/**
 * Create a new task.
 * @param {Object} payload
 */
async function createTask(payload) {
  const res  = await fetch(`${API_BASE}?action=create`, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(payload),
  });
  return res.json();
}

/**
 * Update an existing task.
 * @param {number} id
 * @param {Object} payload
 */
async function updateTask(id, payload) {
  const res = await fetch(`${API_BASE}?action=update&id=${id}`, {
    method:  'PUT',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(payload),
  });
  return res.json();
}

/**
 * Update only the status of a task (lightweight endpoint).
 * @param {number} id
 * @param {string} status
 */
async function updateStatus(id, status) {
  const res = await fetch(`${API_BASE}?action=status&id=${id}`, {
    method:  'PUT',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ status }),
  });
  return res.json();
}

/**
 * Delete a task by id.
 * @param {number} id
 */
async function deleteTask(id) {
  const res = await fetch(`${API_BASE}?action=delete&id=${id}`, {
    method: 'DELETE',
  });
  return res.json();
}


// =============================================================
// RENDERING
// =============================================================

/**
 * Re-render the full board based on allTasks + active filters.
 */
function renderBoard() {
  // Apply priority filter
  const tasks = getFilteredTasks();

  // Clear all columns
  Object.values(colEl).forEach(col => { col.innerHTML = ''; });

  // Counters
  const counts = { todo: 0, inprogress: 0, completed: 0, overdue: 0 };

  tasks.forEach(task => {
    const status = task.status;
    if (!colEl[status]) return; // safety guard

    counts[status]++;
    colEl[status].appendChild(buildCard(task));
  });

  // Update stat numbers & column counts
  Object.keys(counts).forEach(s => {
    statEl[s].textContent  = allTasks.filter(t => t.status === s).length; // always use full count for stats
    countEl[s].textContent = counts[s]; // filtered count
  });

  // Total active tasks (excluding completed)
  const activeCount = allTasks.filter(t => t.status !== 'completed').length;
  $('total-tasks').textContent = activeCount;

  // Sidebar badges
  $('badge-tasks').textContent = allTasks.length;
  const overdueCount = allTasks.filter(t => t.status === 'overdue').length;
  $('badge-notif').textContent = overdueCount;

  // Notification dot
  const notifDot = $('notif-dot');
  if (overdueCount > 0) { notifDot.removeAttribute('hidden'); }
  else                  { notifDot.setAttribute('hidden', ''); }

  // Show empty placeholders
  Object.entries(colEl).forEach(([status, col]) => {
    if (col.children.length === 0) {
      const emp = document.createElement('div');
      emp.className = 'col-empty';
      emp.textContent = 'Tidak ada tugas';
      col.appendChild(emp);
    }
  });
}

/**
 * Build a task card DOM element.
 * @param {Object} task
 * @returns {HTMLElement}
 */
function buildCard(task) {
  const card = document.createElement('article');
  card.className = 'task-card';
  card.dataset.id = task.id;
  card.setAttribute('aria-label', `Tugas: ${task.title}`);

  const priorityClass  = `priority-${task.priority}`;
  const priorityText   = PRIORITY_LABEL[task.priority] || task.priority;
  const formattedDate  = formatDate(task.deadline);
  const isOverdue      = task.status === 'overdue';
  const isCompleted    = task.status === 'completed';

  // Reminder / stamp row
  let footerRight = '';
  if (isCompleted) {
    footerRight = `<span class="done-stamp">${ICON.check} Dikumpulkan</span>`;
  } else if (task.reminder) {
    footerRight = `
      <span class="reminder-label">
        ${isOverdue ? ICON.warn : ICON.bell}
        ${escHtml(task.reminder)}
      </span>`;
  }

  // Status quick-change options (exclude current status)
  const statusOptions = Object.entries(STATUS_LABEL)
    .filter(([s]) => s !== task.status)
    .map(([s, l]) => `<button class="status-change-btn" data-status="${s}">→ ${l}</button>`)
    .join('');

  card.innerHTML = `
    <div class="card-top">
      <span class="card-title">${escHtml(task.title)}</span>
      <div class="card-menu-wrap">
        <button class="card-menu-btn" aria-label="Opsi tugas" aria-haspopup="true">
          ${ICON.dots}
        </button>
        <div class="card-dropdown" role="menu">
          <button class="edit-btn" role="menuitem">${ICON.edit} Edit Tugas</button>
          <button class="danger delete-btn" role="menuitem">${ICON.trash} Hapus Tugas</button>
          <div class="dropdown-divider"></div>
          <div class="dropdown-status-label">Pindah ke</div>
          ${statusOptions}
        </div>
      </div>
    </div>

    ${task.subject ? `<span class="card-tag">${escHtml(task.subject)}</span>` : ''}

    <div class="card-deadline">
      ${isOverdue ? ICON.warn : ICON.calendar}
      ${isOverdue ? 'Terlambat sejak: ' : 'Deadline: '}${formattedDate}
    </div>

    <div class="card-footer">
      <span class="priority-pill ${priorityClass}">
        <span class="priority-dot"></span>${priorityText}
      </span>
      ${footerRight}
    </div>
  `;

  // ── Card event listeners ──────────────────────────────────

  // Toggle dropdown
  const menuBtn  = card.querySelector('.card-menu-btn');
  const dropdown = card.querySelector('.card-dropdown');

  menuBtn.addEventListener('click', e => {
    e.stopPropagation();
    closeAllDropdowns(dropdown); // close others first
    dropdown.classList.toggle('open');
  });

  // Edit
  card.querySelector('.edit-btn').addEventListener('click', () => {
    dropdown.classList.remove('open');
    openEditModal(task);
  });

  // Delete
  card.querySelector('.delete-btn').addEventListener('click', () => {
    dropdown.classList.remove('open');
    openDeleteModal(task);
  });

  // Status change buttons
  card.querySelectorAll('.status-change-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      dropdown.classList.remove('open');
      await handleStatusChange(task.id, btn.dataset.status);
    });
  });

  return card;
}

/**
 * Close all open card dropdowns except the given one.
 * @param {HTMLElement|null} except
 */
function closeAllDropdowns(except = null) {
  document.querySelectorAll('.card-dropdown.open').forEach(d => {
    if (d !== except) d.classList.remove('open');
  });
}

// Close dropdowns when clicking elsewhere
document.addEventListener('click', () => closeAllDropdowns());


// =============================================================
// FILTERING & SEARCH
// =============================================================

/**
 * Returns tasks filtered by the active priority filter
 * and the current search query.
 */
function getFilteredTasks() {
  const query = ($('search-input').value || '').toLowerCase().trim();

  return allTasks.filter(task => {
    const matchesPriority = activeFilter === 'all' || task.priority === activeFilter;
    const matchesSearch   = !query
      || task.title.toLowerCase().includes(query)
      || (task.subject || '').toLowerCase().includes(query)
      || (task.description || '').toLowerCase().includes(query);
    return matchesPriority && matchesSearch;
  });
}


// =============================================================
// MODAL — CREATE / EDIT
// =============================================================

/**
 * Open the modal in "create" mode, optionally pre-filling status.
 * @param {string} [preStatus='todo']
 */
function openCreateModal(preStatus = 'todo') {
  resetForm();
  $('modal-title').textContent  = 'Tambah Tugas Baru';
  $('form-submit-btn').innerHTML = `${ICON.check} Simpan Tugas`;
  $('task-id').value = '';
  $('f-status').value = preStatus;
  showModal(true);
  $('f-title').focus();
}

/**
 * Open the modal in "edit" mode, populated with task data.
 * @param {Object} task
 */
function openEditModal(task) {
  resetForm();
  $('modal-title').textContent  = 'Edit Tugas';
  $('form-submit-btn').innerHTML = `${ICON.edit} Perbarui Tugas`;
  $('task-id').value        = task.id;
  $('f-title').value        = task.title;
  $('f-description').value  = task.description || '';
  $('f-subject').value      = task.subject     || '';
  $('f-deadline').value     = task.deadline;   // YYYY-MM-DD works with <input type="date">
  $('f-priority').value     = task.priority;
  $('f-status').value       = task.status;
  $('f-reminder').value     = task.reminder    || '';
  showModal(true);
  $('f-title').focus();
}

/** Show/hide the task form modal. */
function showModal(show) {
  $('modal-backdrop').hidden = !show;
  document.body.style.overflow = show ? 'hidden' : '';
}

/** Reset form fields and validation state. */
function resetForm() {
  $('task-form').reset();
  $('task-id').value = '';
  $('err-title').textContent   = '';
  $('err-deadline').textContent = '';
  $('f-title').classList.remove('invalid');
  $('f-deadline').classList.remove('invalid');
  $('form-error').hidden = true;
  $('form-error').textContent = '';
}


// =============================================================
// FORM SUBMISSION
// =============================================================

$('task-form').addEventListener('submit', async e => {
  e.preventDefault();

  if (!validateForm()) return;

  const id      = $('task-id').value;
  const isEdit  = !!id;

  const payload = {
    title:       $('f-title').value.trim(),
    description: $('f-description').value.trim() || null,
    subject:     $('f-subject').value.trim()     || null,
    deadline:    $('f-deadline').value,
    priority:    $('f-priority').value,
    status:      $('f-status').value,
    reminder:    $('f-reminder').value.trim()    || null,
  };

  const submitBtn = $('form-submit-btn');
  submitBtn.disabled = true;

  try {
    const json = isEdit
      ? await updateTask(Number(id), payload)
      : await createTask(payload);

    if (!json.success) {
      showFormError(json.message || 'Terjadi kesalahan.');
      return;
    }

    showModal(false);
    showToast(
      isEdit ? 'Tugas berhasil diperbarui.' : 'Tugas berhasil ditambahkan.',
      'success'
    );
    await loadTasks(); // refresh board
  } catch {
    showFormError('Gagal menyimpan tugas. Coba lagi.');
  } finally {
    submitBtn.disabled = false;
  }
});

/**
 * Client-side validation.
 * @returns {boolean} true if valid
 */
function validateForm() {
  let valid = true;

  const title    = $('f-title').value.trim();
  const deadline = $('f-deadline').value;

  if (!title) {
    $('err-title').textContent = 'Judul tugas tidak boleh kosong.';
    $('f-title').classList.add('invalid');
    valid = false;
  } else {
    $('err-title').textContent = '';
    $('f-title').classList.remove('invalid');
  }

  if (!deadline) {
    $('err-deadline').textContent = 'Deadline harus diisi.';
    $('f-deadline').classList.add('invalid');
    valid = false;
  } else {
    $('err-deadline').textContent = '';
    $('f-deadline').classList.remove('invalid');
  }

  return valid;
}

/** Show error banner inside the form modal. */
function showFormError(msg) {
  const banner = $('form-error');
  banner.textContent = msg;
  banner.hidden = false;
}


// =============================================================
// DELETE MODAL
// =============================================================

/**
 * Open delete confirmation modal.
 * @param {Object} task
 */
function openDeleteModal(task) {
  deleteTargetId = task.id;
  $('delete-task-title').textContent = `"${task.title}"`;
  $('delete-backdrop').hidden = false;
  document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
  deleteTargetId = null;
  $('delete-backdrop').hidden = true;
  document.body.style.overflow = '';
}

$('delete-confirm').addEventListener('click', async () => {
  if (!deleteTargetId) return;

  const btn = $('delete-confirm');
  btn.disabled = true;

  try {
    const json = await deleteTask(deleteTargetId);

    if (!json.success) {
      showToast(json.message || 'Hapus gagal.', 'error');
      return;
    }

    closeDeleteModal();
    showToast('Tugas berhasil dihapus.', 'success');
    await loadTasks();
  } catch {
    showToast('Gagal menghapus tugas. Coba lagi.', 'error');
  } finally {
    btn.disabled = false;
  }
});


// =============================================================
// STATUS CHANGE
// =============================================================

/**
 * Update task status and refresh board.
 * @param {number} id
 * @param {string} newStatus
 */
async function handleStatusChange(id, newStatus) {
  try {
    const json = await updateStatus(id, newStatus);
    if (!json.success) {
      showToast(json.message || 'Gagal mengubah status.', 'error');
      return;
    }
    showToast(`Tugas dipindahkan ke ${STATUS_LABEL[newStatus]}.`, 'info');
    await loadTasks();
  } catch {
    showToast('Gagal mengubah status. Coba lagi.', 'error');
  }
}


// =============================================================
// EVENT LISTENERS
// =============================================================

function initEventListeners() {

  // ── Modal open / close ──────────────────────────────────────
  $('open-create-modal').addEventListener('click', () => openCreateModal());

  $('modal-close').addEventListener('click',  () => showModal(false));
  $('modal-cancel').addEventListener('click', () => showModal(false));

  // Close modal on backdrop click
  $('modal-backdrop').addEventListener('click', e => {
    if (e.target === $('modal-backdrop')) showModal(false);
  });

  $('delete-modal-close').addEventListener('click', closeDeleteModal);
  $('delete-cancel').addEventListener('click',      closeDeleteModal);

  $('delete-backdrop').addEventListener('click', e => {
    if (e.target === $('delete-backdrop')) closeDeleteModal();
  });

  // ── Column "Tambah Tugas" buttons ───────────────────────────
  document.querySelectorAll('.col-add-btn').forEach(btn => {
    btn.addEventListener('click', () => openCreateModal(btn.dataset.status));
  });

  // ── Keyboard: Escape closes modals ─────────────────────────
  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    if (!$('modal-backdrop').hidden)  showModal(false);
    if (!$('delete-backdrop').hidden) closeDeleteModal();
    closeAllDropdowns();
  });

  // ── Search input (debounced) ────────────────────────────────
  $('search-input').addEventListener('input', debounce(() => renderBoard(), 250));

  // ── Filter chips ────────────────────────────────────────────
  document.querySelectorAll('.filter-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      activeFilter = chip.dataset.filter;
      renderBoard();
    });
  });

  // ── Sidebar nav ─────────────────────────────────────────────
  document.querySelectorAll('.sidebar-nav a').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const page = link.dataset.nav;
      if (page) navigateTo(page);
      // On mobile, close sidebar after nav click
      $('sidebar').classList.remove('sidebar-open');
    });
  });

  // ── Notification bell → jump to Notifikasi page ─────────────
  $('notif-btn').addEventListener('click', () => navigateTo('notif'));

  // ── Tugas Saya: filter/sort controls ────────────────────────
  $('tasks-add-btn').addEventListener('click', () => openCreateModal());
  $('tasks-filter-status').addEventListener('change', renderTasksView);
  $('tasks-filter-priority').addEventListener('change', renderTasksView);
  $('tasks-sort').addEventListener('change', renderTasksView);

  // ── Kalender: prev/next month ────────────────────────────────
  $('cal-prev').addEventListener('click', () => {
    calMonth--;
    if (calMonth < 0) { calMonth = 11; calYear--; }
    renderCalendarView();
  });
  $('cal-next').addEventListener('click', () => {
    calMonth++;
    if (calMonth > 11) { calMonth = 0; calYear++; }
    renderCalendarView();
  });

  // ── Notifikasi: clear all ────────────────────────────────────
  $('notif-clear-btn').addEventListener('click', () => {
    document.querySelectorAll('.notif-item').forEach(el => {
      el.classList.remove('unread', 'unread-danger');
    });
    $('badge-notif').textContent = '0';
    $('notif-dot').setAttribute('hidden', '');
    showToast('Semua notifikasi telah ditandai dibaca.', 'success');
  });

  // ── Pengaturan: save profile ─────────────────────────────────
  $('save-profile-btn').addEventListener('click', () => {
    const name = $('s-name').value.trim();
    if (name) {
      document.querySelector('.user-name').textContent = name;
      document.querySelector('.avatar').textContent = name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
    }
    const semester = $('s-semester').value.trim();
    if (semester) {
      const sub = document.querySelector('.content-subtitle');
      if (sub) sub.innerHTML = `${semester} — <span id="total-tasks">${$('total-tasks')?.textContent || 0}</span> tugas aktif`;
    }
    showToast('Profil berhasil disimpan.', 'success');
  });

  // ── Pengaturan: save preferences ─────────────────────────────
  $('save-pref-btn').addEventListener('click', () => {
    showToast('Preferensi berhasil disimpan.', 'success');
  });

  // ── Pengaturan: export tasks ─────────────────────────────────
  $('export-tasks-btn').addEventListener('click', () => {
    const blob = new Blob([JSON.stringify(allTasks, null, 2)], { type: 'application/json' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'taskboard-export.json';
    a.click();
    URL.revokeObjectURL(url);
    showToast('Data tugas berhasil diekspor.', 'success');
  });

  // ── Hamburger (mobile sidebar toggle) ──────────────────────
  $('hamburger-btn').addEventListener('click', () => {
    $('sidebar').classList.toggle('sidebar-open');
  });

  // Tap backdrop on mobile closes sidebar
  document.addEventListener('click', e => {
    const sidebar = $('sidebar');
    if (
      sidebar.classList.contains('sidebar-open') &&
      !sidebar.contains(e.target) &&
      e.target !== $('hamburger-btn')
    ) {
      sidebar.classList.remove('sidebar-open');
    }
  });
}



// =============================================================
// PAGE NAVIGATION
// =============================================================

const PAGE_TITLES = {
  dashboard: 'Papan Tugas',
  tasks:     'Tugas Saya',
  calendar:  'Kalender',
  progress:  'Progres',
  notif:     'Notifikasi',
  settings:  'Pengaturan',
};

/**
 * Switch to a page view by nav key.
 * @param {string} page - one of dashboard|tasks|calendar|progress|notif|settings
 */
function navigateTo(page) {
  if (page === currentPage) return;
  currentPage = page;

  // Hide all views
  document.querySelectorAll('.page-view').forEach(v => v.setAttribute('hidden', ''));
  // Show target view
  const view = $(`view-${page}`);
  if (view) view.removeAttribute('hidden');

  // Update topbar title
  const titleEl = $('topbar-title');
  if (titleEl) titleEl.textContent = PAGE_TITLES[page] || '';

  // Highlight active sidebar link
  document.querySelectorAll('.sidebar-nav li').forEach(li => li.classList.remove('active'));
  const activeLink = document.querySelector(`.sidebar-nav a[data-nav="${page}"]`);
  if (activeLink) activeLink.closest('li').classList.add('active');

  // Render page-specific content
  if (page === 'tasks')    renderTasksView();
  if (page === 'calendar') renderCalendarView();
  if (page === 'progress') renderProgressView();
  if (page === 'notif')    renderNotifView();
}


// =============================================================
// VIEW: TUGAS SAYA
// =============================================================

function renderTasksView() {
  const statusFilter   = $('tasks-filter-status').value;
  const priorityFilter = $('tasks-filter-priority').value;
  const sortBy         = $('tasks-sort').value;
  const list           = $('tasks-list');

  let tasks = [...allTasks];

  if (statusFilter !== 'all')   tasks = tasks.filter(t => t.status === statusFilter);
  if (priorityFilter !== 'all') tasks = tasks.filter(t => t.priority === priorityFilter);

  const priorityOrder = { high: 0, medium: 1, low: 2 };
  if (sortBy === 'deadline_asc')  tasks.sort((a, b) => (a.deadline || '').localeCompare(b.deadline || ''));
  if (sortBy === 'deadline_desc') tasks.sort((a, b) => (b.deadline || '').localeCompare(a.deadline || ''));
  if (sortBy === 'priority')      tasks.sort((a, b) => (priorityOrder[a.priority] ?? 1) - (priorityOrder[b.priority] ?? 1));
  if (sortBy === 'title')         tasks.sort((a, b) => a.title.localeCompare(b.title));

  list.innerHTML = '';

  if (tasks.length === 0) {
    list.innerHTML = '<div class="task-list-empty">Tidak ada tugas yang sesuai filter.</div>';
    return;
  }

  const statusColors = {
    todo: 'var(--todo-accent)',
    inprogress: 'var(--inprog-accent)',
    completed: 'var(--done-accent)',
    overdue: 'var(--over-accent)',
  };

  tasks.forEach(task => {
    const row = document.createElement('div');
    row.className = 'task-row';
    row.innerHTML = `
      <span class="task-row-status-dot" style="background:${statusColors[task.status] || '#ccc'}"></span>
      <div class="task-row-main">
        <div class="task-row-title${task.status === 'completed' ? ' done-strike' : ''}">${escHtml(task.title)}</div>
        <div class="task-row-meta">
          ${task.subject ? `<span>📚 ${escHtml(task.subject)}</span>` : ''}
          ${task.deadline ? `<span>📅 ${formatDate(task.deadline)}</span>` : ''}
          <span>${PRIORITY_LABEL[task.priority] || task.priority}</span>
          <span>${STATUS_LABEL[task.status] || task.status}</span>
        </div>
      </div>
      <div class="task-row-actions">
        <button class="task-row-btn" data-edit="${task.id}">Edit</button>
        <button class="task-row-btn danger" data-del="${task.id}" data-title="${escHtml(task.title)}">Hapus</button>
      </div>
    `;
    // Edit
    row.querySelector('[data-edit]').addEventListener('click', () => {
      const t = allTasks.find(x => x.id === task.id);
      if (t) openEditModal(t);
    });
    // Delete
    row.querySelector('[data-del]').addEventListener('click', () => {
      const t = allTasks.find(x => x.id === task.id);
      if (t) openDeleteModal(t);
    });
    list.appendChild(row);
  });
}


// =============================================================
// VIEW: KALENDER
// =============================================================

const MONTH_NAMES = ['Januari','Februari','Maret','April','Mei','Juni',
                     'Juli','Agustus','September','Oktober','November','Desember'];

function renderCalendarView() {
  $('cal-month-label').textContent = `${MONTH_NAMES[calMonth]} ${calYear}`;

  const grid = $('cal-grid');
  grid.innerHTML = '';

  const today = new Date();
  const firstDay  = new Date(calYear, calMonth, 1);
  const lastDay   = new Date(calYear, calMonth + 1, 0);
  const startDow  = firstDay.getDay(); // 0=Sun
  const totalDays = lastDay.getDate();

  // Build a map: 'YYYY-MM-DD' → [tasks]
  const taskMap = {};
  allTasks.forEach(t => {
    if (t.deadline) {
      if (!taskMap[t.deadline]) taskMap[t.deadline] = [];
      taskMap[t.deadline].push(t);
    }
  });

  // Leading blanks from previous month
  const prevLast = new Date(calYear, calMonth, 0).getDate();
  for (let i = startDow - 1; i >= 0; i--) {
    const day = document.createElement('div');
    day.className = 'cal-day other-month';
    day.innerHTML = `<div class="cal-day-num">${prevLast - i}</div>`;
    grid.appendChild(day);
  }

  // Days of current month
  for (let d = 1; d <= totalDays; d++) {
    const mm   = String(calMonth + 1).padStart(2, '0');
    const dd   = String(d).padStart(2, '0');
    const key  = `${calYear}-${mm}-${dd}`;
    const isToday = (d === today.getDate() && calMonth === today.getMonth() && calYear === today.getFullYear());

    const day = document.createElement('div');
    day.className = 'cal-day' + (isToday ? ' today' : '');
    day.innerHTML = `<div class="cal-day-num">${d}</div>`;

    const tasks = taskMap[key] || [];
    tasks.slice(0, 3).forEach(t => {
      const pill = document.createElement('span');
      pill.className = `cal-task-pill cal-pill-${t.status}`;
      pill.title = `${t.title} — ${STATUS_LABEL[t.status]}`;
      pill.textContent = t.title;
      pill.addEventListener('click', () => openEditModal(t));
      day.appendChild(pill);
    });
    if (tasks.length > 3) {
      const more = document.createElement('span');
      more.className = 'cal-task-pill cal-pill-todo';
      more.style.opacity = '.7';
      more.textContent = `+${tasks.length - 3} lainnya`;
      day.appendChild(more);
    }

    grid.appendChild(day);
  }

  // Trailing blanks to fill the last row
  const totalCells = startDow + totalDays;
  const remainder  = totalCells % 7;
  if (remainder !== 0) {
    for (let d = 1; d <= 7 - remainder; d++) {
      const day = document.createElement('div');
      day.className = 'cal-day other-month';
      day.innerHTML = `<div class="cal-day-num">${d}</div>`;
      grid.appendChild(day);
    }
  }
}


// =============================================================
// VIEW: PROGRES
// =============================================================

function renderProgressView() {
  const total     = allTasks.length;
  const completed = allTasks.filter(t => t.status === 'completed').length;
  const pct       = total > 0 ? Math.round((completed / total) * 100) : 0;

  $('prog-pct').textContent = `${pct}%`;
  $('prog-bar').style.width = `${pct}%`;
  $('prog-sub').textContent  = `${completed} dari ${total} tugas selesai`;

  // Per-status breakdown
  const breakdown = $('prog-breakdown');
  breakdown.innerHTML = '';
  const statusCfg = [
    { key: 'todo',       label: 'To Do',       color: 'var(--todo-accent)'  },
    { key: 'inprogress', label: 'In Progress',  color: 'var(--inprog-accent)' },
    { key: 'completed',  label: 'Completed',   color: 'var(--done-accent)'  },
    { key: 'overdue',    label: 'Overdue',      color: 'var(--over-accent)'  },
  ];
  statusCfg.forEach(({ key, label, color }) => {
    const count = allTasks.filter(t => t.status === key).length;
    const card  = document.createElement('div');
    card.className = 'prog-stat-card';
    card.innerHTML = `
      <div class="prog-stat-num" style="color:${color}">${count}</div>
      <div class="prog-stat-lbl">${label}</div>
      <div class="prog-stat-bar" style="background:${color}"></div>
    `;
    breakdown.appendChild(card);
  });

  // Per-subject
  const subjectsEl = $('prog-subjects');
  subjectsEl.innerHTML = '';
  const subjectMap = {};
  allTasks.forEach(t => {
    const subj = t.subject || '(Tanpa Mata Kuliah)';
    if (!subjectMap[subj]) subjectMap[subj] = { total: 0, done: 0 };
    subjectMap[subj].total++;
    if (t.status === 'completed') subjectMap[subj].done++;
  });

  if (Object.keys(subjectMap).length === 0) {
    subjectsEl.innerHTML = '<p style="color:var(--text-muted);font-size:13px">Belum ada data mata kuliah.</p>';
    return;
  }

  Object.entries(subjectMap).sort((a, b) => a[0].localeCompare(b[0])).forEach(([subj, data]) => {
    const pctSubj = data.total > 0 ? Math.round((data.done / data.total) * 100) : 0;
    const row = document.createElement('div');
    row.className = 'subject-row';
    row.innerHTML = `
      <div class="subject-row-header">
        <span>${escHtml(subj)}</span>
        <span class="subject-row-count">${data.done}/${data.total} selesai &nbsp;·&nbsp; ${pctSubj}%</span>
      </div>
      <div class="progress-bar-track">
        <div class="progress-bar-fill" style="width:${pctSubj}%"></div>
      </div>
    `;
    subjectsEl.appendChild(row);
  });
}


// =============================================================
// VIEW: NOTIFIKASI
// =============================================================

function renderNotifView() {
  const list = $('notif-list');
  list.innerHTML = '';

  const today    = new Date(); today.setHours(0,0,0,0);
  const warnDays = parseInt($('pref-deadline-warn')?.value || '3', 10);
  const items    = [];

  allTasks.forEach(task => {
    if (task.status === 'overdue') {
      items.push({
        type: 'overdue',
        title: `Tugas Terlambat: ${task.title}`,
        desc: `Deadline sudah lewat${task.subject ? ` — ${task.subject}` : ''}.`,
        time: task.deadline ? formatDate(task.deadline) : '',
        taskId: task.id,
      });
    } else if (task.status !== 'completed' && task.deadline) {
      const dl   = new Date(task.deadline + 'T00:00:00');
      const diff = Math.ceil((dl - today) / (1000 * 60 * 60 * 24));
      if (diff >= 0 && diff <= warnDays) {
        items.push({
          type: 'deadline',
          title: diff === 0 ? `Deadline Hari Ini: ${task.title}` : `Deadline ${diff} Hari Lagi: ${task.title}`,
          desc: `${task.subject ? task.subject + ' — ' : ''}Deadline: ${formatDate(task.deadline)}`,
          time: formatDate(task.deadline),
          taskId: task.id,
        });
      }
    } else if (task.status === 'completed') {
      items.push({
        type: 'done',
        title: `Selesai: ${task.title}`,
        desc: task.subject || '',
        time: task.deadline ? formatDate(task.deadline) : '',
        taskId: task.id,
      });
    }
  });

  if (items.length === 0) {
    list.innerHTML = '<div class="notif-empty">Tidak ada notifikasi saat ini.</div>';
    return;
  }

  // Sort: overdue first, then deadline, then done
  const typeOrder = { overdue: 0, deadline: 1, done: 2 };
  items.sort((a, b) => typeOrder[a.type] - typeOrder[b.type]);

  items.forEach(item => {
    const el = document.createElement('div');
    const isUnread = item.type === 'overdue' || item.type === 'deadline';
    el.className = `notif-item${item.type === 'overdue' ? ' unread-danger' : isUnread ? ' unread' : ''}`;

    const iconClass = item.type === 'overdue' ? 'notif-icon-overdue'
                    : item.type === 'deadline' ? 'notif-icon-deadline'
                    : 'notif-icon-done';

    const iconSvg = item.type === 'overdue'
      ? '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>'
      : item.type === 'deadline'
      ? '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M20 3h-1V1h-2v2H7V1H5v2H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm0 18H4V8h16v13z"/></svg>'
      : '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>';

    el.innerHTML = `
      <div class="notif-icon ${iconClass}">${iconSvg}</div>
      <div class="notif-body">
        <div class="notif-title">${escHtml(item.title)}</div>
        ${item.desc ? `<div class="notif-desc">${escHtml(item.desc)}</div>` : ''}
        ${item.time ? `<div class="notif-time">${escHtml(item.time)}</div>` : ''}
      </div>
    `;
    list.appendChild(el);
  });
}



// =============================================================
// TOAST SYSTEM
// =============================================================

/**
 * Show a toast notification.
 * @param {string} message
 * @param {'success'|'error'|'info'} type
 * @param {number} [duration=3500] ms
 */
function showToast(message, type = 'info', duration = 3500) {
  const container = $('toast-container');

  const icons = {
    success: `<svg viewBox="0 0 24 24" width="16" height="16" fill="#36B37E"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`,
    error:   `<svg viewBox="0 0 24 24" width="16" height="16" fill="#FF5630"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>`,
    info:    `<svg viewBox="0 0 24 24" width="16" height="16" fill="#0052CC"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-7h2v7zm0-9h-2V7h2v1z"/></svg>`,
  };

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <span class="toast-icon">${icons[type]}</span>
    <span class="toast-msg">${escHtml(message)}</span>
  `;

  container.appendChild(toast);

  // Auto-remove
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity .3s';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}


// =============================================================
// LOADING OVERLAY
// =============================================================

/** Show or hide the full-page loading overlay. */
function showLoading(show) {
  $('loading-overlay').hidden = !show;
}


// =============================================================
// HELPERS
// =============================================================

/**
 * Format a YYYY-MM-DD date string into a localised display string.
 * @param {string} dateStr
 * @returns {string}
 */
function formatDate(dateStr) {
  if (!dateStr) return '—';
  try {
    const [y, m, d] = dateStr.split('-').map(Number);
    const date = new Date(y, m - 1, d);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
  } catch {
    return dateStr;
  }
}

/**
 * Escape HTML special characters to prevent XSS in innerHTML.
 * @param {string} str
 * @returns {string}
 */
function escHtml(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g,  '&amp;')
    .replace(/</g,  '&lt;')
    .replace(/>/g,  '&gt;')
    .replace(/"/g,  '&quot;')
    .replace(/'/g,  '&#039;');
}

/**
 * Simple debounce utility.
 * @param {Function} fn
 * @param {number}   delay ms
 */
function debounce(fn, delay) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };
}
