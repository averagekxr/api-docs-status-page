<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'apistat');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Status Check Functions
function check_api_status($endpoint)
{
    $start = microtime(true);
    try {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_NOBODY => true
        ]);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $status = ($httpCode >= 200 && $httpCode < 300) ? 'operational' : 'outage';
        $responseTime = round((microtime(true) - $start) * 1000, 2);
        return ['status' => $status, 'response_time' => $responseTime];
    } catch (Exception $e) {
        return ['status' => 'outage', 'response_time' => 0];
    }
}

function check_mysql_status()
{
    $start = microtime(true);
    try {
        global $pdo;
        $pdo->query('SELECT 1');
        return ['status' => 'operational', 'response_time' => round((microtime(true) - $start) * 1000, 2)];
    } catch (PDOException $e) {
        return ['status' => 'outage', 'response_time' => 0];
    }
}

$services = $pdo->query("SELECT * FROM services")->fetchAll();
$currentStatus = [];
foreach ($services as $service) {
    switch ($service['type']) {
        case 'api':
            $result = check_api_status($service['endpoint']);
            break;
        case 'database':
            $result = check_mysql_status();
            break;
        case 'auth':
            $result = ['status' => 'operational', 'response_time' => 0];
            break;
        default:
            $result = ['status' => 'outage', 'response_time' => 0];
    }

    $stmt = $pdo->prepare("INSERT INTO status_checks (service_id, status, response_time) VALUES (?, ?, ?)");
    $stmt->execute([$service['id'], $result['status'], $result['response_time']]);

    $currentStatus[] = [
        'service' => $service['name'],
        'type' => $service['type'],
        'status' => $result['status'],
        'response_time' => $result['response_time'],
        'last_checked' => date('c')
    ];
}

$incidents = $pdo->query("
    SELECT i.*, s.name as service_name 
    FROM incidents i
    JOIN services s ON i.service_id = s.id
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();

$uptimeStats = $pdo->query("
    SELECT 
        s.name,
        COUNT(*) AS total_checks,
        SUM(CASE WHEN sc.status = 'operational' THEN 1 ELSE 0 END) AS operational_checks,
        ROUND((SUM(CASE WHEN sc.status = 'operational' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) AS uptime_percentage
    FROM status_checks sc
    JOIN services s ON sc.service_id = s.id
    WHERE sc.checked_at >= NOW() - INTERVAL 7 DAY
    GROUP BY s.name
")->fetchAll();

file_put_contents('status_log.json', json_encode([
    'last_updated' => date('c'),
    'services' => $currentStatus,
    'uptime_stats' => $uptimeStats,
    'recent_incidents' => $incidents
], JSON_PRETTY_PRINT));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validonix Status | Organized Service Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* width */
        ::-webkit-scrollbar {
            width: 10px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            box-shadow: inset 0 0 5px grey;
            border-radius: 2px;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: grey;
            border-radius: 5px;
        }


        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--background);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: opacity 0.5s ease;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--primary);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-text {
            margin-top: 20px;
            color: var(--text);
            font-size: 1.2rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        :root {
            --primary: #5865f2;
            --secondary: #9146ff;
            --success: #2ecc71;
            --background: #121212;
            --text: #e0e0e0;
            --border: #3a3a3a;
            --card-bg: #1e1e1e;
        }

        body {
            background: var(--background);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 2rem;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .section-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .grid-layout {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .status-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 24px rgba(88, 101, 242, 0.08);
            transition: transform 0.2s ease;
            border: 1px solid var(--border);
        }

        .status-card:hover {
            transform: translateY(-3px);
        }

        .metric-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .metric-item {
            text-align: center;
            padding: 1.5rem;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(88, 101, 242, 0.05);
            border: 1px solid var(--border);
        }

        .metric-value {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin: 0.5rem 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .data-table th,
        .data-table td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .data-table th {
            border-bottom: 2px solid var(--border);
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background: rgba(11, 20, 120, 0.5);
            color: var(--primary);
            font-size: 0.9em;
        }

        .status-indicator i {
            font-size: 0.8em;
        }

        .chart-placeholder {
            height: 200px;
            background: #3a3a3a;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            margin: 1rem 0;
        }
    </style>
</head>

<body>
    <div class="loading-screen">
        <div class="loading-spinner"></div>
        <div class="loading-text">Validonix API is Loading...</div>
    </div>

    <body>
        <div class="dashboard-container">
            <header class="section-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1 style="margin: 0; font-weight: 700;">Service Health Dashboard</h1>
                        <p style="margin: 0.5rem 0 0; opacity: 0.9;">Real-time system status monitoring</p>
                    </div>
                    <div class="status-indicator">
                        <i class="fas fa-check-circle"></i>
                        <span>All Systems Operational</span>
                    </div>
                </div>
            </header>

            <div class="metric-group">
                <div class="metric-item">
                    <?php if (isset($uptimeStats[0])): ?>
                        <div class="metric-value"><?= $uptimeStats[0]['uptime_percentage'] ?>%</div>
                    <?php else: ?>
                        <div class="metric-value">--%</div>
                    <?php endif; ?>
                    <div class="subtext">7 Günlük Uptime</div>
                </div>
                <div class="metric-item">
                    <?php
                    $totalResponse = 0;
                    $count = count($currentStatus);
                    foreach ($currentStatus as $status) {
                        $totalResponse += $status['response_time'];
                    }
                    $avgResponse = $count > 0 ? round($totalResponse / $count, 2) : 0;
                    ?>
                    <div class="metric-value"><?= $avgResponse ?>ms</div>
                    <div class="subtext">Ortalama Yanıt Süresi</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value">2</div>
                    <div class="subtext">Günlük İstek</div>
                </div>
            </div>

            <div class="grid-layout">
                <section class="status-card">
                    <h2><i class="fas fa-history"></i> Incident History</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Servis</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($incidents)): ?>
                                <?php foreach ($incidents as $incident): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($incident['created_at'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($incident['service_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="status-indicator">
                                                <?php if (strtolower($incident['status']) === 'operational'): ?>
                                                    <i class="fas fa-check"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($incident['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">Herhangi bir incident bulunamadı</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>

                <section class="status-card">
                    <h2><i class="fas fa-heartbeat"></i> Component Health</h2>
                    <table class="data-table">
                        <tbody>
                            <?php if (!empty($currentStatus)): ?>
                                <?php foreach ($currentStatus as $status): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($status['service']) ?></td>
                                        <td>
                                            <span class="status-indicator">
                                                <?php if ($status['status'] === 'operational'): ?>
                                                    <i class="fas fa-check"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                <?php endif; ?>
                                                <?= ucfirst($status['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2">Servis durumu bulunamadı</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </div>

            <section class="status-card">
                <h2><i class="fas fa-chart-line"></i> Performance Overview</h2>
                <?php if (!empty($uptimeStats)): ?>
                    <table class="data-table" style="margin-bottom: 1rem;">
                        <thead>
                            <tr>
                                <th>SERVİS</th>
                                <th>UPTIME (%)</th>
                                <th>OPERATIONAL CHECKS</th>
                                <th>TOTAL CHECKS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($uptimeStats as $stat): ?>
                                <tr>
                                    <td><?= htmlspecialchars($stat['name']) ?></td>
                                    <td><?= htmlspecialchars($stat['uptime_percentage']) ?>%</td>
                                    <td><?= htmlspecialchars($stat['operational_checks']) ?></td>
                                    <td><?= htmlspecialchars($stat['total_checks']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Uptime verisi mevcut değil.</p>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                    <div class="status-indicator">
                        <i class="fas fa-info-circle"></i> HER 5 DAKİKADA GÜNCELLENİYOR
                    </div>
                    <a href="?download=1"
                        style="background: var(--primary); color: white; text-decoration: none; border: none; padding: 0.5rem 1rem; border-radius: 8px; display: inline-flex; align-items: center;">
                        <i class="fas fa-download" style="margin-right: 0.5rem;"></i> VERİLERİ İNDİR
                    </a>
                </div>
            </section>

            <script>
                window.addEventListener('load', function () {
                    setTimeout(function () {
                        document.querySelector('.loading-screen').style.opacity = '0';
                        setTimeout(function () {
                            document.querySelector('.loading-screen').style.display = 'none';
                        }, 500);
                    }, 2000);
                });
            </script>

        </div>
        <div class="docs-card" style="text-align: center; color: #666;">
            <p>© 2025 Validonix API. All rights reserved.<br>
                <small>Discord is a trademark of Discord Inc. Not affiliated with Discord.</small>
            </p>
        </div>
    </body>

</html>