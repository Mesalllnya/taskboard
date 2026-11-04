<!-- Settings -->
<div class="content-header">
  <div><h1>Settings</h1><p class="content-subtitle">Manage your profile and preferences</p></div>
</div>

<?php if (isset($_GET['saved'])): ?>
  <div class="alert alert-success" style="margin-bottom:16px">Profile saved successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['pwd_saved'])): ?>
  <div class="alert alert-success" style="margin-bottom:16px">Password updated successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['pwd_error'])): ?>
  <div class="alert alert-error" style="margin-bottom:16px">
    <?= ($_GET['pwd_error'] == 1) ? 'Current password is incorrect.' : 'New passwords do not match or are too short.' ?>
  </div>
<?php endif; ?>

<div class="settings-grid">

  <!-- Profile Card -->
  <div class="settings-card">
    <h2 class="settings-card-title">Profile</h2>
    <form method="POST" action="<?= BASE_URL ?>/settings/profile" class="settings-form">
      <div class="form-group">
        <label for="s-name">Full Name</label>
        <input type="text" id="s-name" name="name" required
               value="<?= htmlspecialchars($userRecord['name'] ?? '') ?>" />
      </div>
      <div class="form-group">
        <label for="s-email">Email (read-only)</label>
        <input type="email" id="s-email" value="<?= htmlspecialchars($userRecord['email'] ?? '') ?>" disabled />
      </div>
      <div class="form-group">
        <label for="s-semester">Semester</label>
        <input type="text" id="s-semester" name="semester"
               value="<?= htmlspecialchars($userRecord['semester'] ?? '') ?>"
               placeholder="e.g. Ganjil 2025/2026" />
      </div>
      <div class="form-group">
        <label for="s-major">Program / Major</label>
        <input type="text" id="s-major" name="major"
               value="<?= htmlspecialchars($userRecord['major'] ?? '') ?>"
               placeholder="e.g. Teknik Informatika" />
      </div>
      <button type="submit" class="btn-primary">Save Profile</button>
    </form>
  </div>

  <!-- Password Card -->
  <div class="settings-card">
    <h2 class="settings-card-title">Change Password</h2>
    <form method="POST" action="<?= BASE_URL ?>/settings/password" class="settings-form">
      <div class="form-group">
        <label for="s-cur">Current Password</label>
        <input type="password" id="s-cur" name="current_password" required />
      </div>
      <div class="form-group">
        <label for="s-new">New Password</label>
        <input type="password" id="s-new" name="new_password" required placeholder="Min. 8 characters" />
      </div>
      <div class="form-group">
        <label for="s-conf">Confirm New Password</label>
        <input type="password" id="s-conf" name="confirm_password" required />
      </div>
      <button type="submit" class="btn-primary">Update Password</button>
    </form>
  </div>

  <!-- Account info -->
  <div class="settings-card">
    <h2 class="settings-card-title">Account Info</h2>
    <dl class="settings-dl">
      <dt>Member Since</dt>
      <dd><?= date('d M Y', strtotime($userRecord['created_at'] ?? 'now')) ?></dd>
      <dt>Role</dt>
      <dd><?= ucfirst($userRecord['role'] ?? 'student') ?></dd>
    </dl>
  </div>

  <!-- Danger Zone -->
  <div class="settings-card settings-danger-card">
    <h2 class="settings-card-title settings-danger-title">Danger Zone</h2>
    <p class="settings-danger-desc">Irreversible destructive actions.</p>
    <button class="btn-danger" id="export-btn">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
        <path d="M19 9h-4V3H9v6H5l7 7 7-7zm-8 2V5h2v6h1.17L12 13.17 9.83 11H11zm-6 7h14v2H5z"/>
      </svg>
      Export My Tasks (JSON)
    </button>
    <a class="btn-secondary" href="<?= BASE_URL ?>/auth/logout" style="display:inline-flex;align-items:center;gap:6px;margin-left:10px">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5-5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
      </svg>
      Log Out
    </a>
  </div>

</div>

<script>
document.getElementById('export-btn')?.addEventListener('click', async () => {
  try {
    const res  = await fetch(APP_BASE_URL + '/api/tasks');
    const json = await res.json();
    const blob = new Blob([JSON.stringify(json.data, null, 2)], {type:'application/json'});
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = 'taskboard-export.json'; a.click();
    URL.revokeObjectURL(url);
  } catch (e) { alert('Export failed: ' + e.message); }
});
</script>
