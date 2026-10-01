<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $price = $_POST['price'];
    
    $imageName = time() . '_' . $_FILES['image']['name'];
    $target = "uploads/" . $imageName;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $conn->prepare("INSERT INTO products (title, price, image_url) VALUES (?, ?, ?)");
        $stmt->execute([$title, $price, $imageName]);
        header("Location: admin.php");
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>افزودن محصول</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <h4 class="mb-4 text-center text-primary"><i class="fas fa-box-open me-2"></i>افزودن محصول جدید</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">نام محصول</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">قیمت (تومان)</label>
                            <input type="number" class="form-control" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تصویر محصول</label>
                            <input type="file" class="form-control" name="image" id="imgInp" required>
                            <div class="mt-3 text-center">
                                <img id="blah" src="#" alt="پیش نمایش" style="max-height: 150px; display:none; border-radius:10px;" class="shadow-sm"/>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> ذخیره محصول
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
    
    <script>
        imgInp.onchange = evt => {
            const [file] = imgInp.files
            if (file) {
                blah.src = URL.createObjectURL(file)
                blah.style.display = "block"
            }
        }
    </script>
</body>
</html>