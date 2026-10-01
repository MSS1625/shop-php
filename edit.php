<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $price = $_POST['price'];
    
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $imageName);
        $stmt = $conn->prepare("UPDATE products SET title=?, price=?, image_url=? WHERE id=?");
        $stmt->execute([$title, $price, $imageName, $id]);
    } else {
        $stmt = $conn->prepare("UPDATE products SET title=?, price=? WHERE id=?");
        $stmt->execute([$title, $price, $id]);
    }
    header("Location: admin.php");
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ویرایش محصول</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <h4 class="mb-4 text-center text-warning"><i class="fas fa-edit me-2"></i>ویرایش محصول</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">نام محصول</label>
                            <input type="text" class="form-control" name="title" value="<?= $product['title'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">قیمت (تومان)</label>
                            <input type="number" class="form-control" name="price" value="<?= $product['price'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تغییر تصویر (اختیاری)</label>
                            <input type="file" class="form-control" name="image">
                            <div class="mt-3 text-center">
                                <label class="d-block text-muted small mb-1">تصویر فعلی:</label>
                                <img src="uploads/<?= $product['image_url'] ?>" style="height: 100px; border-radius:10px;" class="shadow-sm">
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning text-white">
                                <i class="fas fa-sync-alt me-1"></i> بروزرسانی
                            </button>
                            <a href="admin.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-1"></i> بازگشت
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>