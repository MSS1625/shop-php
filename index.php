<?php
require 'db.php';
$stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فروشگاه دیجیتال</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-store me-2"></i>دیجی‌شاپ</a>
            <div class="ms-auto">
                <a href="login.php" class="btn btn-light text-primary btn-sm shadow-sm">
                    <i class="fas fa-user-cog me-1"></i> پنل مدیریت
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-white py-5 mb-5 text-center shadow-sm">
        <div class="container">
            <h1 class="display-4 fw-bold text-dark">بهترین محصولات دیجیتال</h1>
            <p class="lead text-muted">خرید امن، سریع و با بهترین قیمت</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row g-4">
            <?php foreach ($products as $p): ?>
            <div class="col-md-4 col-sm-6 col-lg-3">
                <div class="card h-100">
                    <div class="product-img-container">
                        <img src="uploads/<?= $p['image_url'] ?>" alt="<?= $p['title'] ?>">
                    </div>
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title fw-bold text-dark"><?= $p['title'] ?></h5>
                        <p class="card-text text-success fw-bold fs-5 mt-auto">
                            <?= number_format($p['price']) ?> <span class="fs-6 text-muted">تومان</span>
                        </p>
                        <button class="btn btn-outline-primary w-100 mt-2">
                            <i class="fas fa-shopping-cart me-1"></i> افزودن به سبد
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <p class="mb-0">طراحی شده با ❤️ توسط محمد صادق صداقت</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>