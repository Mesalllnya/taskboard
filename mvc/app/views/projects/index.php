<!-- Projects -->
<div class="content-header">
  <div>
    <h1>Projects</h1>
    <p class="content-subtitle"><?= count($projects) ?> active project<?= count($projects) !== 1 ? 's' : '' ?></p>
  </div>
  <button class="btn-primary" id="open-project-modal">
    <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
    New Project
  </button>
</div>

<?php if (empty($projects)): ?>
  <div class="empty-state">
    <svg viewBox="0 0 24 24" width="48" height="48" fill="#DFE1E6">
      <path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/>
    </svg>
    <h3>No projects yet</h3>
    <p>Create a project to group your tasks.</p>
    <button class="btn-primary" id="open-project-modal2">Create Project</button>
  </div>
<?php else: ?>
  <div class="projects-grid" id="projects-grid">
    <?php foreach ($projects as $p): ?>
      <?php
        $total = (int)$p['task_count'];
        $done  = (int)$p['done_count'];
        $pct   = $total > 0 ? round($done / $total * 100) : 0;
      ?>
      <div class="project-card" style="--proj-color:<?= htmlspecialchars($p['color']) ?>">
        <div class="project-card-top">
          <div class="project-color-bar"></div>
          <div class="project-card-menu">
            <button class="card-menu-btn proj-menu-btn" data-id="<?= $p['id'] ?>">⋯</button>
            <div class="card-dropdown proj-dropdown" id="proj-drop-<?= $p['id'] ?>">
              <button data-edit-proj="<?= $p['id'] ?>">Edit</button>
              <div class="dropdown-divider"></div>
              <button data-del-proj="<?= $p['id'] ?>" class="danger">Archive</button>
            </div>
          </div>
        </div>
        <h3 class="project-name">
          <a href="<?= BASE_URL ?>/projects/<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
        </h3>
        <?php if ($p['description']): ?>
          <p class="project-desc"><?= htmlspecialchars($p['description']) ?></p>
        <?php endif; ?>
        <div class="project-meta">
          <span><?= $total ?> task<?= $total !== 1 ? 's' : '' ?></span>
          <span><?= $done ?> done</span>
        </div>
        <div class="progress-bar-track" style="margin-top:10px">
          <div class="progress-bar-fill" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($p['color']) ?>"></div>
        </div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px"><?= $pct ?>% complete</div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Create / Edit Project Modal -->
<div class="modal-backdrop" id="project-modal" hidden>
  <div class="modal modal-sm">
    <div class="modal-header">
      <h2 class="modal-title" id="proj-modal-title">New Project</h2>
      <button class="modal-close" id="proj-modal-close">&times;</button>
    </div>
    <form id="project-form" method="POST">
      <input type="hidden" name="_method" id="proj-method" value="POST" />
      <div style="padding:18px 20px;display:flex;flex-direction:column;gap:14px">
        <div class="form-group">
          <label for="proj-name">Project Name *</label>
          <input type="text" id="proj-name" name="name" required />
        </div>
        <div class="form-group">
          <label for="proj-desc">Description</label>
          <textarea id="proj-desc" name="description" rows="2"></textarea>
        </div>
        <div class="form-group">
          <label for="proj-color">Color</label>
          <input type="color" id="proj-color" name="color" value="#0052CC" style="height:36px;width:60px;padding:2px;cursor:pointer" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" id="proj-modal-cancel">Cancel</button>
        <button type="submit" class="btn-primary">Save Project</button>
      </div>
    </form>
  </div>
</div>

<script>
// Project modal logic
const projModal = document.getElementById('project-modal');
document.querySelectorAll('#open-project-modal,#open-project-modal2').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('proj-modal-title').textContent = 'New Project';
    document.getElementById('project-form').action = APP_BASE_URL + '/projects';
    document.getElementById('proj-method').value   = 'POST';
    projModal.removeAttribute('hidden');
  });
});
document.getElementById('proj-modal-close').addEventListener('click',  () => projModal.setAttribute('hidden',''));
document.getElementById('proj-modal-cancel').addEventListener('click', () => projModal.setAttribute('hidden',''));

// Project menu buttons
document.querySelectorAll('.proj-menu-btn').forEach(btn => {
  btn.addEventListener('click', e => {
    e.stopPropagation();
    document.querySelectorAll('.proj-dropdown').forEach(d => d.classList.remove('open'));
    document.getElementById('proj-drop-' + btn.dataset.id).classList.toggle('open');
  });
});
document.addEventListener('click', () => document.querySelectorAll('.proj-dropdown').forEach(d => d.classList.remove('open')));

// Archive
document.querySelectorAll('[data-del-proj]').forEach(btn => {
  btn.addEventListener('click', () => {
    if (confirm('Archive this project?')) {
      const f = document.createElement('form');
      f.method = 'POST';
      f.action = APP_BASE_URL + '/projects/' + btn.dataset.delProj;
      f.innerHTML = '<input type="hidden" name="_method" value="DELETE" />';
      document.body.appendChild(f);
      f.submit();
    }
  });
});
</script>
