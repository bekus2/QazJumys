<?php
/**
 * Project: QazJumys
 * File: dashboard.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Unified private dashboard for publishing projects, sending proposals, messaging, uploads, and work completion.
 * RU: Единый личный кабинет для публикации проектов, откликов, сообщений, файлов и завершения работы.
 */

$isOwner = ($user['role'] ?? '') === 'owner';
?>
<section class="page-hero">
    <div class="container dashboard-head">
        <div>
            <span class="eyebrow"><?= $isOwner ? 'Owner account' : 'Бір аккаунт' ?></span>
            <h1>Сәлем, <?= e($profile['name'] ?? $user['name'] ?? '') ?></h1>
            <p><?= $isOwner ? 'Owner басқаруы бөлек қорғалған панельде ашылады.' : 'Осы кабинеттен жоба жариялап, басқа жобаларға ұсыныс жіберіп, жұмыс барысын аяқтауға болады.' ?></p>
        </div>
        <div class="hero-actions">
            <?php if ($isOwner): ?>
                <a class="btn btn-primary" href="owner.php">Owner panel</a>
            <?php else: ?>
                <a class="btn btn-primary" href="<?= e(url_for('project-create')) ?>">Жоба жариялау</a>
                <a class="btn btn-ghost" href="<?= e(url_for('projects')) ?>">Жұмыс табу</a>
            <?php endif; ?>
            <a class="btn btn-ghost" href="<?= e(url_for('profile')) ?>">Профиль</a>
        </div>
    </div>
</section>

<?php if ($isOwner): ?>
    <section class="section section-tight">
        <div class="container empty-state">
            <h2>Owner басқаруы дайын</h2>
            <p>Статистика, шағымдар, блоктау, разблоктау, пароль сброс және жобаларды модерациялау үшін арнайы owner panel ашыңыз.</p>
            <a class="btn btn-primary" href="owner.php">owner.php ашу</a>
        </div>
    </section>
<?php else: ?>
    <section class="section section-tight">
        <div class="container stat-grid stat-grid-wide">
            <div class="stat-card">
                <span>Мен жариялаған жобалар</span>
                <strong><?= (int) $dashboardStats['projects'] ?></strong>
            </div>
            <div class="stat-card">
                <span>Маған келген откликтер</span>
                <strong><?= (int) $dashboardStats['received_proposals'] ?></strong>
            </div>
            <div class="stat-card">
                <span>Мен жіберген откликтер</span>
                <strong><?= (int) $dashboardStats['proposals'] ?></strong>
            </div>
            <div class="stat-card">
                <span>Актив жұмыс</span>
                <strong><?= (int) $dashboardStats['active_work'] ?></strong>
            </div>
            <div class="stat-card">
                <span>Жаңа хабарлама</span>
                <strong><?= (int) $dashboardStats['unread_messages'] ?></strong>
            </div>
            <div class="stat-card">
                <span>Уведомления</span>
                <strong><?= (int) $dashboardStats['unread_notifications'] ?></strong>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container section-heading row-heading">
            <div>
                <span class="eyebrow">Жарияланған жұмыстар</span>
                <h2>Менің жобаларым және келген өтінімдер</h2>
            </div>
            <a class="btn btn-primary" href="<?= e(url_for('project-create')) ?>">Жаңа жоба</a>
        </div>
        <div class="container project-grid">
            <?php if (!empty($myProjects)): ?>
                <?php foreach ($myProjects as $project): ?>
                    <article class="project-card workflow-card">
                        <div class="project-meta">
                            <span><?= e($project['category_name']) ?></span>
                            <span><?= e(project_status_label((string) $project['status'])) ?></span>
                            <span><?= (int) $project['proposals_count'] ?> отклик</span>
                        </div>
                        <h3><?= e($project['title']) ?></h3>
                        <p><?= e(mb_substr((string) $project['description'], 0, 150, 'UTF-8')) ?>...</p>
                        <div class="workflow-steps">
                            <span class="<?= $project['status'] === 'open' ? 'is-current' : 'is-done' ?>">Open</span>
                            <span class="<?= in_array($project['status'], ['in_progress', 'submitted', 'completed'], true) ? 'is-done' : '' ?>">Accepted</span>
                            <span class="<?= in_array($project['status'], ['submitted', 'completed'], true) ? 'is-done' : '' ?>">Review</span>
                            <span class="<?= $project['status'] === 'completed' ? 'is-done' : '' ?>">Done</span>
                        </div>
                        <div class="project-footer">
                            <strong><?= e(format_money($project['budget_min'])) ?> - <?= e(format_money($project['budget_max'])) ?></strong>
                            <?php if (in_array((string) $project['status'], ['in_progress', 'submitted'], true)): ?>
                                <form class="inline-form js-ajax-form" action="ajax.php" method="post">
                                    <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                                    <input type="hidden" name="action" value="project_complete">
                                    <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
                                    <button class="btn btn-small" type="submit">Аяқтау</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <form class="form mini upload-inline js-ajax-form" action="ajax.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                            <input type="hidden" name="action" value="file_upload">
                            <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
                            <input type="hidden" name="visibility" value="brief">
                            <label>
                                <span>Brief файлы</span>
                                <input type="file" name="project_file" required>
                            </label>
                            <button class="btn btn-small" type="submit">Жүктеу</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Әлі жоба жарияланбаған</h3>
                    <p>Алғашқы тапсырманы жариялап, орындаушылардан нақты ұсыныс алыңыз.</p>
                    <a class="btn btn-primary" href="<?= e(url_for('project-create')) ?>">Жоба жариялау</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="section band">
        <div class="container section-heading">
            <span class="eyebrow">Келген өтінімдер</span>
            <h2>Откликті қабылдау, отклон ету және сөйлесу</h2>
        </div>
        <div class="container project-list">
            <?php if (!empty($receivedProposals)): ?>
                <?php foreach ($receivedProposals as $proposal): ?>
                    <article class="project-row slim management-row">
                        <div>
                            <div class="project-meta">
                                <span><?= e(proposal_status_label((string) $proposal['status'])) ?></span>
                                <span><?= e(project_status_label((string) $proposal['project_status'])) ?></span>
                                <span><?= (int) $proposal['delivery_days'] ?> күн</span>
                            </div>
                            <h2><?= e($proposal['project_title']) ?></h2>
                            <p><?= e(mb_substr((string) $proposal['cover_letter'], 0, 220, 'UTF-8')) ?>...</p>
                            <div class="chip-row">
                                <span><?= e($proposal['freelancer_name']) ?></span>
                                <span>★ <?= number_format((float) $proposal['freelancer_rating'], 1) ?></span>
                                <span><?= e(format_money($proposal['bid_amount'])) ?></span>
                            </div>
                            <form class="form mini message-inline js-ajax-form" action="ajax.php" method="post">
                                <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                                <input type="hidden" name="action" value="message_send">
                                <input type="hidden" name="project_id" value="<?= (int) $proposal['project_id'] ?>">
                                <input type="hidden" name="proposal_id" value="<?= (int) $proposal['id'] ?>">
                                <input type="hidden" name="receiver_id" value="<?= (int) $proposal['freelancer_id'] ?>">
                                <label>
                                    <span>Хабарлама</span>
                                    <textarea name="body" rows="2" required placeholder="Жоба бойынша сұрақ немесе келесі қадам"></textarea>
                                </label>
                                <button class="btn btn-small" type="submit">Жіберу</button>
                            </form>
                        </div>
                        <div class="proposal-summary action-stack">
                            <strong><?= e(format_money($proposal['bid_amount'])) ?></strong>
                            <?php if (in_array((string) $proposal['status'], ['sent', 'shortlisted'], true) && $proposal['project_status'] === 'open'): ?>
                                <form class="js-ajax-form" action="ajax.php" method="post">
                                    <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                                    <input type="hidden" name="action" value="proposal_accept">
                                    <input type="hidden" name="proposal_id" value="<?= (int) $proposal['id'] ?>">
                                    <button class="btn btn-small btn-primary" type="submit">Қабылдау</button>
                                </form>
                                <form class="js-ajax-form" action="ajax.php" method="post">
                                    <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                                    <input type="hidden" name="action" value="proposal_decline">
                                    <input type="hidden" name="proposal_id" value="<?= (int) $proposal['id'] ?>">
                                    <button class="btn btn-small" type="submit">Отклон</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Келген отклик жоқ</h3>
                    <p>Жоба жарияланғаннан кейін ұсыныстар осы жерде пайда болады.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="section">
        <div class="container section-heading row-heading">
            <div>
                <span class="eyebrow">Орындаушы ретінде</span>
                <h2>Мен жіберген откликтер және қабылданған жұмыс</h2>
            </div>
            <a class="btn btn-primary" href="<?= e(url_for('projects')) ?>">Жұмыс табу</a>
        </div>
        <div class="container project-list">
            <?php if (!empty($myProposals)): ?>
                <?php foreach ($myProposals as $proposal): ?>
                    <article class="project-row slim">
                        <div>
                            <div class="project-meta">
                                <span><?= e($proposal['category_name']) ?></span>
                                <span><?= e(proposal_status_label((string) $proposal['status'])) ?></span>
                                <span><?= e(project_status_label((string) $proposal['project_status'])) ?></span>
                            </div>
                            <h2><?= e($proposal['project_title']) ?></h2>
                            <p><?= e(mb_substr((string) $proposal['cover_letter'], 0, 180, 'UTF-8')) ?>...</p>
                            <div class="chip-row">
                                <span><?= e($proposal['client_name']) ?></span>
                                <span><?= e(format_money($proposal['bid_amount'])) ?></span>
                                <span><?= (int) $proposal['delivery_days'] ?> күн</span>
                            </div>
                            <?php if (in_array((string) $proposal['project_status'], ['in_progress', 'submitted'], true) && (int) $proposal['assigned_freelancer_id'] === (int) $user['id']): ?>
                                <form class="form mini upload-inline js-ajax-form" action="ajax.php" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                                    <input type="hidden" name="action" value="file_upload">
                                    <input type="hidden" name="project_id" value="<?= (int) $proposal['project_id'] ?>">
                                    <input type="hidden" name="proposal_id" value="<?= (int) $proposal['id'] ?>">
                                    <input type="hidden" name="visibility" value="delivery">
                                    <label>
                                        <span>Нәтиже файлы</span>
                                        <input type="file" name="project_file" required>
                                    </label>
                                    <button class="btn btn-small" type="submit">Жүктеу</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="proposal-summary action-stack">
                            <strong><?= e(format_money($proposal['bid_amount'])) ?></strong>
                            <?php if ($proposal['project_status'] === 'in_progress' && (int) $proposal['assigned_freelancer_id'] === (int) $user['id']): ?>
                                <form class="js-ajax-form" action="ajax.php" method="post">
                                    <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                                    <input type="hidden" name="action" value="project_submit_delivery">
                                    <input type="hidden" name="project_id" value="<?= (int) $proposal['project_id'] ?>">
                                    <button class="btn btn-small btn-primary" type="submit">Тапсыру</button>
                                </form>
                            <?php endif; ?>
                            <form class="form mini message-inline js-ajax-form" action="ajax.php" method="post">
                                <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                                <input type="hidden" name="action" value="message_send">
                                <input type="hidden" name="project_id" value="<?= (int) $proposal['project_id'] ?>">
                                <input type="hidden" name="proposal_id" value="<?= (int) $proposal['id'] ?>">
                                <input type="hidden" name="receiver_id" value="<?= (int) $proposal['client_id'] ?>">
                                <label>
                                    <span>Хабарлама</span>
                                    <textarea name="body" rows="2" required placeholder="Тапсырыс берушіге хабарлама"></textarea>
                                </label>
                                <button class="btn btn-small" type="submit">Жіберу</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Ұсыныс әлі жіберілмеген</h3>
                    <p>Ашық жобаларды қарап, өз тәжірибеңізге сәйкес ұсыныс беріңіз.</p>
                    <a class="btn btn-primary" href="<?= e(url_for('projects')) ?>">Жобаларды қарау</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="section band">
        <div class="container dashboard-ops-grid">
            <div class="panel">
                <div class="section-heading">
                    <span class="eyebrow">Уведомления</span>
                    <h2>Соңғы хабарламалар</h2>
                </div>
                <?php if (!empty($notifications)): ?>
                    <div class="mini-list">
                        <?php foreach ($notifications as $notice): ?>
                            <div class="<?= empty($notice['is_read']) ? 'is-unread' : '' ?>">
                                <strong><?= e($notice['title']) ?></strong>
                                <p><?= e($notice['body']) ?></p>
                                <span><?= e($notice['created_at']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <form class="js-ajax-form" action="ajax.php" method="post">
                        <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                        <input type="hidden" name="action" value="notification_mark_read">
                        <button class="btn btn-small" type="submit">Оқылды</button>
                    </form>
                <?php else: ?>
                    <p class="muted">Әзірге уведомления жоқ.</p>
                <?php endif; ?>
            </div>

            <div class="panel">
                <div class="section-heading">
                    <span class="eyebrow">Messages</span>
                    <h2>Соңғы сөйлесулер</h2>
                </div>
                <?php if (!empty($recentMessages)): ?>
                    <div class="mini-list">
                        <?php foreach ($recentMessages as $message): ?>
                            <div class="<?= ((int) $message['receiver_id'] === (int) $user['id'] && empty($message['is_read'])) ? 'is-unread' : '' ?>">
                                <strong><?= e($message['project_title']) ?></strong>
                                <p><?= e($message['sender_name']) ?>: <?= e($message['body']) ?></p>
                                <span><?= e($message['created_at']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="muted">Сөйлесулер әлі басталмады.</p>
                <?php endif; ?>
            </div>

            <div class="panel">
                <div class="section-heading">
                    <span class="eyebrow">Files</span>
                    <h2>Жоба файлдары</h2>
                </div>
                <?php if (!empty($recentFiles)): ?>
                    <div class="mini-list">
                        <?php foreach ($recentFiles as $file): ?>
                            <div>
                                <strong><a href="download.php?id=<?= (int) $file['id'] ?>"><?= e($file['original_name']) ?></a></strong>
                                <p><?= e($file['project_title']) ?> · <?= e($file['uploader_name']) ?></p>
                                <span><?= e(format_file_size($file['file_size'])) ?> · <?= e($file['visibility']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="muted">Файлдар жүктелмеген.</p>
                <?php endif; ?>
            </div>

            <div class="panel">
                <div class="section-heading">
                    <span class="eyebrow">Жалоба</span>
                    <h2>Owner-ге хабарлау</h2>
                </div>
                <form class="form mini js-ajax-form" action="ajax.php" method="post">
                    <input type="hidden" name="_csrf" value="<?= e(\QazJumys\Core\Csrf::token()) ?>">
                    <input type="hidden" name="action" value="complaint_create">
                    <div class="two-cols">
                        <label>
                            <span>Project ID</span>
                            <input type="number" name="project_id" min="0" placeholder="міндетті емес">
                        </label>
                        <label>
                            <span>User ID</span>
                            <input type="number" name="reported_user_id" min="0" placeholder="міндетті емес">
                        </label>
                    </div>
                    <label>
                        <span>Тақырып</span>
                        <input type="text" name="subject" required minlength="4" maxlength="180">
                    </label>
                    <label>
                        <span>Сипаттама</span>
                        <textarea name="body" rows="4" required minlength="10"></textarea>
                    </label>
                    <button class="btn btn-primary btn-full" type="submit">Жіберу</button>
                </form>
                <?php if (!empty($recentComplaints)): ?>
                    <div class="mini-list compact-list">
                        <?php foreach ($recentComplaints as $complaint): ?>
                            <div>
                                <strong><?= e($complaint['subject']) ?></strong>
                                <span><?= e($complaint['status']) ?> · <?= e($complaint['created_at']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container section-heading">
            <span class="eyebrow">Жаңа мүмкіндіктер</span>
            <h2>Соңғы ашық жобалар</h2>
        </div>
        <div class="container project-grid">
            <?php foreach ($recommendedProjects as $project): ?>
                <article class="project-card">
                    <div class="project-meta">
                        <span><?= e($project['category_name']) ?></span>
                        <span><?= (int) $project['proposals_count'] ?> ұсыныс</span>
                    </div>
                    <h3><?= e($project['title']) ?></h3>
                    <p><?= e(mb_substr((string) $project['description'], 0, 130, 'UTF-8')) ?>...</p>
                    <div class="project-footer">
                        <strong><?= e(format_money($project['budget_min'])) ?> - <?= e(format_money($project['budget_max'])) ?></strong>
                        <a class="btn btn-small" href="<?= e(url_for('projects', ['q' => $project['title']])) ?>">Қарау</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
