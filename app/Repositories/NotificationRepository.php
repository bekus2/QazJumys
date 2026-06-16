<?php
/**
 * Project: QazJumys
 * File: NotificationRepository.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Creates in-app notifications and logs optional email delivery attempts.
 * RU: Создает внутренние уведомления и журналирует опциональную отправку email.
 */

declare(strict_types=1);

namespace QazJumys\Repositories;

use PDO;

final class NotificationRepository
{
    /**
     * @param PDO $pdo Database connection / Подключение к базе
     * @param array<string, mixed> $appConfig App settings / Настройки приложения
     */
    public function __construct(private readonly PDO $pdo, private readonly array $appConfig)
    {
    }

    /**
     * EN: Creates a notification and stores an email log row for operations visibility.
     * RU: Создает уведомление и сохраняет запись email-журнала для прозрачности операций.
     *
     * @param int $userId Recipient user id / ID получателя
     * @param string $type Notification type / Тип уведомления
     * @param string $title Notification title / Заголовок уведомления
     * @param string $body Notification body / Текст уведомления
     * @param bool $sendEmail Whether to attempt email / Нужно ли пытаться отправить email
     * @return void
     */
    public function notify(int $userId, string $type, string $title, string $body, bool $sendEmail = true): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO notifications (user_id, type, title, body, is_read, created_at)
             VALUES (:user_id, :type, :title, :body, 0, NOW())'
        );
        $statement->execute([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
        ]);

        if ($sendEmail) {
            $this->logEmail($userId, $title, $body);
        }
    }

    /**
     * EN: Returns recent notifications for a user.
     * RU: Возвращает последние уведомления пользователя.
     *
     * @param int $userId User id / ID пользователя
     * @param int $limit Maximum rows / Максимум строк
     * @return array<int, array<string, mixed>>
     */
    public function recentForUser(int $userId, int $limit = 10): array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * EN: Counts unread notifications for the active account.
     * RU: Считает непрочитанные уведомления активного аккаунта.
     *
     * @param int $userId User id / ID пользователя
     * @return int
     */
    public function unreadCount(int $userId): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0');
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    /**
     * EN: Marks all notifications as read for the user.
     * RU: Отмечает все уведомления пользователя как прочитанные.
     *
     * @param int $userId User id / ID пользователя
     * @return void
     */
    public function markAllRead(int $userId): void
    {
        $statement = $this->pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :user_id');
        $statement->execute(['user_id' => $userId]);
    }

    /**
     * EN: Logs email delivery, sending only when MAIL_ENABLED is true.
     * RU: Журналирует email-доставку, отправляя только когда MAIL_ENABLED=true.
     *
     * @param int $userId Recipient user id / ID получателя
     * @param string $subject Email subject / Тема письма
     * @param string $body Email body / Текст письма
     * @return void
     */
    private function logEmail(int $userId, string $subject, string $body): void
    {
        $user = $this->findUser($userId);

        if (!$user) {
            return;
        }

        $status = 'queued';
        $error = null;
        $sentAt = null;

        if (!empty($this->appConfig['mail_enabled'])) {
            $headers = [
                'From: ' . (string) ($this->appConfig['mail_from'] ?? 'no-reply@qazjumys.local'),
                'Content-Type: text/plain; charset=UTF-8',
            ];
            $sent = @mail((string) $user['email'], $subject, $body, implode("\r\n", $headers));
            $status = $sent ? 'sent' : 'failed';
            $error = $sent ? null : 'PHP mail() returned false. Configure SMTP or disable MAIL_ENABLED.';
            $sentAt = $sent ? date('Y-m-d H:i:s') : null;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO email_logs (user_id, recipient_email, subject, body, status, error_message, created_at, sent_at)
             VALUES (:user_id, :recipient_email, :subject, :body, :status, :error_message, NOW(), :sent_at)'
        );
        $statement->execute([
            'user_id' => $userId,
            'recipient_email' => $user['email'],
            'subject' => $subject,
            'body' => $body,
            'status' => $status,
            'error_message' => $error,
            'sent_at' => $sentAt,
        ]);
    }

    /**
     * EN: Finds a notification recipient.
     * RU: Ищет получателя уведомления.
     *
     * @param int $userId User id / ID пользователя
     * @return array<string, mixed>|null
     */
    private function findUser(int $userId): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, email FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $userId]);
        $user = $statement->fetch();

        return $user ?: null;
    }
}
