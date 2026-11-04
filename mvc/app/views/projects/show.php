<div class="content-header">
  <div>
    <h1><?= htmlspecialchars($project['name']) ?></h1>
    <p class="content-subtitle"><?= htmlspecialchars($project['description'] ?? '') ?></p>
  </div>
  <a href="<?= BASE_URL ?>/projects" class="btn-secondary">← Projects</a>
</div>

<?php if (empty($tasks)): ?>
  <p class="empty-hint">No tasks in this project yet.</p>
<?php else: ?>
  <div class="tasks-list" style="margin-top:16px">
    <?php
    $statusColors = ['todo'=>'var(--todo-accent)','inprogress'=>'var(--inprog-accent)',
                     'completed'=>'var(--done-accent)','overdue'=>'var(--over-accent)'];
    foreach ($tasks as $t): ?>
      <div class="task-row">
        <span class="task-row-status-dot" style="background:<?= $statusColors[$t['status']] ?? '#ccc' ?>"></span>
        <div class="task-row-main">
          <div class="task-row-title<?= $t['status']==='completed' ? ' done-strike' : '' ?>"><?= htmlspecialchars($t['title']) ?></div>
          <div class="task-row-meta">
            <?php if($t['subject']): ?><span><?= htmlspecialchars($t['subject']) ?></span><?php endif; ?>
            <span>📅 <?= htmlspecialchars($t['deadline']) ?></span>
            <span class="priority-pill priority-<?= $t['priority'] ?>"><span class="priority-dot"></span><?= ucfirst($t['priority']) ?></span>
          </div>
        </div>
        <span class="status-chip status-<?= $t['status'] ?>"><?= $t['status'] ?></span>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
