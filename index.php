<?php
$page_title = "Home";
require_once 'includes/header.php';

$conn = getDB();
$booking_msg = "";
$booking_err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_test'])) {
    $p_name = trim($_POST['patient_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $p_date = trim($_POST['preferred_date'] ?? '');
    $test_req = trim($_POST['test_requested'] ?? '');
    $col_type = trim($_POST['collection_type'] ?? 'Walk-in Clinic');

    if ($p_name && $phone && $p_date && $test_req) {
        $ref = 'GSK-BK-' . strtoupper(substr(md5(uniqid()), 0, 6));
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO appointments (booking_ref, patient_name, phone, email, preferred_date, test_requested, collection_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $ref, $p_name, $phone, $email, $p_date, $test_req, $col_type);
            if ($stmt->execute()) {
                $booking_msg = "Appointment confirmed! Your Booking Reference is <strong>$ref</strong>. Present this code at reception.";
            } else {
                $booking_err = "Unable to save booking right now. Please call our reception desk.";
            }
            $stmt->close();
        } else {
            $booking_msg = "Demo Mode: Booking Reference <strong>$ref</strong> generated (Import database.sql to persist records).";
        }
    } else {
        $booking_err = "Please fill in all required fields.";
    }
}

$tests = [];
if ($conn) {
    $res = $conn->query("SELECT * FROM test_catalog ORDER BY department, test_name");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $tests[] = $row;
        }
    }
}
if (empty($tests)) {
    $tests = [
        ['test_code'=>'HEM-001','test_name'=>'Full Blood Count (FBC)','department'=>'Hematology','sample_type'=>'EDTA Whole Blood','price'=>4500],
        ['test_code'=>'HEM-002','test_name'=>'Malaria Parasite (MP) Microscopy','department'=>'Parasitology','sample_type'=>'EDTA Whole Blood','price'=>1500],
        ['test_code'=>'CHM-001','test_name'=>'Fasting Blood Sugar (FBS)','department'=>'Clinical Chemistry','sample_type'=>'Fluoride Oxalate','price'=>2000],
        ['test_code'=>'CHM-002','test_name'=>'Lipid Profile','department'=>'Clinical Chemistry','sample_type'=>'Plain Serum','price'=>8500],
        ['test_code'=>'CHM-003','test_name'=>'Liver Function Test (LFT)','department'=>'Clinical Chemistry','sample_type'=>'Plain Serum','price'=>9000],
        ['test_code'=>'MOL-001','test_name'=>'Hepatitis B Surface Antigen (HBsAg)','department'=>'Molecular & Virology','sample_type'=>'Serum','price'=>3500],
    ];
}
?>

<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge hero-badge rounded-pill px-3 py-2 mb-3">
                    <i class="fa-solid fa-shield-virus me-1"></i> Accredited Clinical &amp; Research Facility
                </span>
                <h1 class="display-5 fw-bold mb-3"><?php echo LAB_NAME; ?></h1>
                <p class="lead opacity-90 mb-4">
                    Delivering accurate, reproducible, and timely laboratory diagnostics powered by modern automated analyzers and expert medical laboratory scientists.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="verify_result.php" class="btn btn-accent btn-lg px-4 rounded-pill shadow">
                        <i class="fa-solid fa-file-waveform me-2"></i>Verify / Print Lab Result
                    </a>
                    <a href="#appointment" class="btn btn-outline-light btn-lg px-4 rounded-pill">
                        <i class="fa-solid fa-calendar-check me-2"></i>Book Diagnostic Test
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="hero-logo-card mx-auto" style="max-width: 360px;">
                    <img src="assets/img/logo.png" alt="Gaskiya Medical Logo" class="img-fluid rounded-4 shadow-lg mb-3" style="max-height: 210px;">
                    <h5 class="fw-bold mb-1">Gaskiya Medical</h5>
                    <p class="small text-info mb-0"><?php echo LAB_TAGLINE; ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="p-3 bg-light rounded stat-card">
                    <h4 class="fw-bold text-success mb-0">99.8%</h4>
                    <small class="text-muted">Internal QC Precision</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3 bg-light rounded stat-card">
                    <h4 class="fw-bold text-success mb-0">&lt; 2 Hrs</h4>
                    <small class="text-muted">STAT Emergency Turnaround</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3 bg-light rounded stat-card">
                    <h4 class="fw-bold text-success mb-0">150+</h4>
                    <small class="text-muted">Validated Clinical Assays</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3 bg-light rounded stat-card">
                    <h4 class="fw-bold text-success mb-0">24/7</h4>
                    <small class="text-muted">Online Result Verification</small>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="services" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-uppercase fw-bold text-success small">Our Core Specialties</span>
            <h2 class="fw-bold mt-1">Diagnostic &amp; Research Departments</h2>
            <p class="text-muted mx-auto" style="max-width: 650px;">Comprehensive analytical services supporting clinicians, hospitals, research institutions, and walk-in patients.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card dept-card h-100 shadow-sm p-4">
                    <div class="dept-icon mb-3"><i class="fa-solid fa-droplet"></i></div>
                    <h5 class="fw-bold">Hematology &amp; Serology</h5>
                    <p class="text-muted small mb-0">Automated 5-part differential FBC, coagulation profiles, blood grouping, cross-matching, and hemoglobin electrophoresis.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card dept-card h-100 shadow-sm p-4">
                    <div class="dept-icon mb-3"><i class="fa-solid fa-vial-circle-check"></i></div>
                    <h5 class="fw-bold">Clinical Chemistry</h5>
                    <p class="text-muted small mb-0">Liver &amp; renal function panels, lipid profiles, HbA1c, cardiac biomarkers, electrolytes, and hormonal assays.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card dept-card h-100 shadow-sm p-4">
                    <div class="dept-icon mb-3"><i class="fa-solid fa-bacteria"></i></div>
                    <h5 class="fw-bold">Medical Microbiology</h5>
                    <p class="text-muted small mb-0">Culture &amp; antimicrobial susceptibility testing (MCS), CSF microscopy, parasitology, and fungal diagnostics.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card dept-card h-100 shadow-sm p-4">
                    <div class="dept-icon mb-3"><i class="fa-solid fa-dna"></i></div>
                    <h5 class="fw-bold">Molecular &amp; Research</h5>
                    <p class="text-muted small mb-0">Nucleic acid amplification (PCR), viral load quantification, infectious disease screening, and biomedical research support.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="catalog" class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="text-uppercase fw-bold text-success small">Transparent Pricing</span>
                <h2 class="fw-bold mb-0">Diagnostic Test Menu</h2>
            </div>
            <div class="mt-3 mt-md-0" style="min-width: 280px;">
                <input type="text" id="testSearch" class="form-control" placeholder="Search test name or department...">
            </div>
        </div>
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover align-middle mb-0" id="testTable">
                <thead class="table-dark">
                    <tr>
                        <th>Code</th>
                        <th>Test Name</th>
                        <th>Department</th>
                        <th>Sample Requirement</th>
                        <th class="text-end">Fee (₦)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests as $t): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($t['test_code']); ?></span></td>
                        <td class="fw-semibold"><?php echo htmlspecialchars($t['test_name']); ?></td>
                        <td><?php echo htmlspecialchars($t['department']); ?></td>
                        <td class="small text-muted"><?php echo htmlspecialchars($t['sample_type']); ?></td>
                        <td class="text-end fw-bold text-success">₦<?php echo number_format((float)$t['price'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section id="appointment" class="py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <span class="text-uppercase fw-bold text-success small">Fast-Track Diagnostics</span>
                <h2 class="fw-bold mt-1 mb-3">Book a Lab Test or Home Sample Collection</h2>
                <p class="text-muted">Skip the queue by pre-registering your laboratory investigation online, or request a certified phlebotomist for home/office sample collection.</p>
                <div class="card border-0 bg-white shadow-sm p-3 mb-3">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-house-medical fs-3 text-success me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-0">Home &amp; Corporate Phlebotomy</h6>
                            <small class="text-muted">Cold-chain specimen transport directly to our analytical floor.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm p-4 rounded-4">
                    <h4 class="fw-bold mb-3">Patient Test Booking Form</h4>
                    <?php if ($booking_msg): ?>
                        <div class="alert alert-success"><?php echo $booking_msg; ?></div>
                    <?php endif; ?>
                    <?php if ($booking_err): ?>
                        <div class="alert alert-danger"><?php echo $booking_err; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="index.php#appointment">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Patient Full Name *</label>
                                <input type="text" name="patient_name" class="form-control" required placeholder="e.g. Amina Suleiman">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phone Number *</label>
                                <input type="text" name="phone" class="form-control" required placeholder="e.g. 0803 123 4567">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email Address (Optional)</label>
                                <input type="email" name="email" class="form-control" placeholder="patient@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Preferred Date *</label>
                                <input type="date" name="preferred_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Collection Mode *</label>
                                <select name="collection_type" class="form-select">
                                    <option value="Walk-in Clinic">Walk-in Clinic</option>
                                    <option value="Home Sample Collection">Home Sample Collection</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Requested Investigation(s) *</label>
                                <input type="text" name="test_requested" class="form-control" required placeholder="e.g. FBC, Malaria Parasite, FBS">
                            </div>
                            <div class="col-12">
                                <button type="submit" name="book_test" class="btn btn-gaskiya w-100 py-2 fw-bold">
                                    <i class="fa-solid fa-paper-plane me-2"></i>Confirm Test Appointment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('testSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#testTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
