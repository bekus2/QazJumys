<?php
/**
 * Project: QazJumys
 * File: workflow_smoke.php
 * Author: Beck Sarbassov
 * Version: 1.4.0
 * Release Date: 2026-06-28
 * Last Updated: 2026-06-28
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Local end-to-end repository smoke test for the marketplace work lifecycle.
 * RU: Локальный end-to-end smoke-тест репозиториев для полного рабочего цикла маркетплейса.
 */

declare(strict_types=1);

use QazJumys\Core\Database;
use QazJumys\Repositories\ComplaintRepository;
use QazJumys\Repositories\EngagementRepository;
use QazJumys\Repositories\FileRepository;
use QazJumys\Repositories\MessageRepository;
use QazJumys\Repositories\OwnerRepository;
use QazJumys\Repositories\ProjectRepository;
use QazJumys\Repositories\UserRepository;

$root = dirname(__DIR__);
$config = require $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';
$pdo = Database::connection($config['database']);
$failures = [];

/**
 * EN: Adds a workflow failure when a condition is false.
 * RU: Добавляет ошибку workflow-теста, если условие ложно.
 *
 * @param bool $condition Assertion result / Результат проверки
 * @param string $message Failure message / Сообщение ошибки
 * @return void
 */
function workflow_check(bool $condition, string $message): void
{
    global $failures;

    if (!$condition) {
        $failures[] = $message;
    }
}

/**
 * EN: Expects an operation to throw.
 * RU: Ожидает, что операция выбросит исключение.
 *
 * @param callable $callback Operation / Операция
 * @param string $message Failure message / Сообщение ошибки
 * @return void
 */
function workflow_expect_exception(callable $callback, string $message): void
{
    try {
        $callback();
        workflow_check(false, $message);
    } catch (Throwable) {
        workflow_check(true, $message);
    }
}

$users = new UserRepository($pdo);
$projects = new ProjectRepository($pdo);
$messages = new MessageRepository($pdo);
$files = new FileRepository($pdo);
$complaints = new ComplaintRepository($pdo);
$engagement = new EngagementRepository($pdo);
$owner = new OwnerRepository($pdo);

$run = 'codex-smoke-' . date('YmdHis') . '-' . random_int(1000, 9999);
$password = 'SmokePass123!';
$changedPassword = 'SmokeChanged123!';

try {
    $cleanup = $pdo->prepare('DELETE FROM users WHERE email LIKE :pattern');
    $cleanup->execute(['pattern' => 'codex-smoke-%@qazjumys.local']);

    $categoryId = (int) $pdo->query('SELECT id FROM categories ORDER BY sort_order ASC, id ASC LIMIT 1')->fetchColumn();
    workflow_check($categoryId > 0, 'At least one category must exist.');

    $smokeOwner = $users->create([
        'role' => 'owner',
        'name' => 'Smoke Owner',
        'email' => $run . '-owner@qazjumys.local',
        'password' => $password,
        'city' => 'Local',
    ]);
    workflow_check($users->changePassword((int) $smokeOwner['id'], $password, $changedPassword), 'Owner password change works.');
    $ownerWithHash = $users->findByEmail($run . '-owner@qazjumys.local');
    workflow_check(password_verify($changedPassword, (string) $ownerWithHash['password_hash']), 'Changed owner password verifies.');

    $client = $users->create([
        'role' => 'member',
        'name' => 'Smoke Client',
        'email' => $run . '-client@qazjumys.local',
        'password' => $password,
        'city' => 'Almaty',
    ]);
    $freelancer = $users->create([
        'role' => 'member',
        'name' => 'Smoke Performer',
        'email' => $run . '-performer@qazjumys.local',
        'password' => $password,
        'city' => 'Astana',
    ]);
    $declinedUser = $users->create([
        'role' => 'member',
        'name' => 'Smoke Declined',
        'email' => $run . '-declined@qazjumys.local',
        'password' => $password,
        'city' => 'Shymkent',
    ]);

    $projectId = $projects->create((int) $client['id'], [
        'category_id' => $categoryId,
        'title' => 'Smoke marketplace project ' . $run,
        'description' => 'Smoke workflow project with enough detail for proposal, acceptance, messaging, files, and completion checks.',
        'project_type' => 'fixed',
        'experience_level' => 'intermediate',
        'skills' => 'PHP, marketplace, smoke',
        'location' => 'Remote',
        'is_remote' => 1,
        'is_urgent' => 0,
        'budget_min' => 100000,
        'budget_max' => 200000,
        'deadline_days' => 14,
    ]);

    workflow_check($engagement->toggleSavedProject((int) $freelancer['id'], $projectId), 'Performer can save a project.');
    $engagement->createSavedSearch((int) $freelancer['id'], 'Smoke search', 'page=projects&is_remote=1');

    $proposalId = $projects->createProposal($projectId, (int) $freelancer['id'], [
        'cover_letter' => 'I can complete this smoke project and provide a delivery package with clear milestones.',
        'bid_amount' => 150000,
        'delivery_days' => 10,
    ]);
    $declinedProposalId = $projects->createProposal($projectId, (int) $declinedUser['id'], [
        'cover_letter' => 'This proposal will be declined for access-rule verification.',
        'bid_amount' => 175000,
        'delivery_days' => 12,
    ]);
    $projects->shortlistProposal($proposalId, (int) $client['id']);
    $projects->declineProposal($declinedProposalId, (int) $client['id']);
    workflow_check(!$projects->isParticipant($projectId, (int) $declinedUser['id']), 'Declined proposal is not a participant.');

    $projects->acceptProposal($proposalId, (int) $client['id']);
    workflow_expect_exception(
        static fn() => $projects->acceptProposal($proposalId, (int) $client['id']),
        'Duplicate proposal acceptance is blocked.'
    );

    $withdrawProjectId = $projects->create((int) $client['id'], [
        'category_id' => $categoryId,
        'title' => 'Smoke withdrawal project ' . $run,
        'description' => 'Secondary project used to verify proposal withdrawal and cancellation rules.',
        'project_type' => 'fixed',
        'experience_level' => 'entry',
        'skills' => 'Testing, QA',
        'location' => 'Remote',
        'is_remote' => 1,
        'is_urgent' => 0,
        'budget_min' => 50000,
        'budget_max' => 90000,
        'deadline_days' => 7,
    ]);
    $withdrawProposalId = $projects->createProposal($withdrawProjectId, (int) $freelancer['id'], [
        'cover_letter' => 'This proposal will be withdrawn by the performer.',
        'bid_amount' => 70000,
        'delivery_days' => 5,
    ]);
    $projects->withdrawProposal($withdrawProposalId, (int) $freelancer['id']);
    workflow_check(!$projects->isParticipant($withdrawProjectId, (int) $freelancer['id']), 'Withdrawn proposal is not a participant.');
    workflow_expect_exception(
        static fn() => $projects->acceptProposal($withdrawProposalId, (int) $client['id']),
        'Withdrawn proposal cannot be accepted.'
    );
    $projects->cancelProject($withdrawProjectId, (int) $client['id']);

    workflow_check($projects->canMessage($projectId, $proposalId, (int) $client['id'], (int) $freelancer['id']), 'Client can message active performer.');
    workflow_check(!$projects->canMessage($projectId, $declinedProposalId, (int) $client['id'], (int) $declinedUser['id']), 'Client cannot message declined proposal.');
    $messages->create($projectId, $proposalId, (int) $client['id'], (int) $freelancer['id'], 'Please start with the brief.');
    $messages->create($projectId, $proposalId, (int) $freelancer['id'], (int) $client['id'], 'Brief received, delivery is in progress.');

    workflow_check($files->canUpload($projectId, null, (int) $client['id'], 'brief'), 'Client can upload brief file.');
    workflow_check($files->canUpload($projectId, $proposalId, (int) $freelancer['id'], 'proposal'), 'Performer can upload proposal file.');
    workflow_check($files->canUpload($projectId, $proposalId, (int) $freelancer['id'], 'delivery'), 'Assigned performer can upload delivery file.');
    workflow_check(!$files->canUpload($projectId, null, (int) $declinedUser['id'], 'delivery'), 'Declined user cannot upload delivery file.');

    $uploadDirectory = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads';
    foreach (['brief', 'proposal', 'delivery'] as $visibility) {
        $storedName = $run . '-' . $visibility . '.txt';
        file_put_contents($uploadDirectory . DIRECTORY_SEPARATOR . $storedName, 'Smoke ' . $visibility);
        $fileId = $files->create([
            'project_id' => $projectId,
            'proposal_id' => $visibility === 'brief' ? null : $proposalId,
            'uploader_id' => $visibility === 'brief' ? (int) $client['id'] : (int) $freelancer['id'],
            'original_name' => $visibility . '.txt',
            'stored_name' => $storedName,
            'mime_type' => 'text/plain',
            'file_size' => 16,
            'visibility' => $visibility,
        ]);
        workflow_check($files->canAccess($fileId, (int) $client['id']), $visibility . ' file visible to client.');
        workflow_check($files->canAccess($fileId, (int) $freelancer['id']), $visibility . ' file visible to accepted performer.');
        workflow_check(!$files->canAccess($fileId, (int) $declinedUser['id']), $visibility . ' file hidden from declined user.');
    }

    $engagement->createMilestone($projectId, (int) $client['id'], 'Smoke milestone', date('Y-m-d', strtotime('+7 days')));
    $milestones = $engagement->milestonesForUser((int) $client['id']);
    workflow_check($milestones !== [], 'Milestone is visible after creation.');
    $engagement->completeMilestone((int) $milestones[0]['id'], (int) $client['id']);

    $projects->submitDelivery($projectId, (int) $freelancer['id']);
    $projects->completeProject($projectId, (int) $client['id']);
    $engagement->createReview($projectId, (int) $client['id'], (int) $freelancer['id'], 5, 'Great smoke delivery and clear communication.');
    $engagement->createReview($projectId, (int) $freelancer['id'], (int) $client['id'], 5, 'Clear brief, fast feedback, and smooth acceptance.');
    $engagement->createPortfolioItem((int) $freelancer['id'], [
        'title' => 'Smoke portfolio item',
        'description' => 'A smoke portfolio record for marketplace profile checks.',
        'url' => 'https://example.com/smoke',
        'skills' => 'PHP, QA',
    ]);

    $verificationId = $engagement->requestVerification((int) $freelancer['id'], 'Smoke verification request with enough detail for owner moderation.');
    $engagement->reviewVerification($verificationId, 'approved', 'Smoke approved.');
    $verificationRejectId = $engagement->requestVerification((int) $client['id'], 'Second smoke verification request for rejection coverage.');
    $engagement->reviewVerification($verificationRejectId, 'rejected', 'Smoke rejected.');

    $complaintId = $complaints->create([
        'reporter_id' => (int) $client['id'],
        'reported_user_id' => (int) $freelancer['id'],
        'project_id' => $projectId,
        'proposal_id' => $proposalId,
        'subject' => 'Smoke complaint',
        'body' => 'Smoke complaint body for owner status update.',
    ]);
    $owner->updateComplaint($complaintId, 'resolved', 'Smoke resolved.');
    $owner->blockUser((int) $declinedUser['id'], 'Smoke block.');
    $blocked = $users->find((int) $declinedUser['id']);
    workflow_check(($blocked['status'] ?? '') === 'blocked', 'Owner can block a test user.');
    $owner->unblockUser((int) $declinedUser['id']);
    $unblocked = $users->find((int) $declinedUser['id']);
    workflow_check(($unblocked['status'] ?? '') === 'active', 'Owner can unblock a test user.');
    $owner->resetPassword((int) $declinedUser['id'], 'SmokeReset123!');
    $resetUser = $users->findByEmail($run . '-declined@qazjumys.local');
    workflow_check(password_verify('SmokeReset123!', (string) $resetUser['password_hash']), 'Owner can reset a test user password.');
} catch (Throwable $exception) {
    $failures[] = 'Workflow smoke exception: ' . $exception->getMessage();
}

if ($failures !== []) {
    fwrite(STDERR, "QazJumys workflow smoke failed:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "QazJumys workflow smoke passed.\n";
