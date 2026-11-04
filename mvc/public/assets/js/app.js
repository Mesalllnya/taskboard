/**
 * app.js — TaskBoard MVC
 * Fetch API CRUD + Kanban board + Notifications + UI helpers
 *
 * Requires:  APP_BASE_URL (injected by PHP in layouts/main.php)
 *            INITIAL_PROJECTS (injected by views/tasks/index.php on Kanban page)
 */

'use strict';

// =============================================================
// CONFIG / CONSTANTS
// =============================================================
const API = APP_BASE_URL;

const PRIORITY_LABEL = { high: 'High', medium: 'Medium', low: 'Low' };
const STATUS_LABEL   = { todo: 'To Do', inprogress: 'In Progress', completed: 'Completed', overdue: 'Overdue' };

const STATUS_COLORS = {
  todo:        'var(--todo-accent)',
  inprogress:  'var(--inprog-accent)',
  completed:   'var(--done-accent)',
  overdue:     'var(--over-accent)',
};

// =============================================================
// STATE
// =============================================================
let allTasks       = [];
let deleteTargetId = null;

// =============================================================
// DOM HELPERS
// =============================================================
const $ = id => document.getElementById(id);
const colEl  = {
  todo:        $('col-todo'),
  inprogress:  $('col-inprogress'),
  completed:   $('col-completed'),
  overdue:     $('col-overdue'),
};
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
  initUI();
  // Only run Kanban logic on the /tasks page
  if ($('kanban-board')) {
    loadTasks();
    initKanbanListeners();
  }
  initModalListeners();
  initTopbarListeners();
  loadNotifications();
});

// =============================================================
// API LAYER (Fetch wrappers)
// =============================================================

async function apiFetch(url, options = {}) {
  const res  = await fetch(url, options);
  const json = await res.json();
  if (!json.success) throw new Error(json.message || 'API error');
  return json;
}

async function loadTasks() {
  showLoading(true);
  try {
    const params = buildFilterParams();
    const json   = await apiFetch(`${API}/api/tasks?${params}`);
    allTasks = json.data || [];
    renderBoard();
  } catch (err) {
    showToast(err.message, 'error');
  } finally {
    showLoading(false);
  }
}

async function createTask(payload) {
  return apiFetch(`${API}/api/tasks`, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(payload),
  });
}

async function updateTask(id, payload) {
  return apiFetch(`${API}/api/tasks/${id}`, {
    method:  'PUT',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify(payload),
  });
}

async function updateStatus(id, status) {
  return apiFetch(`${API}/api/tasks/${id}/status`, {
    method:  'PUT',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ status }),
  });
}

async function deleteTask(id) {
  return apiFetch(`${API}/api/tasks/${id}`, { method: 'DELETE' });
}

// =============================================================
// BOARD RENDERING
// =============================================================

function renderBoard() {
  if (!$('kanban-board')) return;

  // Clear columns
  Object.values(colEl).forEach(col => { if (col) col.innerHTML = ''; });

  const counts = { todo: 0, inprogress: 0, completed: 0, overdue: 0 };

  allTasks.forEach(task => {
    const status = task.status;
    if (!colEl[status]) return;
    counts[status]++;
    colEl[status].appendChild(buildCard(task));
  });

  // Update column counts
  Object.keys(counts).forEach(s => {
    if (countEl[s]) countEl[s].textContent = counts[s];
  });

  // Empty placeholders
  Object.entries(colEl).forEach(([, col]) => {
    if (col && col.children.length === 0) {
      const emp = document.createElement('div');
      emp.className   = 'col-empty';
      emp.textContent = 'No tasks here';
      col.appendChild(emp);
    }
  });
}

// =============================================================
// CARD BUILDER
// =============================================================

function buildCard(task) {
  const card = document.createElement('article');
  card.className   = 'task-card';
  card.dataset.id  = task.id;
  card.setAttribute('aria-label', `Task: ${task.title}`);

  // Reminder / done stamp
  let footerRight = '';
  if (task.status === 'completed') {
    footerRight = `<span class="done-stamp">
      <svg viewBox="0 0 24 24" width="11" height="11" fill="currentColor">
        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
      </svg>
      Done
    </span>`;
  } else if (task.reminder) {
    footerRight = `<span class="reminder-label">
      <svg viewBox="0 0 24 24" width="11" height="11" fill="currentColor">
        <path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 0 0 2 2zm6-6V11c0-3.07-1.63-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
      </svg>
      ${escHtml(task.reminder)}
    </span>`;
  }

  // Project dot
  let projectTag = '';
  if (task.project_name) {
    const color = escHtml(task.project_color || '#0052CC');
    projectTag = `<span class="card-project">
      <span class="card-project-dot" style="background:${color}"></span>
      ${escHtml(task.project_name)}
    </span>`;
  }

  // Status change submenu
  const statusOptions = Object.entries(STATUS_LABEL)
    .filter(([k]) => k !== task.status)
    .map(([k, v]) => `<button data-set-status="${k}" data-id="${task.id}">→ ${v}</button>`)
    .join('');

  card.innerHTML = `
    <div class="card-top">
      <span class="card-title">${escHtml(task.title)}</span>
      <div class="card-menu-wrap">
        <button class="card-menu-btn" aria-label="Task menu">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
          </svg>
        </button>
        <div class="card-dropdown">
          <button class="edit-btn" data-id="${task.id}">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor">
              <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm21.41-12.41a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
            </svg>
            Edit
          </button>
          <div class="dropdown-divider"></div>
          <div class="dropdown-status-label">Move to</div>
          ${statusOptions}
          <div class="dropdown-divider"></div>
          <button class="delete-btn danger" data-id="${task.id}" data-title="${escHtml(task.title)}">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor">
              <path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
            </svg>
            Delete
          </button>
        </div>
      </div>
    </div>

    ${task.subject ? `<span class="card-tag">${escHtml(task.subject)}</span>` : ''}
    ${projectTag}

    ${task.deadline ? `
    <div class="card-deadline">
      <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor">
        <path d="M20 3h-1V1h-2v2H7V1H5v2H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm0 18H4V8h16v13z"/>
      </svg>
      ${formatDate(task.deadline)}
    </div>` : ''}

    <div class="card-footer">
      <span class="priority-pill priority-${task.priority}">
        <span class="priority-dot"></span>
        ${PRIORITY_LABEL[task.priority] || task.priority}
      </span>
      ${footerRight}
    </div>
  `;

  // ── Event listeners ──────────────────────────────────────────

  // Toggle dropdown
  card.querySelector('.card-menu-btn').addEventListener('click', e => {
    e.stopPropagation();
    closeAllDropdowns(card);
    card.querySelector('.card-dropdown').classList.toggle('open');
  });

  // Edit
  card.querySelector('.edit-btn').addEventListener('click', () => {
    const t = allTasks.find(x => x.id === task.id);
    if (t) openEditModal(t);
  });

  // Move to status
  card.querySelectorAll('[data-set-status]').forEach(btn => {
    btn.addEventListener('click', () => handleStatusChange(task.id, btn.dataset.setStatus));
  });

  // Delete
  card.querySelector('.delete-btn').addEventListener('click', () => {
    const t = allTasks.find(x => x.id === task.id);
    if (t) openDeleteModal(t);
  });

  return card;
}

// =============================================================
// FILTER / SEARCH
// =============================================================

function buildFilterParams() {
  const params = new URLSearchParams();
  const proj = $('filter-project')?.value;
  const pri  = $('filter-priority')?.value;
  const q    = $('board-search')?.value || $('global-search')?.value;
  if (proj && proj !== 'all') params.set('project',  proj);
  if (pri  && pri  !== 'all') params.set('priority', pri);
  if (q    && q.trim())       params.set('search',   q.trim());
  return params.toString();
}

function initKanbanListeners() {
  // Filter / search controls
  ['filter-project', 'filter-priority'].forEach(id => {
    $( id)?.addEventListener('change', () => loadTasks());
  });

  const boardSearch = $('board-search');
  if (boardSearch) {
    boardSearch.addEventListener('input', debounce(() => loadTasks(), 280));
  }

  const globalSearch = $('global-search');
  if (globalSearch) {
    globalSearch.addEventListener('input', debounce(() => {
      if ($('kanban-board')) loadTasks();
    }, 280));
  }

  // Reset filters
  $('filter-reset')?.addEventListener('click', () => {
    const fp = $('filter-project'); if (fp) fp.value = 'all';
    const pri = $('filter-priority'); if (pri) pri.value = 'all';
    const bs = $('board-search'); if (bs) bs.value = '';
    loadTasks();
  });

  // Column "Add Task" buttons
  document.querySelectorAll('.col-add-btn').forEach(btn => {
    btn.addEventListener('click', () => openCreateModal(btn.dataset.status));
  });
}

// =============================================================
// STATUS CHANGE
// =============================================================

async function handleStatusChange(id, newStatus) {
  closeAllDropdowns();
  try {
    const json = await updateStatus(id, newStatus);
    // Update in-memory and re-render
    const idx = allTasks.findIndex(t => t.id === id);
    if (idx >= 0) allTasks[idx] = json.data;
    renderBoard();
    showToast(`Moved to ${STATUS_LABEL[newStatus]}.`, 'success');
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// =============================================================
// MODAL — CREATE / EDIT
// =============================================================

function openCreateModal(preStatus = 'todo') {
  $('modal-title').textContent = 'New Task';
  resetForm();
  $('f-status').value = preStatus;
  showModal(true);
}

function openEditModal(task) {
  $('modal-title').textContent = 'Edit Task';
  $('task-id').value       = task.id;
  $('f-title').value       = task.title       || '';
  $('f-description').value = task.description || '';
  $('f-subject').value     = task.subject     || '';
  $('f-deadline').value    = task.deadline    || '';
  $('f-priority').value    = task.priority    || 'medium';
  $('f-status').value      = task.status      || 'todo';
  $('f-reminder').value    = task.reminder    || '';
  if ($('f-project')) $('f-project').value = task.project_id || '';
  showModal(true);
}

function showModal(show) {
  const el = $('modal-backdrop');
  show ? el.removeAttribute('hidden') : el.setAttribute('hidden', '');
  if (show) $('f-title').focus();
}

function resetForm() {
  $('task-id').value       = '';
  $('f-title').value       = '';
  $('f-description').value = '';
  $('f-subject').value     = '';
  $('f-deadline').value    = '';
  $('f-priority').value    = 'medium';
  $('f-status').value      = 'todo';
  $('f-reminder').value    = '';
  if ($('f-project')) $('f-project').value = '';
  clearFormErrors();
}

function clearFormErrors() {
  ['f-title','f-deadline'].forEach(id => {
    const el = $(id); if (el) el.classList.remove('invalid');
  });
  const ee = $('err-title');    if (ee) ee.textContent = '';
  const ed = $('err-deadline'); if (ed) ed.textContent = '';
  const eb = $('form-error');   if (eb) eb.setAttribute('hidden', '');
}

function showFormError(msg) {
  const el = $('form-error');
  if (!el) return;
  el.textContent = msg;
  el.removeAttribute('hidden');
}

function validateForm() {
  clearFormErrors();
  let ok = true;
  const title    = $('f-title');
  const deadline = $('f-deadline');
  if (!title.value.trim()) {
    title.classList.add('invalid');
    const e = $('err-title'); if (e) e.textContent = 'Title is required.';
    ok = false;
  }
  if (!deadline.value) {
    deadline.classList.add('invalid');
    const e = $('err-deadline'); if (e) e.textContent = 'Deadline is required.';
    ok = false;
  }
  return ok;
}

// =============================================================
// MODAL — CONFIRM DELETE
// =============================================================

function openDeleteModal(task) {
  deleteTargetId = task.id;
  const el = $('delete-task-name'); if (el) el.textContent = task.title;
  $('delete-backdrop').removeAttribute('hidden');
}

function closeDeleteModal() {
  deleteTargetId = null;
  $('delete-backdrop').setAttribute('hidden', '');
}

// =============================================================
// EVENT LISTENERS — MODALS & TOPBAR
// =============================================================

function initModalListeners() {
  // "New Task" buttons (main topbar + layout)
  document.querySelectorAll('#open-create-modal').forEach(btn => {
    btn.addEventListener('click', () => openCreateModal());
  });

  $('modal-close')  ?.addEventListener('click',  () => showModal(false));
  $('modal-cancel') ?.addEventListener('click',  () => showModal(false));
  $('modal-backdrop')?.addEventListener('click', e => {
    if (e.target === $('modal-backdrop')) showModal(false);
  });

  $('delete-modal-close')?.addEventListener('click', closeDeleteModal);
  $('delete-cancel')     ?.addEventListener('click', closeDeleteModal);
  $('delete-backdrop')   ?.addEventListener('click', e => {
    if (e.target === $('delete-backdrop')) closeDeleteModal();
  });

  // Delete confirm
  $('delete-confirm')?.addEventListener('click', async () => {
    if (!deleteTargetId) return;
    const id = deleteTargetId;
    closeDeleteModal();
    try {
      await deleteTask(id);
      allTasks = allTasks.filter(t => t.id !== id);
      renderBoard();
      showToast('Task deleted.', 'success');
    } catch (err) {
      showToast(err.message, 'error');
    }
  });

  // Form submit (create / update)
  $('task-form')?.addEventListener('submit', async e => {
    e.preventDefault();
    if (!validateForm()) return;

    const btn = $('form-submit-btn');
    if (btn) btn.disabled = true;

    const id = $('task-id')?.value || '';
    const payload = {
      project_id:  $('f-project')?.value  || null,
      title:       $('f-title')?.value     || '',
      description: $('f-description')?.value || '',
      subject:     $('f-subject')?.value   || '',
      deadline:    $('f-deadline')?.value  || '',
      priority:    $('f-priority')?.value  || 'medium',
      status:      $('f-status')?.value    || 'todo',
      reminder:    $('f-reminder')?.value  || '',
    };
    if (!payload.project_id) payload.project_id = null;

    try {
      let json;
      if (id) {
        json = await updateTask(parseInt(id, 10), payload);
        const idx = allTasks.findIndex(t => t.id === parseInt(id, 10));
        if (idx >= 0) allTasks[idx] = json.data;
        showToast('Task updated.', 'success');
      } else {
        json = await createTask(payload);
        allTasks.push(json.data);
        showToast('Task created.', 'success');
      }
      showModal(false);
      renderBoard();
    } catch (err) {
      showFormError(err.message);
    } finally {
      if (btn) btn.disabled = false;
    }
  });

  // Keyboard Escape
  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    if ($('modal-backdrop')  && !$('modal-backdrop').hidden)  showModal(false);
    if ($('delete-backdrop') && !$('delete-backdrop').hidden) closeDeleteModal();
    closeAllDropdowns();
  });
}

// =============================================================
// TOPBAR — HAMBURGER, NOTIFICATIONS, PROFILE
// =============================================================

function initTopbarListeners() {
  // Hamburger
  $('hamburger-btn')?.addEventListener('click', () => {
    $('sidebar')?.classList.toggle('sidebar-open');
  });

  // Close sidebar on outside click (mobile)
  document.addEventListener('click', e => {
    const sidebar = $('sidebar');
    if (sidebar?.classList.contains('sidebar-open') &&
        !sidebar.contains(e.target) &&
        e.target !== $('hamburger-btn')) {
      sidebar.classList.remove('sidebar-open');
    }
  });

  // Notification dropdown toggle
  $('notif-toggle')?.addEventListener('click', e => {
    e.stopPropagation();
    closeAllDropdowns();
    $('notif-dropdown')?.removeAttribute('hidden');
  });

  // Mark all read
  $('notif-clear-all')?.addEventListener('click', async () => {
    try {
      await apiFetch(`${API}/api/notifications/read-all`, { method: 'PUT' });
      loadNotifications();
    } catch {}
  });

  // Profile dropdown
  $('profile-toggle')?.addEventListener('click', e => {
    e.stopPropagation();
    closeAllDropdowns();
    $('profile-dropdown')?.removeAttribute('hidden');
  });

  // Close all dropdowns on outside click
  document.addEventListener('click', () => closeAllDropdowns());

  // Close all card dropdowns on outside click
  document.addEventListener('click', () => closeAllDropdowns());
}

// =============================================================
// NOTIFICATIONS
// =============================================================

async function loadNotifications() {
  try {
    const json = await apiFetch(`${API}/api/notifications`);
    renderNotifications(json.data || [], json.unread || 0);
  } catch {}
}

function renderNotifications(items, unreadCount) {
  const dot   = $('notif-dot');
  const body  = $('notif-dropdown-body');
  const badge = $('badge-notif');

  if (dot)   unreadCount > 0 ? dot.removeAttribute('hidden') : dot.setAttribute('hidden', '');
  if (badge) badge.textContent = unreadCount > 0 ? unreadCount : '';

  if (!body) return;
  if (!items.length) {
    body.innerHTML = '<p class="notif-empty-msg">No notifications.</p>';
    return;
  }
  body.innerHTML = items.map(n => `
    <div class="notif-dd-item ${n.is_read ? '' : (n.type === 'overdue' ? 'danger' : 'unread')}">
      <div>
        <div class="notif-dd-title">${escHtml(n.message)}</div>
        <div class="notif-dd-sub">${formatDate(n.created_at?.split(' ')[0] || '')}</div>
      </div>
    </div>
  `).join('');
}

// =============================================================
// DROPDOWN MANAGEMENT
// =============================================================

function closeAllDropdowns(exceptCard = null) {
  document.querySelectorAll('.card-dropdown.open').forEach(d => {
    if (exceptCard && exceptCard.contains(d)) return;
    d.classList.remove('open');
  });
  if (!exceptCard) {
    $('notif-dropdown')   ?.setAttribute('hidden', '');
    $('profile-dropdown') ?.setAttribute('hidden', '');
  }
}

// =============================================================
// TOAST
// =============================================================

function showToast(message, type = 'info', duration = 3500) {
  const container = $('toast-container');
  if (!container) return;
  const icons = {
    success: '<svg viewBox="0 0 24 24" width="16" height="16" fill="var(--pri-low)"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>',
    error:   '<svg viewBox="0 0 24 24" width="16" height="16" fill="var(--pri-high)"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>',
    info:    '<svg viewBox="0 0 24 24" width="16" height="16" fill="var(--accent)"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-7h2v7zm0-9h-2V7h2v1z"/></svg>',
  };
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<span class="toast-icon">${icons[type] || icons.info}</span>
                     <span class="toast-msg">${escHtml(message)}</span>`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), duration);
}

// =============================================================
// LOADING OVERLAY
// =============================================================

function showLoading(show) {
  const el = $('loading-overlay');
  if (!el) return;
  show ? el.removeAttribute('hidden') : el.setAttribute('hidden', '');
}

// =============================================================
// UTILITY FUNCTIONS
// =============================================================

function formatDate(dateStr) {
  if (!dateStr || dateStr.length < 10) return dateStr || '';
  const [y, m, d] = dateStr.substring(0, 10).split('-');
  const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return `${parseInt(d, 10)} ${months[parseInt(m, 10) - 1]} ${y}`;
}

function escHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function debounce(fn, delay) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };
}
