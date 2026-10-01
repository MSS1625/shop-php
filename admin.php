<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    // Redirect with query param for success message
    header("Location: admin.php?msg=deleted");
    exit;
}

$stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت فروشگاه</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand"><i class="fas fa-tachometer-alt me-2"></i>داشبورد مدیریت</span>
            <div>
                <a href="index.php" class="btn btn-outline-light btn-sm me-2"><i class="fas fa-globe"></i> مشاهده سایت</a>
                <a href="admin.php?logout=true" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> خروج</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">
            <h4 class="mb-0 text-secondary">لیست محصولات</h4>
            <a href="add.php" class="btn btn-success shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> افزودن محصول جدید
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>تصویر</th>
                                <th>عنوان محصول</th>
                                <th>قیمت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><img src="uploads/<?= $p['image_url'] ?>" class="thumb-img shadow-sm"></td>
                                <td class="fw-bold"><?= $p['title'] ?></td>
                                <td class="text-success"><?= number_format($p['price']) ?> تومان</td>
                                <td>
                                    <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?= $p['id'] ?>)" class="btn btn-danger btn-sm" title="حذف">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Check for delete message
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('msg') === 'deleted') {
            Swal.fire({
                icon: 'success',
                title: 'حذف شد!',
                text: 'محصول با موفقیت حذف گردید.',
                confirmButtonText: 'باشه',
                timer: 2000
            });
            window.history.replaceState(null, null, window.location.pathname);
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: "این عملیات قابل بازگشت نیست!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'admin.php?delete=' + id;
                }
            })
        }
    </script>
</body>
</html>