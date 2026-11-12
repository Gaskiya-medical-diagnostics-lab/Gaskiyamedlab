<?php
$page_title = "LIMS Dashboard";
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = getDB();
$alert = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_patient'])) {
    $full_name = trim($_POST['full_name']);
    $age = trim($_POST['age']);
    $gender = $_POST['gender'];
    $phone = trim($_POST['phone']);
    $ref_doc = trim($_POST['referring_doctor']) ?: 'Self / Walk-in';
    $test_id = (int)$_POST['test_id'];

    $lab_no = 'GSK-' . date('ym') . '-' . rand(1000, 9999);
    $stmt = $conn->prepare("INSERT INTO patients (lab_no, full_name, age, gender, phone, referring_doctor) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $lab_no, $full_name, $age, $gender, $phone, $ref_doc);
    if ($stmt->execute()) {
        $patient_id = $stmt->insert_id;
        $acc_no = '';
        if ($test_id > 0) {
            $acc_no = 'ACC-' . date('ymd') . '-' . rand(100, 999);
            $r_stmt = $conn->prepare("INSERT INTO lab_requests (accession_no, patient_id, test_id) VALUES (?, ?, ?)");
            $r_stmt->bind_param("sii", $acc_no, $patient_id, $test_id);
            $r_stmt->execute();
        }
        $alert = "<div class='alert alert-success'>Patient registered! Lab Number: <strong>$lab_no</strong> | Accession No: <strong>$acc_no</strong></div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_result'])) {
    $req_id = (int)$_POST['request_id'];
    $result_val = trim($_POST['result_value']);
    $flag = $_POST['flag'];
    $comment = trim($_POST['scientist_comment']);
    $verifier = $_SESSION['full_name'];

    $stmt = $conn->prepare("UPDATE lab_requests SET result_value=?, flag=?, scientist_comment=?, verified_by=?, status='Verified', completed_at=NOW() WHERE id=?");
    $stmt->bind_param("ssssi", $result_val, $flag, $comment, $verifier, $req_id);
    if ($stmt->execute()) {
        $alert = "<div class='alert alert-success'>Laboratory result verified and published for patient printing!</div>";
    }
}

$total_patients = $conn->query("SELECT COUNT(*) c FROM patients")->fetch_assoc()['c'] ?? 0;
$pending_tests = $conn->query("SELECT COUNT(*) c FROM lab_requests WHERE status='Pending'")->fetch_assoc()['c'] ?? 0;
$verified_tests = $conn->query("SELECT COUNT(*) c FROM lab_requests WHERE status='Verified'")->fetch_assoc()['c'] ?? 0;
$bookings_count = $conn->query("SELECT COUNT(*) c FROM appointments")->fetch_assoc()['c'] ?? 0;

$catalog = $conn->query("SELECT id, test_code, test_name, price FROM test_catalog ORDER BY test_name");
$worklist = $conn->query("
    SELECT r.*, p.lab_no, p.full_name, p.age, p.gender, t.test_name, t.reference_range, t.unit
    FROM lab_requests r
    JOIN patients p ON r.patient_id = p.id
    JOIN test_catalog t ON r.test_id = t.id
    ORDER BY r.requested_at DESC LIMIT 30
");
$appointments = $conn->query("SELECT * FROM appointments ORDER BY created_at DESC LIMIT 10");
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Laboratory Information Management System (LIMS)</h3>
            <small class="text-muted">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong> (<?php echo strtoupper($_SESSION['role']); ?>)</small>
        </div>
        <div class="mt-2 mt-md-0">
            <button class="btn btn-gaskiya" data-bs-toggle="modal" data-bs-target="#newPatientModal">
                <i class="fa-solid fa-user-plus me-1"></i> New Patient &amp; Test Order
            </button>
            <a href="logout.php" class="btn btn-outline-danger ms-2"><i class="fa-solid fa-right-from-bracket me-1"></i>Logout</a>
        </div>
    </div>

    <?php echo $alert; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted">Total Registered Patients</small>
                <h3 class="fw-bold mb-0 text-success"><?php echo $total_patients; ?></h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted">Pending Lab Analysis</small>
                <h3 class="fw-bold mb-0 text-warning"><?php echo $pending_tests; ?></h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted">Verified Reports</small>
                <h3 class="fw-bold mb-0 text-primary"><?php echo $verified_tests; ?></h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm p-3">
                <small class="text-muted">Online Appointments</small>
                <h3 class="fw-bold mb-0 text-info"><?php echo $bookings_count; ?></h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-flask-vial me-2 text-success"></i>Analytical Bench Worklist</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Lab / Accession No</th>
                        <th>Patient Info</th>
                        <th>Investigation</th>
                        <th>Result</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $worklist->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <span class="fw-bold text-success d-block"><?php echo htmlspecialchars($row['lab_no']); ?></span>
                            <small class="text-muted"><?php echo htmlspecialchars($row['accession_no']); ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold"><?php echo htmlspecialchars($row['full_name']); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($row['age']); ?> yrs / <?php echo htmlspecialchars($row['gender']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($row['test_name']); ?></td>
                        <td>
                            <?php if ($row['result_value']): ?>
                                <strong><?php echo htmlspecialchars($row['result_value']); ?></strong>
                                <span class="badge bg-<?php echo $row['flag']=='Normal'?'success':($row['flag']=='Critical'?'danger':'warning'); ?> ms-1"><?php echo $row['flag']; ?></span>
                            <?php else: ?>
                                <span class="text-muted small">Awaiting assay</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $row['status']=='Verified'?'success':'warning text-dark'; ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#resultModal<?php echo $row['id']; ?>">
                                <i class="fa-solid fa-pen-to-square"></i> Enter Result
                            </button>
                            <?php if ($row['status'] === 'Verified'): ?>
                                <a href="verify_result.php?lab_no=<?php echo urlencode($row['lab_no']); ?>" target="_blank" class="btn btn-sm btn-success">
                                    <i class="fa-solid fa-print"></i> Report
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <div class="modal fade" id="resultModal<?php echo $row['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Verify Result: <?php echo htmlspecialchars($row['test_name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                    <p class="small text-muted mb-2">
                                        Patient: <strong><?php echo htmlspecialchars($row['full_name']); ?></strong> (<?php echo $row['lab_no']; ?>)<br>
                                        Reference Range: <code><?php echo htmlspecialchars($row['reference_range'] . ' ' . $row['unit']); ?></code>
                                    </p>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Observed Result Value *</label>
                                        <input type="text" name="result_value" class="form-control" required value="<?php echo htmlspecialchars($row['result_value'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Clinical Flag</label>
                                        <select name="flag" class="form-select">
                                            <option value="Normal" <?php echo $row['flag']=='Normal'?'selected':''; ?>>Normal</option>
                                            <option value="High" <?php echo $row['flag']=='High'?'selected':''; ?>>High</option>
                                            <option value="Low" <?php echo $row['flag']=='Low'?'selected':''; ?>>Low</option>
                                            <option value="Critical" <?php echo $row['flag']=='Critical'?'selected':''; ?>>Critical / Panic Value</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Medical Scientist Remark</label>
                                        <textarea name="scientist_comment" class="form-control" rows="2"><?php echo htmlspecialchars($row['scientist_comment'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="save_result" class="btn btn-gaskiya">Authorize &amp; Publish Result</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-calendar-day me-2 text-info"></i>Recent Online Test Bookings</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking Ref</th>
                        <th>Patient Name</th>
                        <th>Phone</th>
                        <th>Date</th>
                        <th>Tests Requested</th>
                        <th>Mode</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($bk = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-bold"><?php echo htmlspecialchars($bk['booking_ref']); ?></td>
                        <td><?php echo htmlspecialchars($bk['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($bk['phone']); ?></td>
                        <td><?php echo htmlspecialchars($bk['preferred_date']); ?></td>
                        <td><?php echo htmlspecialchars($bk['test_requested']); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($bk['collection_type']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="newPatientModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Register Patient &amp; Request Investigation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Patient Full Name *</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Age *</label>
                        <input type="text" name="age" class="form-control" required placeholder="e.g. 34">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Gender *</label>
                        <select name="gender" class="form-select">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Phone Number</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Referring Clinician / Hospital</label>
                    <input type="text" name="referring_doctor" class="form-control" placeholder="Self / Walk-in">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Select Diagnostic Test *</label>
                    <select name="test_id" class="form-select" required>
                        <?php while ($c = $catalog->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>">
                                <?php echo htmlspecialchars($c['test_code'] . ' - ' . $c['test_name'] . ' (₦' . number_format($c['price']) . ')'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="register_patient" class="btn btn-gaskiya w-100">Generate Lab Number &amp; Queue Test</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
