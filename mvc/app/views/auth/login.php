<div class="auth-card">
  <div class="auth-logo">
    <div class="logo-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="#fff"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg></div>
    <span class="auth-brand">TaskBoard</span>
  </div>
  <h1 class="auth-title">Welcome back</h1>
  <p class="auth-sub">Sign in to your account</p>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= BASE_URL ?>/auth/login" class="auth-form">
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required placeholder="ahmad@example.com"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required placeholder="••••••••" />
    </div>
    <button type="submit" class="btn-primary btn-full">Sign In</button>
  </form>
  <p class="auth-switch">Don't have an account?
    <a href="<?= BASE_URL ?>/auth/register">Register</a>
  </p>
  <p class="auth-hint">Demo: <code>ahmad@example.com</code> / <code>password</code></p>
</div>
