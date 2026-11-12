<?php
$page_title = "Staff LIMS Login";
require_once 'includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $conn = getDB();
    if ($conn) {
        $stmt = $conn->prepare("SELECT id, full_name, username, password_hash, role FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($user = $res->fetch_assoc()) {
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                header("Location: dashboard.php");
                exit;
            }
        }
        $error = "Invalid username or password.";
    } else {
        $error = "Database connection not configured yet. Please check config/db.php and import database.sql.";
    }
}
?>

<div class="container py-5 my-auto">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow rounded-4 p-4">
                <div class="text-center mb-4">
                    <img src="assets/img/logo.png" alt="Logo" style="height: 72px; border-radius: 12px;" class="mb-2">
                    <h4 class="fw-bold mb-0">LIMS Staff Portal</h4>
                    <small class="text-muted"><?php echo LAB_SHORT; ?></small>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger small"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="username" class="form-control" required placeholder="admin">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-gaskiya w-100 py-2 fw-bold">Sign In to LIMS</button>
                </form>
                <div class="mt-3 text-center">
                    <small class="text-muted">Default Admin: <code>admin</code> / <code>password123</code></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
