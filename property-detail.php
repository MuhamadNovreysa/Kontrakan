<?php
include '../includes/db.php';
include '../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$property_id = sanitize($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header("Location: ../index.php");
    exit();
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="property-images mb-4">
                <img src="../assets/images/properties/<?= $property['image'] ?>" class="img-fluid rounded" alt="<?= $property['title'] ?>">
            </div>
            
            <h2><?= $property['title'] ?></h2>
            <p class="text-muted"><i class="fas fa-map-marker-alt"></i> <?= $property['location'] ?></p>
            
            <div class="d-flex justify-content-between mb-4">
                <div>
                    <span class="badge bg-primary fs-6">Rp <?= number_format($property['price'], 0, ',', '.') ?>/bulan</span>
                </div>
                <div>
                    <span class="me-3"><i class="fas fa-bed"></i> <?= $property['rooms'] ?> Kamar</span>
                    <span><i class="fas fa-ruler-combined"></i> <?= $property['size'] ?> m²</span>
                </div>
            </div>
            
            <div class="amenities mb-4">
                <h4>Fasilitas</h4>
                <div class="row">
                    <?php 
                    $amenities = explode(',', $property['amenities']);
                    foreach ($amenities as $amenity) {
                        echo '<div class="col-md-4 mb-2"><i class="fas fa-check text-success"></i> '.trim($amenity).'</div>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="description mb-4">
                <h4>Deskripsi</h4>
                <p><?= nl2br($property['description']) ?></p>
            </div>
            
            <div class="rules mb-4">
                <h4>Peraturan</h4>
                <ul>
                    <?php 
                    $rules = explode("\n", $property['rules']);
                    foreach ($rules as $rule) {
                        if (!empty(trim($rule))) {
                            echo '<li>'.trim($rule).'</li>';
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Pemilik</h5>
                </div>
                <div class="card-body">
                    <?php
                    $owner_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $owner_stmt->execute([$property['owner_id']]);
                    $owner = $owner_stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-user-circle fa-3x"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= $owner['name'] ?></h5>
                            <small class="text-muted">Pemilik Kontrakan</small>
                        </div>
                    </div>
                    
                    <div class="contact-info mb-3">
                        <p><i class="fas fa-phone me-2"></i> <?= $owner['phone'] ?></p>
                        <p><i class="fas fa-envelope me-2"></i> <?= $owner['email'] ?></p>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="payment.php?property_id=<?= $property['id'] ?>" class="btn btn-primary">Sewa Sekarang</a>
                        <?php else: ?>
                            <a href="../pages/login.php" class="btn btn-primary">Login untuk Menyewa</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Lokasi</h5>
                </div>
                <div class="card-body">
                    <div id="propertyMap" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize map
    function initMap() {
        const location = { lat: <?= $property['latitude'] ?>, lng: <?= $property['longitude'] ?> };
        const map = new google.maps.Map(document.getElementById("propertyMap"), {
            zoom: 15,
            center: location,
        });
        new google.maps.Marker({
            position: location,
            map: map,
            title: "<?= $property['title'] ?>",
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>

<?php include '../includes/footer.php'; ?>
