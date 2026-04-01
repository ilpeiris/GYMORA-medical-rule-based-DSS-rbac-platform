<?php
// /Gymora/admin/audit_logs.php
require_once '../config/db.php';
require_once '../config/session.php';
require_once '../config/constants.php';

requireRole(ROLE_ADMIN);

// Fetch the most recent 50 audit logs
$stmt = $pdo->query("
    SELECT a.*, u.name as user_name, u.role 
    FROM audit_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.timestamp DESC 
    LIMIT 50
");
$logs = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mt-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Admin Dashboard</a></li>
                <li class="breadcrumb-item active">Audit Logs</li>
            </ol>
        </nav>
        <h2 class="fw-bold text-danger">GDPR Audit Logs</h2>
        <p class="text-muted">Strict tracking of all access to Special Category medical data.</p>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Recent System Activity</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Action</th>
                                <th>Data Type</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($logs) > 0): ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= date('Y-m-d H:i:s', strtotime($log['timestamp'])) ?></td>
                                        <td><?= htmlspecialchars($log['user_name'] ?? 'System') ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($log['role'] ?? 'N/A') ?></span></td>
                                        <td><strong><?= htmlspecialchars($log['action']) ?></strong></td>
                                        <td><?= htmlspecialchars($log['data_type']) ?></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($log['ip_address']) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No audit logs found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>