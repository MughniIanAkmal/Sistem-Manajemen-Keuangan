<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = $_POST['tanggal'] ?? '';
    $jumlah = $_POST['jumlah'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $jenis = $_POST['jenis'] ?? '';
    $kategori = $_POST['kategori'] ?? '';

    // Validasi
    if (!strtotime($tanggal)) $error .= "Tanggal tidak valid.<br>";
    if (!is_numeric($jumlah) || $jumlah <= 0) $error .= "Jumlah harus angka positif.<br>";
    if (!in_array($jenis, ['pemasukan', 'pengeluaran'])) $error .= "Jenis transaksi tidak valid.<br>";
    if (strlen(trim($deskripsi)) == 0) $error .= "Deskripsi tidak boleh kosong.<br>";
    if (empty($kategori) && $jenis == 'pengeluaran') $error .= "Kategori wajib diisi untuk pengeluaran.<br>";

    if (empty($error)) {
        if (empty($kategori) && $jenis == 'pengeluaran') {
            $deskripsi_lower = strtolower($deskripsi);
            $auto_kategori = 'lainnya';
            $kata_kunci = [
                'makan' => 'makanan',
                'transport' => 'transportasi',
                'belanja' => 'belanja',
                'hibur' => 'hiburan',
                'kesehat' => 'kesehatan',
                'pendidik' => 'pendidikan',
                'tagih' => 'tagihan',
                'gaji' => 'gaji',
                'invest' => 'investasi'
            ];
            foreach ($kata_kunci as $keyword => $cat) {
                if (strpos($deskripsi_lower, $keyword) !== false) {
                    $auto_kategori = $cat;
                    break;
                }
            }
            $kategori = $auto_kategori;
        }

        $transaksi = [
            'id' => uniqid(),
            'tanggal' => $tanggal,
            'jumlah' => (float)$jumlah,
            'deskripsi' => trim($deskripsi),
            'jenis' => $jenis,
            'kategori' => $kategori
        ];

        $file = 'data/transaksi.txt';
        $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        $data[] = $transaksi;
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

        $message = "✅ Transaksi berhasil disimpan!";
    }
}

function bacaKategori() {
    if (!file_exists('data/kategori.txt')) {
        return ['makanan', 'transportasi', 'belanja', 'hiburan', 'kesehatan', 'pendidikan', 'tagihan', 'gaji', 'investasi', 'lainnya'];
    }
    $data = file_get_contents('data/kategori.txt');
    return json_decode($data, true) ?: [];
}

$kategori_list = bacaKategori();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>➕ Input Transaksi Baru</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="logo">💰</div>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="input_transaksi.php" class="active">Input Transaksi</a></li>
                <li><a href="laporan_bulanan.php">Laporan Bulanan</a></li>
                <li><a href="visualisasi_data.php">Visualisasi Data</a></li>
                <li><a href="kelola_kategori.php">Kelola Kategori</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h2 class="card-title">➕ Input Transaksi Baru</h2>

            <?php if ($message): ?>
                <div class="success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="tanggal">📅 Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label for="jumlah">💵 Jumlah (Rp)</label>
                    <input type="number" id="jumlah" name="jumlah" step="0.01" min="0.01" required placeholder="Contoh: 50000">
                </div>

                <div class="form-group">
                    <label for="deskripsi">📝 Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" required placeholder="Ex: Beli makan siang, Bayar tagihan listrik"></textarea>
                </div>

                <div class="form-group">
                    <label>🏷️ Tipe Transaksi</label>
                    <div class="radio-group">
                        <label><input type="radio" name="jenis" value="pemasukan" required> Pemasukan</label>
                        <label><input type="radio" name="jenis" value="pengeluaran" required> Pengeluaran</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="kategori">📂 Kategori</label>
                    <select id="kategori" name="kategori">
                        <option value="">Biarkan kosong untuk Klasifikasi Otomatis</option>
                        <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?= htmlspecialchars($kat) ?>"><?= htmlspecialchars($kat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit"><span>💾</span> Simpan Transaksi</button>
                    <button type="reset" class="reset"><span>🔄</span> Reset</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        📊 Sistem Manajemen Keuangan - Ian Co | © <?= date('Y') ?>
    </footer>
</body>
</html>