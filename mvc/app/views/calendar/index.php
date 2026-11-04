<?php
[$y, $m] = explode('-', $yearMonth . '-01');
$y = (int)$y;  $m = (int)$m;
$firstDow  = (int)(new DateTime("{$y}-{$m}-01"))->format('w'); // 0=Sun
$daysCount = (int)(new DateTime("{$y}-{$m}-01"))->format('t');
$today     = date('Y-m-d');
$monthNames = ['','Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
?>
<!-- Calendar -->
<div class="content-header">
  <div>
    <h1>Calendar</h1>
    <p class="content-subtitle">Task deadlines — <?= $monthNames[$m] ?> <?= $y ?></p>
  </div>
  <div class="cal-nav">
    <?php
      $prevY = $m === 1 ? $y-1 : $y; $prevM = $m === 1 ? 12 : $m-1;
      $nextY = $m === 12 ? $y+1 : $y; $nextM = $m === 12 ? 1 : $m+1;
    ?>
    <a href="?month=<?= $prevY ?>-<?= str_pad($prevM,2,'0',STR_PAD_LEFT) ?>" class="btn-secondary">‹</a>
    <span class="cal-month-label"><?= $monthNames[$m] ?> <?= $y ?></span>
    <a href="?month=<?= $nextY ?>-<?= str_pad($nextM,2,'0',STR_PAD_LEFT) ?>" class="btn-secondary">›</a>
  </div>
</div>

<div class="calendar-grid-wrap">
  <div class="cal-weekdays">
    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span>
    <span>Thu</span><span>Fri</span><span>Sat</span>
  </div>
  <div class="cal-grid">
    <?php
      // Leading blanks
      for ($i = 0; $i < $firstDow; $i++) {
          echo '<div class="cal-day other-month"></div>';
      }
      // Days
      for ($d = 1; $d <= $daysCount; $d++) {
          $mm   = str_pad($m, 2, '0', STR_PAD_LEFT);
          $dd   = str_pad($d, 2, '0', STR_PAD_LEFT);
          $key  = "{$y}-{$mm}-{$dd}";
          $cls  = ($key === $today) ? ' today' : '';
          echo "<div class=\"cal-day{$cls}\">";
          echo "<div class=\"cal-day-num\">{$d}</div>";
          $tasksOnDay = $taskMap[$key] ?? [];
          foreach (array_slice($tasksOnDay, 0, 3) as $t) {
              $title = htmlspecialchars($t['title']);
              echo "<span class=\"cal-task-pill cal-pill-{$t['status']}\" title=\"{$title}\">{$title}</span>";
          }
          if (count($tasksOnDay) > 3) {
              echo "<span class=\"cal-task-pill cal-pill-todo\" style=\"opacity:.7\">+" . (count($tasksOnDay)-3) . " more</span>";
          }
          echo "</div>";
      }
      // Trailing blanks
      $total = $firstDow + $daysCount;
      $rem   = $total % 7;
      if ($rem > 0) for ($i = 0; $i < 7 - $rem; $i++) echo '<div class="cal-day other-month"></div>';
    ?>
  </div>
</div>

<div class="cal-legend">
  <span class="cal-legend-item"><span class="cal-dot cal-dot-todo"></span>To Do</span>
  <span class="cal-legend-item"><span class="cal-dot cal-dot-inprogress"></span>In Progress</span>
  <span class="cal-legend-item"><span class="cal-dot cal-dot-completed"></span>Completed</span>
  <span class="cal-legend-item"><span class="cal-dot cal-dot-overdue"></span>Overdue</span>
</div>
