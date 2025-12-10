<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';
$laporan = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate'])) {
    $bulan = $_POST['bulan'] ?? 'januari';
    $tahun = $_POST['tahun'] ?? '2025';

    $bulan_map = [
        'januari' => '01', 'februari' => '02', 'maret' => '03',
        'april' => '04', 'mei' => '05', 'juni' => '06',
        'juli' => '07', 'agustus' => '08', 'september' => '09',
        'oktober' => '10', 'november' => '11', 'desember' => '12'
    ];

    if (!isset($bulan_map[$bulan])) {
        $message = "Bulan tidak valid.";
    } else {
        $bulan_tahun = "$tahun-{$bulan_map[$bulan]}";
        
        $transaksi = file_exists('data/transaksi.txt') ? json_decode(file_get_contents('data/transaksi.txt'), true) : [];

        $saldo_awal = 0;
        $total_pemasukan = 0;
        $total_pengeluaran = 0;
        $jumlah_transaksi = 0;
        $pengeluaran_per_kategori = [];

        foreach ($transaksi as $t) {
            if (substr($t['tanggal'], 0, 7) == $bulan_tahun) {
                $jumlah_transaksi++;
                if ($t['jenis'] == 'pemasukan') {
                    $total_pemasukan += $t['jumlah'];
                    $saldo_awal += $t['jumlah'];
                } else {
                    $total_pengeluaran += $t['jumlah'];
                    $saldo_awal -= $t['jumlah'];
                    $kategori = $t['kategori'];
                    if (!isset($pengeluaran_per_kategori[$kategori])) {
                        $pengeluaran_per_kategori[$kategori] = 0;
                    }
                    $pengeluaran_per_kategori[$kategori] += $t['jumlah'];
                }
            }
        }

        $laporan = [
            'bulan' => ucfirst($bulan),
            'tahun' => $tahun,
            'saldo' => $saldo_awal,
            'pemasukan' => $total_pemasukan,
            'pengeluaran' => $total_pengeluaran,
            'jumlah_transaksi' => $jumlah_transaksi,
            'pengeluaran_per_kategori' => $pengeluaran_per_kategori
        ];
    }
}

$tahun_sekarang = date('Y');
$tahun_list = range($tahun_sekarang - 5, $tahun_sekarang + 5);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Laporan Bulanan</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="logo">💰</div>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="input_transaksi.php">Input Transaksi</a></li>
                <li><a href="laporan_bulanan.php" class="active">Laporan Bulanan</a></li>
                <li><a href="visualisasi_data.php">Visualisasi Data</a></li>
                <li><a href="kelola_kategori.php">Kelola Kategori</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h2 class="card-title">📊 Laporan Keuangan Bulanan</h2>

            <?php if ($message): ?>
                <div class="error"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="select-container">
                    <div class="form-group">
                        <label>📆 Bulan</label>
                        <select name="bulan" required>
                            <option value="">Pilih Bulan</option>
                            <option value="januari" <?= isset($_POST['bulan']) && $_POST['bulan'] == 'januari' ? 'selected' : '' ?>>Januari</option>
                            <option value="februari" <?= isset($_POST['bulan']) && $_POST['bulan'] == 'februari' ? 'selected' : '' ?>>Februari</option>
                            <option value="maret" <?= isset($_POST['bulan']) && $_POST['bulan'] == 'maret' ? 'selected' : '' ?>>Maret</option>
                            <option value="april" <?= isset($_POST['bulan']) && $_POST['bulan'] == 'april' ? 'selected' : '' ?>>April</option>
                            <option value="mei" <?= isset($_POST['bulan']) && $_POST['bulan'] == 'mei' ? 'selected' : '' ?>>Mei</option>
                            <option value="juni" <?= isset($_POST['bulan']) && $_POST['bulan'] == 'juni' ? 'selected' : '' ?>>Juni</option>
                            <option value="juli" <?= isset($_POST['bulan']) && $_POST['bulan'] == 'juli' ? 'selected' : '' ?>>Juli</option>
                            <option value="agustus" <?= isset($_POST['bulan']) && $_POST['bulan'] == 'agustus' ? 'selected' : '' ?>>Agustus</option>
                            <option value="september" <?= isset($_POST['bulan']) && $_POST['bulan'] == 'september' ? 'selected' : '' ?>>September</option>
                            <option value="oktober" <?= isset($_POST['bulan']) && $_POST['bulan'] == 'oktober' ? 'selected' : '' ?>>Oktober</option>
                            <option value="november" <?= isset($_POST['bulan']) && $_POST['bulan'] == 'november' ? 'selected' : '' ?>>November</option>
                            <option value="desember" <?= isset($_POST['bulan']) && $_POST['bulan'] == 'desember' ? 'selected' : '' ?>>Desember</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>🗓️ Tahun</label>
                        <select name="tahun" required>
                            <option value="">Pilih Tahun</option>
                            <?php foreach ($tahun_list as $t): ?>
                                <option value="<?= $t ?>" <?= isset($_POST['tahun']) && $_POST['tahun'] == $t ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" name="generate"><span>🔍</span> Generate Laporan</button>
                    </div>
                </div>
            </form>

            <?php if (!empty($laporan)): ?>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="icon">💵</div>
                        <h3>Saldo Bulan Ini</h3>
                        <p>Rp <?= number_format($laporan['saldo'], 0, ',', '.') ?></p>
                    </div>
                    <div class="stat-box">
                        <div class="icon">📈</div>
                        <h3>Total Pemasukan</h3>
                        <p>Rp <?= number_format($laporan['pemasukan'], 0, ',', '.') ?></p>
                    </div>
                    <div class="stat-box">
                        <div class="icon">📉</div>
                        <h3>Total Pengeluaran</h3>
                        <p>Rp <?= number_format($laporan['pengeluaran'], 0, ',', '.') ?></p>
                    </div>
                    <div class="stat-box">
                        <div class="icon">🔢</div>
                        <h3>Jumlah Transaksi</h3>
                        <p><?= $laporan['jumlah_transaksi'] ?></p>
                    </div>
                </div>

                <div class="card">
                    <h3>🧾 Distribusi Pengeluaran Bulanan</h3>
                    <?php if (empty($laporan['pengeluaran_per_kategori'])): ?>
                        <p style="text-align:center; padding:20px; color:#888;">Tidak ada pengeluaran pada bulan ini.</p>
                    <?php else: ?>
                        <ul style="list-style: none; padding: 0;">
                            <?php foreach ($laporan['pengeluaran_per_kategori'] as $kategori => $jumlah): ?>
                                <li style="padding: 12px; background: #f8fafc; margin: 8px 0; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                    <span><?= htmlspecialchars($kategori) ?></span>
                                    <span style="font-weight: bold; color: #4a6fa5;">Rp <?= number_format($jumlah, 0, ',', '.') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        📊 Sistem Manajemen Keuangan - Ian Co | © <?= date('Y') ?>
    </footer>
</body>
</html>