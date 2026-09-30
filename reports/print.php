<?php
// public_html/api/reports/print.php
require __DIR__ . '/../lib/auth.php';
require __DIR__ . '/../db.php';

$user = require_auth();

$serviceName = $_GET['service'] ?? '';
$from        = $_GET['from'] ?? '';
$to          = $_GET['to'] ?? '';

$SERVICE_MAP = require __DIR__ . '/../lib/serviceMap.php';

if (!isset($SERVICE_MAP[$serviceName])) {
    http_response_code(400);
    echo 'Unknown service';
    exit;
}

$table = $SERVICE_MAP[$serviceName]['table'];

// Build optional WHERE clause
$conditions = [];
$params     = [];

if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
    $conditions[] = 'date >= ?';
    $params[]     = $from;
}
if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    $conditions[] = 'date <= ?';
    $params[]     = $to;
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$stmt = $pdo->prepare("SELECT * FROM `$table` $where ORDER BY date ASC");
$stmt->execute($params);
$rows = $stmt->fetchAll();
$columns = !empty($rows) ? array_keys($rows[0]) : [];

// Compute averages and totals
$numericColumns = [];
$averages = [];
$totals   = [];

if (!empty($rows)) {
    foreach ($columns as $col) {
        if (in_array($col, ['id', 'date', 'submitted_by', 'submitted_at', 'updated_by', 'updated_at'], true)) {
            continue;
        }
        if (isset($rows[0][$col]) && is_numeric($rows[0][$col])) {
            $numericColumns[] = $col;
        }
    }

    foreach ($numericColumns as $col) {
        $sum = 0;
        $count = 0;
        foreach ($rows as $row) {
            if (isset($row[$col]) && is_numeric($row[$col])) {
                $sum += (float)$row[$col];
                $count++;
            }
        }
        $averages[$col] = $count > 0 ? $sum / $count : 0;
        $totals[$col]   = $sum;
    }
}

// Period label
if ($from !== '' && $to !== '') {
    $periodLabel = $from . ' to ' . $to;
} elseif ($from !== '') {
    $periodLabel = 'From ' . $from;
} elseif ($to !== '') {
    $periodLabel = 'Up to ' . $to;
} else {
    $periodLabel = 'All time';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($serviceName) ?> — Report</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 24px 24px 100px;   /* extra bottom padding for the sticky button */
            color: #29231f;
            max-width: 1100px;
            margin: 0 auto;
        }

        /* ─── Report header ─────────────────────── */
        .report-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 2px solid #6b3f24;
            margin-bottom: 20px;
        }
        .report-header img {
            width: 64px;
            height: 64px;
            object-fit: contain;
            border-radius: 8px;
        }
        .report-header .church-info {
            flex: 1;
        }
        .report-header .church-name {
            font-size: 20px;
            font-weight: 700;
            color: #6b3f24;
            margin: 0;
            line-height: 1.2;
        }
        .report-header .report-title {
            font-size: 13px;
            color: #666;
            margin: 4px 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ─── Meta block ────────────────────────── */
        .meta {
            font-size: 13px;
            color: #444;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .meta strong { color: #29231f; }

        /* ─── Table ─────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e5e5;
            text-align: left;
        }
        th {
            background: #f7f4ef;
            font-weight: 600;
            color: #6b3f24;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.4px;
        }
        tr:nth-child(even) td { background: #fafaf7; }

        /* ─── Summary ───────────────────────────── */
        .summary {
            margin-top: 24px;
            padding: 16px;
            background: #f7f4ef;
            border-radius: 8px;
            font-size: 13px;
        }
        .summary-title {
            color: #6b3f24;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.4px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
        }
        .summary-item {
            padding: 8px 12px;
            background: #fff;
            border: 1px solid #e8e0d6;
            border-radius: 6px;
        }
        .summary-item .label {
            color: #666;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .summary-item .values {
            margin-top: 4px;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }
        .summary-item .avg {
            font-weight: 600;
            font-size: 14px;
        }
        .summary-item .total {
            color: #6b3f24;
            font-size: 12px;
            font-weight: 500;
        }

        /* ─── Footer ────────────────────────────── */
        .footer {
            margin-top: 40px;
            padding-top: 16px;
            border-top: 1px solid #e5e5e5;
            font-size: 11px;
            color: #999;
            text-align: center;
        }

        /* ─── Sticky Print Button ─────────────── */
        .print-bar {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
        }
        .print-bar button {
            padding: 12px 32px;
            background: #6b3f24;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(107, 63, 36, 0.35);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.15s;
        }
        .print-bar button:hover {
            background: #54301b;
            transform: translateY(-1px);
            box-shadow: 0 12px 34px rgba(107, 63, 36, 0.45);
        }
        .print-bar button:active {
            transform: translateY(0);
        }

        /* ─── Print styles ─────────────────────── */
        @media print {
            .print-bar { display: none; }
            body { padding: 0; }
            th { background: #eee !important; }
            tr:nth-child(even) td { background: transparent; }
            .summary {
                background: #f7f4ef !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .summary-item {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .report-header img {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- ─── Report header with logo ─────────── -->
    <div class="report-header">
        <img src="/api/reports/foursquare.jpg" alt="Foursquare Gospel Church">
        <div class="church-info">
            <h1 class="church-name">Foursquare Gospel Church, VGC</h1>
            <p class="report-title">Attendance Report</p>
        </div>
    </div>

    <!-- ─── Meta block ──────────────────────── -->
    <div class="meta">
        <strong>Service:</strong> <?= htmlspecialchars($serviceName) ?><br>
        <strong>Period:</strong> <?= htmlspecialchars($periodLabel) ?><br>
        <strong>Generated:</strong> <?= date('F j, Y \a\t g:i A') ?>
        &nbsp;·&nbsp;
        <strong>By:</strong> <?= htmlspecialchars($user['code'] ?? 'unknown') ?>
        &nbsp;·&nbsp;
        <strong>Records:</strong> <?= count($rows) ?>
    </div>

    <!-- ─── Data table ──────────────────────── -->
    <?php if (empty($rows)): ?>
        <p>No records found in this range.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <td><?= htmlspecialchars((string)($row[$col] ?? '')) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ─── Summary with averages + totals ── -->
        <?php if (!empty($averages)): ?>
            <div class="summary">
                <div class="summary-title">Summary</div>
                <div class="summary-grid">
                    <?php foreach ($averages as $col => $avg): ?>
                        <div class="summary-item">
                            <div class="label"><?= htmlspecialchars($col) ?></div>
                            <div class="values">
                                <span class="avg"><?= number_format($avg, 1) ?> avg</span>
                                <span class="total"><?= number_format($totals[$col], 0) ?> total</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ─── Footer ───────────────────────────── -->
    <div class="footer">
        Foursquare Gospel Church, VGC — Attendance Portal<br>
        Generated on <?= date('F j, Y') ?>
    </div>

    <!-- ─── Sticky Print Button ─────────────── -->
    <div class="print-bar">
        <button onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

</body>
</html>