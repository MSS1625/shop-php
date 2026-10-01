<?php
session_start();
require 'db.php';

if (isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit;
}

$check = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($check == 0) {
    $pass = password_hash('1234', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password) VALUES ('admin', '$pass')");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin'] = $user['id'];
        header("Location: admin.php");
        exit;
    } else {
        $error = "نام کاربری یا رمز عبور اشتباه است";
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="card p-4 shadow-lg" style="width: 100%; max-width: 400px;">
            <div class="text-center mb-4">
                <i class="fas fa-user-shield fa-4x text-primary mb-3"></i>
                <h3 class="fw-bold text-primary">ورود مدیر</h3>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div><?= $error ?></div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" name="username" placeholder="نام کاربری" required>
                    <label><i class="fas fa-user me-1"></i> نام کاربری</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" name="password" placeholder="رمز عبور" required>
                    <label><i class="fas fa-lock me-1"></i> رمز عبور</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="fas fa-sign-in-alt me-2"></i> ورود به سیستم
                </button>
            </form>
            <div class="text-center mt-3">
                <a href="index.php" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left"></i> بازگشت به فروشگاه
                </a>
            </div>
        </div>
    </div>
</body>
</html>