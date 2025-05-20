<?php
include '../includes/db.php';
include '../includes/header.php';

$location = isset($_GET['location']) ? sanitize($_GET['location']) : '';
$price = isset($_GET['price']) ? sanitize($_GET['price']) : '';
$rooms = isset($_GET['rooms']) ? sanitize($_GET['rooms']) : '';

// Build query
$sql = "SELECT * FROM properties WHERE 1=1";
$params = [];

if (!empty($location)) {
    $sql .= " AND location LIKE :location";
    $params[':location'] = "%$location%";
}

if (!empty($price)) {
    $priceRange = explode('-', $price);
    $sql .= " AND price BETWEEN :min_price AND :max_price";
    $params[':min_price'] = $priceRange[0];
    $params[':max_price'] = $priceRange[1];
}

if (!empty($rooms)) {
    $sql .= " AND rooms >= :rooms";
    $params[':rooms'] = $rooms;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
    <h2 class="mb-4">Hasil Pencarian</h2>
    
    <div class="row">
        <?php if (count($properties) > 0): ?>
            <?php foreach ($properties as $property): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <img src="../assets/images/properties/<?= $property['image'] ?>" class="card-img-top" alt="<?= $property['title'] ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $property['title'] ?></h5>
                            <p class="card-text"><i class="fas fa-map-marker-alt"></i> <?= $property['location'] ?></p>
                            <p class="card-text"><i class="fas fa-bed"></i> <?= $property['rooms'] ?> Kamar</p>
                            <p class="card-text"><strong>Rp <?= number_format($property['price'], 0, ',', '.') ?>/bulan</strong></p>
                            <div class="amenities mb-3">
                                <?php 
                                $amenities = explode(',', $property['amenities']);
                                foreach ($amenities as $amenity) {
                                    echo '<span class="badge bg-secondary me-1">'.$amenity.'</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="property-detail.php?id=<?= $property['id'] ?>" class="btn btn-primary w-100">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning">
                    Tidak ada properti yang ditemukan dengan kriteria tersebut.
                </div>
                <a href="../index.php" class="btn btn-primary">Kembali ke Beranda</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
