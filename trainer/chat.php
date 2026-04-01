<?php
// /Gymora/trainer/chat.php
require_once '../config/db.php';
require_once '../config/session.php';
require_once '../config/constants.php';

requireRole(ROLE_TRAINER);

$staff_id = $_SESSION['user_id'];

// Fetch ONLY Users (Clients) who have interacted with this Trainer
$contactsStmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.name 
    FROM users u 
    LEFT JOIN appointments a ON u.id = a.user_id AND a.staff_id = ?
    LEFT JOIN workout_plans w ON u.id = w.user_id AND w.trainer_id = ?
    WHERE u.role = 'user' 
    AND u.is_active = 1 
    AND (a.id IS NOT NULL OR w.id IS NOT NULL)
    ORDER BY u.name
");
$contactsStmt->execute([$staff_id, $staff_id]);
$contacts = $contactsStmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mt-4">
    <div class="col-12">
        <h2 class="fw-bold"><i class="bi bi-chat-dots"></i> Client Messenger</h2>
        <p class="text-muted">Communicate with your assigned training clients.</p>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-primary h-100">
            <div class="card-header bg-primary text-white fw-bold">
                My Clients
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($contacts as $contact): ?>
                    <button class="list-group-item list-group-item-action contact-item" 
                            id="contact-<?= $contact['id'] ?>" 
                            onclick="loadChat(<?= $contact['id'] ?>, '<?= addslashes(htmlspecialchars($contact['name'])) ?>')">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong><?= htmlspecialchars($contact['name']) ?></strong>
                            <span class="badge bg-secondary">Client</span>
                        </div>
                    </button>
                <?php endforeach; ?>
                <?php if (count($contacts) == 0): ?>
                    <div class="p-3 text-muted">No clients assigned to you yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-secondary h-100">
            <div class="card-header bg-secondary text-white fw-bold d-flex justify-content-between align-items-center">
                <span id="chat-with-name">Select a client</span>
                <i class="bi bi-shield-lock" title="Messages are secure"></i>
            </div>
            
            <div class="card-body bg-white" id="chat-box" style="height: 400px; overflow-y: auto;">
                <div id="empty-chat-state" class="text-center text-muted mt-5">
                    <i class="bi bi-chat-left-text display-4 text-light"></i>
                    <p class="mt-2">Click a client on the left to view messages.</p>
                </div>
            </div>
            
            <div class="card-footer bg-light" id="message-form" style="display: none;">
                <form id="message-input-form" class="d-flex">
                    <input type="text" id="message-input" class="form-control me-2" placeholder="Type your reply..." required autocomplete="off">
                    <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-send"></i> Send</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/chat_v2.js"></script>

<?php require_once '../includes/footer.php'; ?>