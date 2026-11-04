<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle ?? 'TaskBoard') ?> — <?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css" />
</head>
<body>

<div class="app-shell">

  <!-- =====================================================
       SIDEBAR
  ===================================================== -->
  <aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
      <div class="logo-mark">
        <div class="logo-icon">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="#fff">
            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
          </svg>
        </div>
        <div>
          <div class="logo-text"><?= APP_NAME ?></div>
          <div class="logo-sub">Student Edition</div>
        </div>
      </div>
    </div>

    <!-- Nav -->
    <nav class="sidebar-nav-wrapper">

      <span class="sidebar-section-label">Main Menu</span>
      <ul class="sidebar-nav">

        <li class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
          <a href="<?= BASE_URL ?>/dashboard">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
              <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
            </svg>
            Dashboard
          </a>
        </li>

        <li class="<?= ($activePage ?? '') === 'tasks' ? 'active' : '' ?>">
          <a href="<?= BASE_URL ?>/tasks">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
              <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-7 3a3 3 0 1 1 0 6 3 3 0 0 1 0-6zm6 13H6v-.5c0-2 4-3.1 6-3.1s6 1.1 6 3.1V19z"/>
            </svg>
            My Tasks
            <span class="badge" id="badge-tasks"><?= array_sum($counts ?? []) ?></span>
          </a>
        </li>

        <li class="<?= ($activePage ?? '') === 'projects' ? 'active' : '' ?>">
          <a href="<?= BASE_URL ?>/projects">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
              <path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/>
            </svg>
            Projects
          </a>
        </li>

        <li class="<?= ($activePage ?? '') === 'calendar' ? 'active' : '' ?>">
          <a href="<?= BASE_URL ?>/calendar">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
              <path d="M20 3h-1V1h-2v2H7V1H5v2H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm0 18H4V8h16v13z"/>
            </svg>
            Calendar
          </a>
        </li>

        <li class="<?= ($activePage ?? '') === 'analytics' ? 'active' : '' ?>">
          <a href="<?= BASE_URL ?>/analytics">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
              <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
            </svg>
            Analytics
          </a>
        </li>

      </ul>

      <span class="sidebar-section-label" style="margin-top:16px">Workspace</span>
      <ul class="sidebar-nav">

        <li class="<?= ($activePage ?? '') === 'team' ? 'active' : '' ?>">
          <a href="<?= BASE_URL ?>/team">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
              <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
            </svg>
            Team
          </a>
        </li>

        <li class="<?= ($activePage ?? '') === 'files' ? 'active' : '' ?>">
          <a href="<?= BASE_URL ?>/files">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
              <path d="M20 6h-8l-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2z"/>
            </svg>
            Files
          </a>
        </li>

        <li class="<?= ($activePage ?? '') === 'integrations' ? 'active' : '' ?>">
          <a href="<?= BASE_URL ?>/integrations">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
              <path d="M17 7H7a5 5 0 0 0 0 10h10a5 5 0 0 0 0-10zm0 8H7a3 3 0 0 1 0-6h10a3 3 0 0 1 0 6z"/>
            </svg>
            Integrations
          </a>
        </li>

      </ul>

      <span class="sidebar-section-label" style="margin-top:16px">Account</span>
      <ul class="sidebar-nav">
        <li class="<?= ($activePage ?? '') === 'settings' ? 'active' : '' ?>">
          <a href="<?= BASE_URL ?>/settings">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
              <path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.07-.94l2.03-1.58a.49.49 0 0 0 .12-.61l-1.92-3.32a.49.49 0 0 0-.59-.22l-2.39.96a7 7 0 0 0-1.62-.94l-.36-2.54a.484.484 0 0 0-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96a.48.48 0 0 0-.59.22L2.74 8.87a.47.47 0 0 0 .12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58a.49.49 0 0 0-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32a.47.47 0 0 0-.12-.61l-2.01-1.58zM12 15.6a3.6 3.6 0 1 1 0-7.2 3.6 3.6 0 0 1 0 7.2z"/>
            </svg>
            Settings
          </a>
        </li>
      </ul>

    </nav>

    <!-- User card -->
    <div class="sidebar-user">
      <div class="avatar"><?= htmlspecialchars($_SESSION['user_init'] ?? 'U') ?></div>
      <div class="sidebar-user-info">
        <div class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></div>
        <div class="user-role">Mahasiswa Aktif</div>
      </div>
    </div>

  </aside><!-- /sidebar -->


  <!-- =====================================================
       MAIN WRAPPER
  ===================================================== -->
  <div class="main-wrapper">

    <!-- TOP BAR -->
    <header class="topbar">
      <button class="hamburger" id="hamburger-btn" aria-label="Toggle sidebar">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
          <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
        </svg>
      </button>

      <span class="topbar-title" id="topbar-title"><?= htmlspecialchars($pageTitle ?? 'TaskBoard') ?></span>

      <!-- Global Search -->
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 24 24" width="14" height="14" fill="#5E6C84">
          <path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16a6.47 6.47 0 0 0 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
        </svg>
        <input type="search" id="global-search" class="search-input"
               placeholder="Search tasks…" autocomplete="off" />
      </div>

      <div class="topbar-spacer"></div>

      <!-- Create New -->
      <button class="btn-primary" id="open-create-modal">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
          <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
        </svg>
        New Task
      </button>

      <!-- Notifications -->
      <button class="icon-btn" id="notif-toggle" aria-label="Notifications">
        <span class="notif-dot" id="notif-dot" hidden></span>
        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
          <path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 0 0 2 2zm6-6V11c0-3.07-1.63-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
        </svg>
      </button>

      <!-- Notifications Dropdown -->
      <div class="notif-dropdown" id="notif-dropdown" hidden>
        <div class="notif-dropdown-header">
          <strong>Notifications</strong>
          <button class="notif-clear-all" id="notif-clear-all">Mark all read</button>
        </div>
        <div class="notif-dropdown-body" id="notif-dropdown-body">
          <p class="notif-empty-msg">No notifications.</p>
        </div>
      </div>

      <!-- Profile -->
      <div class="profile-wrap" id="profile-wrap">
        <button class="profile-btn" id="profile-toggle" aria-label="Profile">
          <div class="avatar avatar-sm"><?= htmlspecialchars($_SESSION['user_init'] ?? 'U') ?></div>
        </button>
        <div class="profile-dropdown" id="profile-dropdown" hidden>
          <div class="profile-dropdown-header">
            <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong>
          </div>
          <a href="<?= BASE_URL ?>/settings">Settings</a>
          <a href="<?= BASE_URL ?>/auth/logout" class="logout-link">Log Out</a>
        </div>
      </div>

    </header><!-- /topbar -->

    <!-- MAIN CONTENT AREA -->
    <main class="content" id="main-content">
      <?= $content ?>
    </main>

  </div><!-- /main-wrapper -->
</div><!-- /app-shell -->


<!-- =====================================================
     MODAL — Create / Edit Task
===================================================== -->
<div class="modal-backdrop" id="modal-backdrop" hidden role="dialog" aria-modal="true" aria-labelledby="modal-title">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title" id="modal-title">New Task</h2>
      <button class="modal-close" id="modal-close">&times;</button>
    </div>
    <form id="task-form" novalidate>
      <input type="hidden" id="task-id" value="" />

      <div class="form-group">
        <label for="f-title">Title <span class="req">*</span></label>
        <input type="text" id="f-title" placeholder="e.g. Laporan Praktikum Fisika" required />
        <span class="field-error" id="err-title"></span>
      </div>

      <div class="form-group">
        <label for="f-description">Description</label>
        <textarea id="f-description" rows="3" placeholder="Optional details…"></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="f-subject">Subject / Course</label>
          <input type="text" id="f-subject" placeholder="e.g. Fisika" />
        </div>
        <div class="form-group">
          <label for="f-project">Project</label>
          <select id="f-project">
            <option value="">— No Project —</option>
            <?php foreach ($projects ?? [] as $p): ?>
              <option value="<?= $p['id'] ?>"
                      style="border-left:3px solid <?= htmlspecialchars($p['color']) ?>">
                <?= htmlspecialchars($p['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="f-deadline">Deadline <span class="req">*</span></label>
          <input type="date" id="f-deadline" required />
          <span class="field-error" id="err-deadline"></span>
        </div>
        <div class="form-group">
          <label for="f-priority">Priority</label>
          <select id="f-priority">
            <option value="high">High</option>
            <option value="medium" selected>Medium</option>
            <option value="low">Low</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="f-status">Status</label>
          <select id="f-status">
            <option value="todo">To Do</option>
            <option value="inprogress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="overdue">Overdue</option>
          </select>
        </div>
        <div class="form-group">
          <label for="f-reminder">Reminder Label</label>
          <input type="text" id="f-reminder" placeholder="Optional, e.g. 2 days left" />
        </div>
      </div>

      <div class="form-error-banner" id="form-error" hidden></div>

      <div class="modal-footer">
        <button type="button" class="btn-secondary" id="modal-cancel">Cancel</button>
        <button type="submit" class="btn-primary" id="form-submit-btn">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
            <path d="M17 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7l-4-4zm-5 16a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm3-10H5V5h10v4z"/>
          </svg>
          Save Task
        </button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL — Confirm Delete -->
<div class="modal-backdrop" id="delete-backdrop" hidden role="dialog" aria-modal="true">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h2 class="modal-title">Delete Task</h2>
      <button class="modal-close" id="delete-modal-close">&times;</button>
    </div>
    <div class="delete-body">
      <svg viewBox="0 0 24 24" width="40" height="40" fill="#FF5630">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
      </svg>
      <p>Delete task <strong id="delete-task-name"></strong>?</p>
      <p class="delete-warn">This action cannot be undone.</p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn-secondary" id="delete-cancel">Cancel</button>
      <button type="button" class="btn-danger"    id="delete-confirm">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
          <path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
        </svg>
        Delete
      </button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast-container" id="toast-container" aria-live="polite"></div>

<!-- JS -->
<script>
  const APP_BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
