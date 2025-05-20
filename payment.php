<?php
include '../includes/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['property_id'])) {
    header("Location: ../index.php");
    exit();
}

$property_id = sanitize($_GET['property_id']);
$user_id = $_SESSION['user_id'];

// Get property details
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header("Location: ../index.php");
    exit();
}

// Check if user already rented this property
$rent_stmt = $pdo->prepare("SELECT * FROM rentals WHERE property_id = ? AND user_id = ? AND status = 'active'");
$rent_stmt->execute([$property_id, $user_id]);
$rental = $rent_stmt->fetch(PDO::FETCH_ASSOC);

// Process payment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = sanitize($_POST['payment_method']);
    $amount = $property['price'];
    $invoice_number = 'INV-' . time() . '-' . $user_id;
    
    try {
        $pdo->beginTransaction();
        
        // Create payment record
        $stmt = $pdo->prepare("INSERT INTO payments (invoice_number, user_id, property_id, amount, payment_method, status) 
                              VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$invoice_number, $user_id, $property_id, $amount, $payment_method]);
        
        // Create or update rental record
        if ($rental) {
            $stmt = $pdo->prepare("UPDATE rentals SET last_payment_date = NOW(), next_payment_date = DATE_ADD(NOW(), INTERVAL 1 MONTH) 
                                  WHERE id = ?");
            $stmt->execute([$rental['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO rentals (user_id, property_id, start_date, last_payment_date, next_payment_date, status) 
                                  VALUES (?, ?, NOW(), NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), 'active')");
            $stmt->execute([$user_id, $property_id]);
        }
        
        $pdo->commit();
        
        // Redirect to payment gateway (simulated)
        $_SESSION['invoice_number'] = $invoice_number;
        header("Location: payment-process.php?method=$payment_method");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Terjadi kesalahan saat memproses pembayaran: " . $e->getMessage();
    }
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Pembayaran Sewa</h4>
                </div>
                <div class="card-body">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <div class="property-info mb-4">
                        <h5><?= $property['title'] ?></h5>
                        <p><i class="fas fa-map-marker-alt"></i> <?= $property['location'] ?></p>
                        <p><strong>Harga Sewa:</strong> Rp <?= number_format($property['price'], 0, ',', '.') ?>/bulan</p>
                    </div>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="bankTransfer" value="bank_transfer" checked>
                                <label class="form-check-label" for="bankTransfer">
                                    <i class="fas fa-university"></i> Transfer Bank
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="gopay" value="gopay">
                                <label class="form-check-label" for="gopay">
                                    <i class="fab fa-google-pay"></i> GoPay
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="ovo" value="ovo">
                                <label class="form-check-label" for="ovo">
                                    <i class="fas fa-mobile-alt"></i> OVO
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="dana" value="dana">
                                <label class="form-check-label" for="dana">
                                    <i class="fas fa-wallet"></i> DANA
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Jumlah Pembayaran</label>
                            <input type="text" class="form-control" value="Rp <?= number_format($property['price'], 0, ',', '.') ?>" readonly>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Bayar Sekarang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Ringkasan Pembayaran</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <td>Harga Sewa</td>
                            <td class="text-end">Rp <?= number_format($property['price'], 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>Biaya Admin</td>
                            <td class="text-end">Rp 5.000</td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Total Pembayaran</td>
                            <td class="text-end">Rp <?= number_format($property['price'] + 5000, 0, ',', '.') ?></td>
                        </tr>
                    </table>
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i> Pembayaran akan memperpanjang masa sewa selama 1 bulan.
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Riwayat Pembayaran</h5>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? AND property_id = ? ORDER BY created_at DESC LIMIT 5");
                    $stmt->execute([$user_id, $property_id]);
                    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($payments) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($payments as $payment): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <div>
                                        <small class="text-muted"><?= date('d M Y', strtotime($payment['created_at'])) ?></small><br>
                                        <?= strtoupper($payment['payment_method']) ?>
                                    </div>
                                    <div class="text-end">
                                        Rp <?= number_format($payment['amount'], 0, ',', '.') ?><br>
                                        <span class="badge bg-<?= $payment['status'] == 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($payment['status']) ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Belum ada riwayat pembayaran</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
