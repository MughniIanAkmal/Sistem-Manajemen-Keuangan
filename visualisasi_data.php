<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';
$data_chart = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_chart'])) {
    $tahun = $_POST['tahun'] ?? '2025';

    $transaksi = file_exists('data/transaksi.txt') ? json_decode(file_get_contents('data/transaksi.txt'), true) : [];

    $bulan = [
        'januari' => 0, 'februari' => 0, 'maret' => 0,
        'april' => 0, 'mei' => 0, 'juni' => 0,
        'juli' => 0, 'agustus' => 0, 'september' => 0,
        'oktober' => 0, 'november' => 0, 'desember' => 0
    ];

    foreach ($transaksi as $t) {
        if (substr($t['tanggal'], 0, 4) == $tahun && $t['jenis'] == 'pengeluaran') {
            $bulan_tahun = substr($t['tanggal'], 5, 2);
            $nama_bulan = array_search($bulan_tahun, [
                '01' => 'januari', '02' => 'februari', '03' => 'maret',
                '04' => 'april', '05' => 'mei', '06' => 'juni',
                '07' => 'juli', '08' => 'agustus', '09' => 'september',
                '10' => 'oktober', '11' => 'november', '12' => 'desember'
            ]);
            if ($nama_bulan) {
                $bulan[$nama_bulan] += $t['jumlah'];
            }
        }
    }

    $data_chart = array_values($bulan);
}

$tahun_sekarang = date('Y');
$tahun_list = range($tahun_sekarang - 5, $tahun_sekarang + 5);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📈 Visualisasi Data Keuangan</title>
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
                <li><a href="visualisasi_data.php" class="active">Visualisasi Data</a></li>
                <li><a href="kelola_kategori.php">Kelola Kategori</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h2 class="card-title">📈 Visualisasi Data Keuangan</h2>

            <form method="POST">
                <div class="form-group">
                    <label>🗓️ Tahun</label>
                    <select name="tahun" required>
                        <option value="">Pilih Tahun</option>
                        <?php foreach ($tahun_list as $t): ?>
                            <option value="<?= $t ?>" <?= isset($_POST['tahun']) && $_POST['tahun'] == $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="generate_chart"><span>📊</span> Generate Laporan</button>
            </form>

            <?php if (!empty($data_chart)): ?>
                <div class="chart-container">
                    <div class="chart-placeholder">
                        <h3>💸 Pengeluaran Per Bulan (Tahun <?= htmlspecialchars($tahun) ?>)</h3>
                        <svg class="chart-svg" viewBox="0 0 1200 300" preserveAspectRatio="none">
                            <!-- Background Grid -->
                            <g stroke="#ddd" stroke-width="1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <line x1="0" y1="<?= 300 - $i * 50 ?>" x2="1200" y2="<?= 300 - $i * 50 ?>" />
                                <?php endfor; ?>
                            </g>

                            <!-- Line Chart -->
                            <polyline 
                                fill="none" 
                                stroke="#4a6fa5" 
                                stroke-width="4" 
                                stroke-linejoin="round"
                                stroke-linecap="round"
                                points="
                                    <?php 
                                    $max = max($data_chart);
                                    $step = 1200 / 12;
                                    for ($i = 0; $i < 12; $i++) {
                                        $x = $i * $step + $step/2;
                                        $y = 250 - ($data_chart[$i] / ($max ?: 1)) * 200;
                                        echo "$x,$y ";
                                    }
                                    ?>
                                "
                                style="animation: drawLine 2s ease-out;"
                            />

                            <!-- Dots -->
                            <?php 
                            for ($i = 0; $i < 12; $i++) {
                                $x = $i * $step + $step/2;
                                $y = 250 - ($data_chart[$i] / ($max ?: 1)) * 200;
                                echo "<circle cx='$x' cy='$y' r='5' fill='#4a6fa5' style='animation: popIn 0.5s ease-out ".($i*0.1)."s;' />";
                            }
                            ?>
                        </svg>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
    @keyframes drawLine {
        from { stroke-dasharray: 0, 1000; }
        to { stroke-dasharray: 1000, 0; }
    }

    @keyframes popIn {
        from { transform: scale(0); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    </style>

    <footer class="footer">
        📊 Sistem Manajemen Keuangan - Ian Co | © <?= date('Y') ?>
    </footer>
</body>
</html>