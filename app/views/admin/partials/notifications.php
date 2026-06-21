<?php
$notifications = is_array($notifications ?? null) ? $notifications : [];
$unreadCount = max(0, (int) ($unreadCount ?? 0));
$badgeLabel = $unreadCount > 99 ? '99+' : (string) $unreadCount;
?>
<details class="notification-center">
  <summary class="notification-trigger" aria-label="Admin notifications">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
      <path d="M10 21h4"/>
    </svg>
    <?php if ($unreadCount > 0): ?>
      <span class="notification-badge"><?= e($badgeLabel) ?></span>
    <?php endif; ?>
  </summary>
  <div class="notification-menu">
    <div class="notification-head">
      <div>
        <span class="eyebrow">Admin</span>
        <strong>Notifications</strong>
      </div>
      <span><?= e((string) $unreadCount) ?> unread</span>
    </div>

    <?php if ($notifications === []): ?>
      <p class="notification-empty">No notifications yet.</p>
    <?php else: ?>
      <div class="notification-list">
        <?php foreach ($notifications as $notification): ?>
          <?php
            $notificationId = (int) ($notification['id'] ?? 0);
            $isUnread = empty($notification['read_at']);
            $actionUrl = (string) ($notification['action_url'] ?? admin_dashboard_url());
            $actionLabel = (string) ($notification['action_label'] ?? 'Open');
          ?>
          <article class="notification-item<?= $isUnread ? ' unread' : '' ?> <?= e((string) ($notification['severity'] ?? 'info')) ?>">
            <span class="notification-dot" aria-hidden="true"></span>
            <div class="notification-copy">
              <span class="notification-type"><?= e(admin_notification_type_label((string) ($notification['type'] ?? ''))) ?></span>
              <strong><?= e((string) ($notification['title'] ?? 'Notification')) ?></strong>
              <p><?= e((string) ($notification['body'] ?? '')) ?></p>
              <time datetime="<?= e((string) ($notification['created_at'] ?? '')) ?>"><?= e(admin_notification_relative_time($notification['created_at'] ?? '')) ?></time>
            </div>
            <form method="post" action="<?= e(admin_notifications_url()) ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="mark_read_open">
              <input type="hidden" name="notification_id" value="<?= e((string) $notificationId) ?>">
              <input type="hidden" name="redirect_to" value="<?= e($actionUrl) ?>">
              <button class="notification-open" type="submit"><?= e($actionLabel !== '' ? $actionLabel : 'Open') ?></button>
            </form>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($unreadCount > 0): ?>
        <form class="notification-footer" method="post" action="<?= e(admin_notifications_url()) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="mark_all_read">
          <input type="hidden" name="redirect_to" value="<?= e((string) ($_SERVER['REQUEST_URI'] ?? admin_dashboard_url())) ?>">
          <button type="submit">Mark all read</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</details>
