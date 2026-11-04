<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle ?? '') ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css" />
</head>
<body class="auth-body">
<?= $content ?>
</body>
</html>
