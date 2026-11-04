<?php
// helpers
$pct = $counts['todo'] + $counts['inprogress'] + $counts['completed'] + $counts['overdue'];
$pct = $pct > 0 ? round($counts['completed'] / $pct * 100) : 0;
?>
<!-- Dashboard -->
<div class="content-header">
  <div>
    <h1>Dashboard</h1>
    <p class="content-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?> 👋 — <?= $active ?> active tasks</p>
  </div>
  <button class="btn-primary" id="open-create-modal">
    <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
    New Task
  </button>
</div>

<!-- Stats -->
<div class="stats-row">
  <div class="stat-card stat-todo">
    <div class="stat-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="#42526E"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg></div>
    <div><div class="stat-num"><?= $counts['todo'] ?></div><div class="stat-lbl">To Do</div></div>
  </div>
  <div class="stat-card stat-prog">
    <div class="stat-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="#0052CC"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/></svg></div>
    <div><div class="stat-num"><?= $counts['inprogress'] ?></div><div class="stat-lbl">In Progress</div></div>
  </div>
  <div class="stat-card stat-done">
    <div class="stat-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="#006644"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg></div>
    <div><div class="stat-num"><?= $counts['completed'] ?></div><div class="stat-lbl">Completed</div></div>
  </div>
  <div class="stat-card stat-over">
    <div class="stat-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="#BF2600"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg></div>
    <div><div class="stat-num"><?= $counts['overdue'] ?></div><div class="stat-lbl">Overdue</div></div>
  </div>
</div>

<!-- Progress + Projects row -->
<div class="dash-grid">

  <!-- Completion progress -->
  <div class="dash-card dash-progress-card">
    <div class="dash-card-title">Overall Completion</div>
    <div class="progress-pct-big"><?= $pct ?>%</div>
    <div class="progress-bar-track" style="margin:10px 0 6px">
      <div class="progress-bar-fill" style="width:<?= $pct ?>%"></div>
    </div>
    <p class="progress-card-sub"><?= $counts['completed'] ?> of <?= $total ?> tasks completed</p>
  </div>

  <!-- Projects summary -->
  <div class="dash-card">
    <div class="dash-card-title">Projects
      <a href="<?= BASE_URL ?>/projects" class="dash-see-all">See all →</a>
    </div>
    <?php if (empty($projects)): ?>
      <p class="empty-hint">No projects yet. <a href="<?= BASE_URL ?>/projects">Create one</a>.</p>
    <?php else: ?>
      <ul class="proj-mini-list">
        <?php foreach (array_slice($projects, 0, 4) as $p): ?>
          <li class="proj-mini-item">
            <span class="proj-color-dot" style="background:<?= htmlspecialchars($p['color']) ?>"></span>
            <a href="<?= BASE_URL ?>/projects/<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
            <span class="proj-mini-count"><?= $p['done_count'] ?>/<?= $p['task_count'] ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

</div>

<!-- Recent Tasks -->
<div class="dash-card" style="margin-top:18px">
  <div class="dash-card-title">
    Upcoming Tasks
    <a href="<?= BASE_URL ?>/tasks" class="dash-see-all">View Kanban →</a>
  </div>
  <?php if (empty($recentTasks)): ?>
    <p class="empty-hint">No upcoming tasks.</p>
  <?php else: ?>
    <table class="task-table">
      <thead>
        <tr>
          <th>Title</th><th>Subject</th><th>Deadline</th><th>Priority</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentTasks as $t): ?>
          <tr>
            <td><?= htmlspecialchars($t['title']) ?></td>
            <td><?= htmlspecialchars($t['subject'] ?? '—') ?></td>
            <td><?= htmlspecialchars($t['deadline']) ?></td>
            <td><span class="priority-pill priority-<?= $t['priority'] ?>">
              <span class="priority-dot"></span>
              <?= ucfirst($t['priority']) ?>
            </span></td>
            <td><span class="status-chip status-<?= $t['status'] ?>"><?= htmlspecialchars($t['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
