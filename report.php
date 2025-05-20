<?php
include '../includes/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's rented properties
$stmt = $pdo->prepare("SELECT p.id, p.title 
                       FROM properties p
                       JOIN rentals r ON p.id = r.property_id
                       WHERE r.user_id = ? AND r.status = 'active'");
$stmt->execute([$user_id]);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process report submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $property_id = sanitize($_POST['property_id']);
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $urgency = sanitize($_POST['urgency']);
    
    // Handle file upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/reports/';
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = $file_name;
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO reports (user_id, property_id, title, description, urgency, image_path, status) 
                              VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $property_id, $title, $description, $urgency, $image_path]);
        
        $success = "Laporan Anda telah berhasil dikirim. Kami akan segera menindaklanjuti.";
    } catch (Exception $e) {
        $error = "Terjadi kesalahan saat mengirim laporan: " . $e->getMessage();
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Laporkan Masalah</h4>
                </div>
                <div class="card-body">
                    <?php if(isset($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php elseif(isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="property_id" class="form-label">Kontrakan</label>
                            <select class="form-select" id="property_id" name="property_id" required>
                                <option value="">Pilih Kontrakan</option>
                                <?php foreach ($properties as $property): ?>
                                    <option value="<?= $property['id'] ?>"><?= $property['title'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Judul Laporan</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi Masalah</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="urgency" class="form-label">Tingkat Urgensi</label>
                            <select class="form-select" id="urgency" name="urgency" required>
                                <option value="low">Rendah (Masalah tidak mendesak)</option>
                                <option value="medium" selected>Sedang (Perlu diperbaiki segera)</option>
                                <option value="high">Tinggi (Membahayakan/Mengganggu)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Upload Foto (Opsional)</label>
                            <input class="form-control" type="file" id="image" name="image" accept="image/*">
                            <small class="text-muted">Upload foto untuk membantu kami memahami masalahnya</small>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Kirim Laporan</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Riwayat Laporan</h5>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $pdo->prepare("SELECT r.*, p.title as property_title 
                                           FROM reports r
                                           JOIN properties p ON r.property_id = p.id
                                           WHERE r.user_id = ?
                                           ORDER BY r.created_at DESC");
                    $stmt->execute([$user_id]);
                    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($reports) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($reports as $report): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= $report['title'] ?></h6>
                                        <small class="text-muted"><?= date('d M Y', strtotime($report['created_at'])) ?></small>
                                    </div>
                                    <p class="mb-1"><?= $report['property_title'] ?></p>
                                    <small class="text-muted">Status: 
                                        <span class="badge bg-<?= 
                                            $report['status'] == 'resolved' ? 'success' : 
                                            ($report['status'] == 'in_progress' ? 'warning' : 'secondary') 
                                        ?>">
                                            <?= ucfirst(str_replace('_', ' ', $report['status'])) ?>
                                        </span>
                                    </small>
                                    <?php if (!empty($report['image_path'])): ?>
                                        <div class="mt-2">
                                            <img src="../assets/images/reports/<?= $report['image_path'] ?>" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Belum ada riwayat laporan</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
