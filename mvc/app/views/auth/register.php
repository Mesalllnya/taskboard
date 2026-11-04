<div class="auth-card">
  <div class="auth-logo">
    <div class="logo-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="#fff"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg></div>
    <span class="auth-brand">TaskBoard</span>
  </div>
  <h1 class="auth-title">Create account</h1>
  <p class="auth-sub">Start managing your student tasks</p>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= BASE_URL ?>/auth/register" class="auth-form">
    <div class="form-group">
      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" required
             value="<?= htmlspecialchars($old['name'] ?? '') ?>" placeholder="Ahmad Rizky" />
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required
             value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="ahmad@example.com" />
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="Min. 8 chars" />
      </div>
      <div class="form-group">
        <label for="confirm">Confirm</label>
        <input type="password" id="confirm" name="confirm" required placeholder="Repeat password" />
      </div>
    </div>
    <button type="submit" class="btn-primary btn-full">Create Account</button>
  </form>
  <p class="auth-switch">Already have an account?
    <a href="<?= BASE_URL ?>/auth/login">Sign In</a>
  </p>
</div>
