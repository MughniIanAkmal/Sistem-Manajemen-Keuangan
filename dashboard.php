<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function bacaFile($file) {
    if (!file_exists($file)) return [];
    $data = file_get_contents($file);
    return json_decode($data, true) ?: [];
}

$transaksi = bacaFile('data/transaksi.txt');

$saldo = 0;
$total_pemasukan = 0;
$total_pengeluaran = 0;
$transaksi_bulan_ini = 0;

$bulan_ini = date('Y-m');
foreach ($transaksi as $t) {
    if ($t['jenis'] == 'pemasukan') {
        $total_pemasukan += $t['jumlah'];
        $saldo += $t['jumlah'];
    } else {
        $total_pengeluaran += $t['jumlah'];
        $saldo -= $t['jumlah'];
    }
    if (substr($t['tanggal'], 0, 7) == $bulan_ini) {
        $transaksi_bulan_ini++;
    }
}

$pengeluaran_per_kategori = [];
foreach ($transaksi as $t) {
    if ($t['jenis'] == 'pengeluaran' && substr($t['tanggal'], 0, 7) == $bulan_ini) {
        $kategori = $t['kategori'];
        if (!isset($pengeluaran_per_kategori[$kategori])) {
            $pengeluaran_per_kategori[$kategori] = 0;
        }
        $pengeluaran_per_kategori[$kategori] += $t['jumlah'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Manajemen Keuangan</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="logo">💰</div>
        <nav>
            <ul>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="input_transaksi.php">Input Transaksi</a></li>
                <li><a href="laporan_bulanan.php">Laporan Bulanan</a></li>
                <li><a href="visualisasi_data.php">Visualisasi Data</a></li>
                <li><a href="kelola_kategori.php">Kelola Kategori</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h2 class="card-title">🎯 Dashboard Keuangan</h2>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="icon">💵</div>
                    <h3>Saldo Saat Ini</h3>
                    <p>Rp <?= number_format($saldo, 0, ',', '.') ?></p>
                </div>
                <div class="stat-box">
                    <div class="icon">📈</div>
                    <h3>Total Pemasukan</h3>
                    <p>Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></p>
                </div>
                <div class="stat-box">
                    <div class="icon">📉</div>
                    <h3>Total Pengeluaran</h3>
                    <p>Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></p>
                </div>
                <div class="stat-box">
                    <div class="icon">📅</div>
                    <h3>Transaksi Bulan Ini</h3>
                    <p><?= $transaksi_bulan_ini ?> transaksi</p>
                </div>
            </div>

            <div class="card">
                <h3>💸 Pengeluaran Per Kategori (Bulan Ini)</h3>
                <?php if (empty($pengeluaran_per_kategori)): ?>
                    <p style="text-align:center; padding:20px; color:#888;">Belum ada pengeluaran bulan ini.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($pengeluaran_per_kategori as $kategori => $jumlah): ?>
                            <li style="padding: 12px; background: #f8fafc; margin: 8px 0; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <span><?= htmlspecialchars($kategori) ?></span>
                                <span style="font-weight: bold; color: #4a6fa5;">Rp <?= number_format($jumlah, 0, ',', '.') ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="footer">
        📊 Sistem Manajemen Keuangan - Ian Co | © <?= date('Y') ?>
    </footer>
</body>
</html>