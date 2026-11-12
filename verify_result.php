<?php
$page_title = "Verify & Print Result";
require_once 'includes/header.php';

$conn = getDB();
$lab_no = trim($_GET['lab_no'] ?? $_POST['lab_no'] ?? '');
$patient = null;
$results = [];
$msg = "";

if ($lab_no !== '') {
    if ($conn) {
        $stmt = $conn->prepare("SELECT * FROM patients WHERE lab_no = ? LIMIT 1");
        $stmt->bind_param("s", $lab_no);
        $stmt->execute();
        $patient = $stmt->get_result()->fetch_assoc();

        if ($patient) {
            $r_stmt = $conn->prepare("
                SELECT r.*, t.test_name, t.department, t.sample_type, t.reference_range, t.unit
                FROM lab_requests r
                JOIN test_catalog t ON r.test_id = t.id
                WHERE r.patient_id = ?
            ");
            $r_stmt->bind_param("i", $patient['id']);
            $r_stmt->execute();
            $res = $r_stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $results[] = $row;
            }
        } else {
            $msg = "No patient record found matching Lab Number: <strong>" . htmlspecialchars($lab_no) . "</strong>";
        }
    } else {
        $msg = "Database connection unavailable. Configure config/db.php first.";
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center no-print mb-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h4 class="fw-bold text-center mb-2">Online Laboratory Result Verification</h4>
                <p class="text-muted text-center small mb-4">Enter the Patient Lab Number printed on your receipt (e.g., <code>GSK-2611-XXXX</code>) to view or print your verified diagnostic report.</p>
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="lab_no" class="form-control form-control-lg" placeholder="Enter Lab Number..." value="<?php echo htmlspecialchars($lab_no); ?>" required>
                    <button type="submit" class="btn btn-gaskiya px-4 fw-bold">Verify</button>
                </form>
                <?php if ($msg): ?>
                    <div class="alert alert-warning mt-3 mb-0 small"><?php echo $msg; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($patient): ?>
    <div class="card border-0 shadow p-4 p-md-5 rounded-4 bg-white print-sheet mx-auto" style="max-width: 900px;">
        <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-4">
            <div class="d-flex align-items-center">
                <img src="assets/img/logo.png" alt="Gaskiya Logo" style="height: 75px; border-radius: 12px;" class="me-3">
                <div>
                    <h4 class="fw-bold text-success mb-0"><?php echo LAB_NAME; ?></h4>
                    <small class="text-muted d-block"><?php echo LAB_ADDRESS; ?></small>
                    <small class="text-muted">Tel: <?php echo LAB_PHONE; ?> | Email: <?php echo LAB_EMAIL; ?></small>
                </div>
            </div>
            <div class="text-md-end mt-3 mt-md-0">
                <span class="badge bg-success px-3 py-2">OFFICIAL DIAGNOSTIC REPORT</span>
                <div class="small text-muted mt-1">Printed: <?php echo date('d M Y, h:i A'); ?></div>
            </div>
        </div>

        <div class="row bg-light rounded p-3 mb-4 small">
            <div class="col-md-6">
                <div><strong>Patient Name:</strong> <?php echo htmlspecialchars($patient['full_name']); ?></div>
                <div><strong>Age / Sex:</strong> <?php echo htmlspecialchars($patient['age']); ?> Years / <?php echo htmlspecialchars($patient['gender']); ?></div>
                <div><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></div>
            </div>
            <div class="col-md-6 text-md-end">
                <div><strong>Laboratory No:</strong> <span class="text-success fw-bold"><?php echo htmlspecialchars($patient['lab_no']); ?></span></div>
                <div><strong>Referring Clinician:</strong> <?php echo htmlspecialchars($patient['referring_doctor']); ?></div>
                <div><strong>Registration Date:</strong> <?php echo date('d M Y', strtotime($patient['created_at'])); ?></div>
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Investigation / Department</th>
                        <th>Sample</th>
                        <th>Observed Result</th>
                        <th>Flag</th>
                        <th>Reference Interval</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?php echo htmlspecialchars($r['test_name']); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($r['department']); ?></small>
                        </td>
                        <td class="small"><?php echo htmlspecialchars($r['sample_type']); ?></td>
                        <td class="fw-bold">
                            <?php echo $r['status']==='Verified' ? htmlspecialchars($r['result_value']) : '<span class="text-warning">Analysis in Progress</span>'; ?>
                        </td>
                        <td>
                            <?php if ($r['status']==='Verified'): ?>
                                <span class="badge bg-<?php echo $r['flag']=='Normal'?'success':($r['flag']=='Critical'?'danger':'warning text-dark'); ?>">
                                    <?php echo htmlspecialchars($r['flag']); ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="small"><?php echo htmlspecialchars($r['reference_range'] . ' ' . $r['unit']); ?></td>
                    </tr>
                    <?php if (!empty($r['scientist_comment']) && $r['status']==='Verified'): ?>
                    <tr>
                        <td colspan="5" class="bg-light small">
                            <strong>Scientist Remark:</strong> <?php echo htmlspecialchars($r['scientist_comment']); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row pt-4 mt-4 border-top align-items-end">
            <div class="col-md-7 small text-muted">
                <em>Note: Clinical correlation is advised. Electronically verified reports carry full institutional validity.</em>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <div class="fw-bold">Authorized Medical Laboratory Scientist</div>
                <small class="text-muted"><?php echo LAB_NAME; ?></small>
            </div>
        </div>

        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-gaskiya px-4"><i class="fa-solid fa-print me-2"></i>Print / Save as PDF</button>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
