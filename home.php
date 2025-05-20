<div class="hero-section bg-primary text-white py-5">
    <div class="container text-center">
        <h1 class="display-4">Temukan Kontrakan Ideal Anda</h1>
        <p class="lead">Cari, sewa, dan kelola kontrakan dengan mudah secara digital</p>
        
        <form action="pages/search.php" method="get" class="mt-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="location" class="form-control" placeholder="Lokasi (Kota/Kecamatan)">
                </div>
                <div class="col-md-3">
                    <select name="price" class="form-select">
                        <option value="">Harga Sewa</option>
                        <option value="500000-1000000">Rp 500rb - 1jt</option>
                        <option value="1000000-2000000">Rp 1jt - 2jt</option>
                        <option value="2000000-5000000">Rp 2jt - 5jt</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="rooms" class="form-select">
                        <option value="">Jumlah Kamar</option>
                        <option value="1">1 Kamar</option>
                        <option value="2">2 Kamar</option>
                        <option value="3">3+ Kamar</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-light w-100">Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="featured-properties py-5">
    <div class="container">
        <h2 class="text-center mb-4">Kontrakan Populer</h2>
        <div class="row">
            <?php
            include '../includes/db.php';
            $stmt = $pdo->query("SELECT * FROM properties ORDER BY RAND() LIMIT 3");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="assets/images/properties/'.$row['image'].'" class="card-img-top" alt="'.$row['title'].'">
                        <div class="card-body">
                            <h5 class="card-title">'.$row['title'].'</h5>
                            <p class="card-text"><i class="fas fa-map-marker-alt"></i> '.$row['location'].'</p>
                            <p class="card-text"><i class="fas fa-bed"></i> '.$row['rooms'].' Kamar</p>
                            <p class="card-text"><strong>Rp '.number_format($row['price'], 0, ',', '.').'/bulan</strong></p>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="property-detail.php?id='.$row['id'].'" class="btn btn-primary w-100">Lihat Detail</a>
                        </div>
                    </div>
                </div>';
            }
            ?>
        </div>
    </div>
</div>

<div class="how-it-works bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-4">Cara Kerja KONTRAKAN DIGITAL</h2>
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="p-4 bg-white rounded shadow">
                    <div class="mb-3"><i class="fas fa-search fa-3x text-primary"></i></div>
                    <h4>Cari Kontrakan</h4>
                    <p>Temukan kontrakan sesuai kebutuhan Anda dengan filter lengkap</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 bg-white rounded shadow">
                    <div class="mb-3"><i class="fas fa-file-signature fa-3x text-primary"></i></div>
                    <h4>Sewa Online</h4>
                    <p>Proses sewa dilakukan secara online dengan dokumen digital</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 bg-white rounded shadow">
                    <div class="mb-3"><i class="fas fa-credit-card fa-3x text-primary"></i></div>
                    <h4>Bayar & Kelola</h4>
                    <p>Pembayaran bulanan dan pengaduan masalah melalui platform</p>
                </div>
            </div>
        </div>
    </div>
</div>
