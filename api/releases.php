<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * Obscuras Mailer Campaigner - Releases API
 * ═══════════════════════════════════════════════════════════════════════════════
 * 
 * Endpoints:
 *   GET /api/releases.php              - Get all releases info
 *   GET /api/releases.php?latest=1     - Get only latest release
 *   GET /api/releases.php?v=1.3.1      - Get specific version info
 *   GET /api/releases.php?stats=1      - Get download statistics (if available)
 * 
 * ═══════════════════════════════════════════════════════════════════════════════
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Load releases data
$releasesFile = __DIR__ . '/downloads/releases.json';
if (!file_exists($releasesFile)) {
    http_response_code(500);
    echo json_encode(['error' => 'Release data not found']);
    exit;
}

$releases = json_decode(file_get_contents($releasesFile), true);
if (!$releases) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid release data']);
    exit;
}

// Get download stats if requested
if (isset($_GET['stats'])) {
    $statsFile = __DIR__ . '/downloads/stats.json';
    $stats = file_exists($statsFile) ? json_decode(file_get_contents($statsFile), true) : ['total' => 0];
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ], JSON_PRETTY_PRINT);
    exit;
}

// Get specific version
if (isset($_GET['v'])) {
    $requestedVersion = $_GET['v'];
    foreach ($releases['releases'] as $release) {
        if ($release['version'] === $requestedVersion) {
            echo json_encode([
                'success' => true,
                'release' => $release,
                'download_url' => '/api/download.php?v=' . $release['version'],
                'checksum_url' => '/api/download.php?v=' . $release['version'] . '&checksum=1'
            ], JSON_PRETTY_PRINT);
            exit;
        }
    }
    
    http_response_code(404);
    echo json_encode([
        'error' => 'Version not found',
        'available' => array_column($releases['releases'], 'version')
    ]);
    exit;
}

// Get only latest release
if (isset($_GET['latest'])) {
    foreach ($releases['releases'] as $release) {
        if ($release['version'] === $releases['latest']) {
            echo json_encode([
                'success' => true,
                'release' => $release,
                'download_url' => '/api/download.php',
                'checksum_url' => '/api/download.php?checksum=1'
            ], JSON_PRETTY_PRINT);
            exit;
        }
    }
}

// Return all releases info
$response = [
    'success' => true,
    'latest' => $releases['latest'],
    'releases' => array_map(function($release) {
        return [
            'version' => $release['version'],
            'date' => $release['date'],
            'size' => $release['size'],
            'sha256' => $release['sha256'],
            'min_python' => $release['min_python'],
            'platforms' => $release['platforms'],
            'changelog' => $release['changelog'],
            'download_url' => '/api/download.php?v=' . $release['version'],
            'checksum_url' => '/api/download.php?v=' . $release['version'] . '&checksum=1'
        ];
    }, $releases['releases'])
];

echo json_encode($response, JSON_PRETTY_PRINT);
