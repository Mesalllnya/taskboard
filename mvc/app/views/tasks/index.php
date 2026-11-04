<!-- My Tasks — Kanban Board -->
<div class="content-header">
  <div>
    <h1>My Tasks</h1>
    <p class="content-subtitle">Drag columns or use filters to manage your board</p>
  </div>
  <button class="btn-primary" id="open-create-modal">
    <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
    New Task
  </button>
</div>

<!-- Filter Bar -->
<div class="filter-bar" id="filter-bar">
  <div class="filter-group">
    <label class="filter-lbl">Project:</label>
    <select id="filter-project" class="filter-select">
      <option value="all">All Projects</option>
      <?php foreach ($projects as $p): ?>
        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="filter-group">
    <label class="filter-lbl">Priority:</label>
    <select id="filter-priority" class="filter-select">
      <option value="all">All Priority</option>
      <option value="high">High</option>
      <option value="medium">Medium</option>
      <option value="low">Low</option>
    </select>
  </div>
  <div class="filter-group">
    <label class="filter-lbl">Search:</label>
    <input type="search" id="board-search" class="search-input" style="max-width:200px"
           placeholder="Search tasks…" autocomplete="off" />
  </div>
  <button class="btn-secondary" id="filter-reset">Reset</button>
</div>

<!-- Kanban Board -->
<div class="kanban-board" id="kanban-board">

  <!-- TO DO -->
  <div class="kanban-col col-todo" data-status="todo">
    <div class="col-header">
      <span class="col-dot"></span>
      <span class="col-title">To Do</span>
      <span class="col-count" id="count-todo"><?= $counts['todo'] ?></span>
    </div>
    <div class="col-body" id="col-todo" data-status="todo"></div>
    <div class="col-footer">
      <button class="col-add-btn" data-status="todo">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        Add Task
      </button>
    </div>
  </div>

  <!-- IN PROGRESS -->
  <div class="kanban-col col-progress" data-status="inprogress">
    <div class="col-header">
      <span class="col-dot"></span>
      <span class="col-title">In Progress</span>
      <span class="col-count" id="count-inprogress"><?= $counts['inprogress'] ?></span>
    </div>
    <div class="col-body" id="col-inprogress" data-status="inprogress"></div>
    <div class="col-footer">
      <button class="col-add-btn" data-status="inprogress">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        Add Task
      </button>
    </div>
  </div>

  <!-- COMPLETED -->
  <div class="kanban-col col-done" data-status="completed">
    <div class="col-header">
      <span class="col-dot"></span>
      <span class="col-title">Completed</span>
      <span class="col-count" id="count-completed"><?= $counts['completed'] ?></span>
    </div>
    <div class="col-body" id="col-completed" data-status="completed"></div>
    <div class="col-footer">
      <button class="col-add-btn" data-status="completed">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        Add Task
      </button>
    </div>
  </div>

  <!-- OVERDUE -->
  <div class="kanban-col col-overdue" data-status="overdue">
    <div class="col-header">
      <span class="col-dot"></span>
      <span class="col-title">Overdue</span>
      <span class="col-count" id="count-overdue"><?= $counts['overdue'] ?></span>
    </div>
    <div class="col-body" id="col-overdue" data-status="overdue"></div>
    <div class="col-footer">
      <button class="col-add-btn" data-status="overdue">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        Add Task
      </button>
    </div>
  </div>

</div><!-- /kanban-board -->

<!-- Loading overlay -->
<div class="loading-overlay" id="loading-overlay" hidden>
  <div class="spinner"></div>
  <p>Loading tasks…</p>
</div>

<!-- Inline data for JS -->
<script>
  const INITIAL_PROJECTS = <?= json_encode(array_values($projects), JSON_UNESCAPED_UNICODE) ?>;
</script>
