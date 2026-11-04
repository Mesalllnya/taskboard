<?php
$total    = array_sum($counts);
$pct      = $total > 0 ? round($counts['completed'] / $total * 100) : 0;
$priorityColors = ['high' => 'var(--pri-high)', 'medium' => 'var(--pri-med)', 'low' => 'var(--pri-low)'];
?>
<!-- Analytics -->
<div class="content-header">
  <div><h1>Analytics</h1><p class="content-subtitle">Task performance overview</p></div>
</div>

<!-- KPI row -->
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

<div class="analytics-grid">

  <!-- Completion rate -->
  <div class="dash-card">
    <div class="dash-card-title">Completion Rate</div>
    <div class="progress-pct-big" style="font-size:36px"><?= $pct ?>%</div>
    <div class="progress-bar-track" style="margin:12px 0 8px">
      <div class="progress-bar-fill" style="width:<?= $pct ?>%"></div>
    </div>
    <p class="progress-card-sub"><?= $counts['completed'] ?> of <?= $total ?> tasks completed</p>
  </div>

  <!-- Priority breakdown -->
  <div class="dash-card">
    <div class="dash-card-title">By Priority</div>
    <?php foreach ($priorities as $p): ?>
      <?php $pp = $p['total'] > 0 ? round($p['done'] / $p['total'] * 100) : 0; ?>
      <div class="analytics-row">
        <span class="analytics-label">
          <span class="priority-dot" style="background:<?= $priorityColors[$p['priority']] ?? '#ccc' ?>"></span>
          <?= ucfirst($p['priority']) ?>
        </span>
        <div class="progress-bar-track analytics-bar">
          <div class="progress-bar-fill"
               style="width:<?= $pp ?>%;background:<?= $priorityColors[$p['priority']] ?? '#ccc' ?>"></div>
        </div>
        <span class="analytics-val"><?= $p['total'] ?></span>
      </div>
    <?php endforeach; ?>
    <?php if (empty($priorities)): ?><p class="empty-hint">No data.</p><?php endif; ?>
  </div>

  <!-- Per subject -->
  <div class="dash-card" style="grid-column:1/-1">
    <div class="dash-card-title">By Subject / Course</div>
    <?php if (empty($subjects)): ?>
      <p class="empty-hint">No subject data yet.</p>
    <?php else: ?>
      <table class="task-table">
        <thead><tr><th>Subject</th><th>Total</th><th>Done</th><th>Progress</th></tr></thead>
        <tbody>
          <?php foreach ($subjects as $s): ?>
            <?php $sp = $s['total'] > 0 ? round($s['done'] / $s['total'] * 100) : 0; ?>
            <tr>
              <td><?= htmlspecialchars($s['subject']) ?></td>
              <td><?= $s['total'] ?></td>
              <td><?= $s['done'] ?></td>
              <td>
                <div class="progress-bar-track" style="min-width:100px">
                  <div class="progress-bar-fill" style="width:<?= $sp ?>%"></div>
                </div>
                <span style="font-size:11px;color:var(--text-muted)"><?= $sp ?>%</span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</div>
