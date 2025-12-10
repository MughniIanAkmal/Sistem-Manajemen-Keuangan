<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';
$error = '';

function bacaKategori() {
    if (!file_exists('data/kategori.txt')) {
        return ['makanan', 'transportasi', 'belanja', 'hiburan', 'kesehatan', 'pendidikan', 'tagihan', 'gaji', 'investasi', 'lainnya'];
    }
    $data = file_get_contents('data/kategori.txt');
    return json_decode($data, true) ?: [];
}

function simpanKategori($kategori_list) {
    file_put_contents('data/kategori.txt', json_encode($kategori_list, JSON_PRETTY_PRINT));
}

$kategori_list = bacaKategori();

if (isset($_GET['hapus'])) {
    $hapus = $_GET['hapus'];
    $new_list = [];
    foreach ($kategori_list as $k) {
        if ($k != $hapus) {
            $new_list[] = $k;
        }
    }
    simpanKategori($new_list);
    $message = "🗑️ Kategori '$hapus' berhasil dihapus.";
    $kategori_list = $new_list;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_kategori'])) {
    $nama_kategori = trim($_POST['nama_kategori'] ?? '');
    $kata_kunci_input = trim($_POST['kata_kunci'] ?? '');

    if (empty($nama_kategori)) {
        $error .= "Nama kategori tidak boleh kosong.<br>";
    } else {
        if (in_array($nama_kategori, $kategori_list)) {
            $error .= "Kategori sudah ada.<br>";
        } else {
            $kategori_list[] = $nama_kategori;
            simpanKategori($kategori_list);
            $message = "✅ Kategori '$nama_kategori' berhasil ditambahkan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📁 Kelola Kategori</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="logo">💰</div>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="input_transaksi.php">Input Transaksi</a></li>
                <li><a href="laporan_bulanan.php">Laporan Bulanan</a></li>
                <li><a href="visualisasi_data.php">Visualisasi Data</a></li>
                <li><a href="kelola_kategori.php" class="active">Kelola Kategori</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h2 class="card-title">📁 Kelola Kategori & Klasifikasi Otomatis</h2>

            <?php if ($message): ?>
                <div class="success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <div class="category-list">
                <?php foreach ($kategori_list as $kategori): ?>
                    <div class="category-item">
                        <span><?= htmlspecialchars($kategori) ?></span>
                        <button onclick="if(confirm('Hapus kategori <?= addslashes($kategori) ?>?')) window.location.href='kelola_kategori.php?hapus=<?= urlencode($kategori) ?>'">🗑️</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="category-form">
                <h3>➕ Tambah Kategori Baru</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="nama_kategori">Nama Kategori</label>
                        <input type="text" id="nama_kategori" name="nama_kategori" required placeholder="Contoh: Makanan">
                    </div>
                    <div class="form-group">
                        <label for="kata_kunci">Kata Kunci (opsional)</label>
                        <input type="text" id="kata_kunci" name="kata_kunci" placeholder="Contoh: makan, nasi, bakso">
                    </div>
                    <button type="submit" name="tambah_kategori"><span>➕</span> Tambah Kategori</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        📊 Sistem Manajemen Keuangan - Ian Co | © <?= date('Y') ?>
    </footer>
</body>
</html>