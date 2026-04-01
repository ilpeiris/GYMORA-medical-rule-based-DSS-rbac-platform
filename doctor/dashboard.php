<?php
// /Gymora/doctor/dashboard.php
require_once '../config/db.php';
require_once '../config/session.php';
require_once '../config/constants.php';

// SECURITY GATEWAY: Only allow 'doctor' roles to view this page
requireRole(ROLE_DOCTOR);

// Fetch the doctor's upcoming appointments
$stmt = $pdo->prepare("
    SELECT a.id as appointment_id, a.datetime, a.status, u.name as patient_name, u.id as patient_id 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.staff_id = ? AND a.datetime >= CURDATE()
    ORDER BY a.datetime ASC
");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mt-4">
    <div class="col-12">
        <h2 class="fw-bold">Doctor Dashboard</h2>
        <p class="text-muted">Welcome, Dr. <?= htmlspecialchars($_SESSION['name']) ?>.</p>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Upcoming Appointments</h5>
                <span class="badge bg-light text-primary"><?= count($appointments) ?> Scheduled</span>
            </div>
            <div class="card-body p-0">
                <?php if (count($appointments) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Patient Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appt): ?>
                                    <tr>
                                        <td class="align-middle">
                                            <strong><?= date('M j, Y', strtotime($appt['datetime'])) ?></strong><br>
                                            <span class="text-muted"><?= date('g:i A', strtotime($appt['datetime'])) ?></span>
                                        </td>
                                        <td class="align-middle"><?= htmlspecialchars($appt['patient_name']) ?></td>
                                        <td class="align-middle">
                                            <?php if ($appt['status'] == 'scheduled'): ?>
                                                <span class="badge bg-info text-dark">Scheduled</span>
                                            <?php elseif ($appt['status'] == 'completed'): ?>
                                                <span class="badge bg-success">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle">
    <?php if ($appt['status'] == 'scheduled'): ?>
        <a href="assessment.php?appointment_id=<?= $appt['appointment_id'] ?>&patient_id=<?= $appt['patient_id'] ?>" class="btn btn-sm btn-primary">
            Start Assessment
        </a>
    <?php else: ?>
        <button class="btn btn-sm btn-secondary" disabled>Assessed</button>
    <?php endif; ?>
    
    <a href="patient_history.php?patient_id=<?= $appt['patient_id'] ?>" class="btn btn-sm btn-outline-info ms-2">
        Patient History
    </a>
</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-5 text-center text-muted">
                        <p class="mb-0 fs-5">You have no upcoming appointments.</p>
                        <small>Enjoy your free time!</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>