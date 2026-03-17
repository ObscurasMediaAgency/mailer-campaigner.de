<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * Obscuras Mailer Campaigner - Download Handler
 * ═══════════════════════════════════════════════════════════════════════════════
 * 
 * Endpoints:
 *   GET /api/download.php              - Download latest version
 *   GET /api/download.php?v=1.3.1      - Download specific version
 *   GET /api/download.php?checksum=1   - Get SHA256 checksum file
 * 
 * ═══════════════════════════════════════════════════════════════════════════════
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Load releases data
$releasesFile = __DIR__ . '/downloads/releases.json';
if (!file_exists($releasesFile)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Release data not found']);
    exit;
}

$releases = json_decode(file_get_contents($releasesFile), true);
if (!$releases) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid release data']);
    exit;
}

// Get requested version (default: latest)
$requestedVersion = $_GET['v'] ?? $releases['latest'];
$checksumOnly = isset($_GET['checksum']);

// Find release
$release = null;
foreach ($releases['releases'] as $r) {
    if ($r['version'] === $requestedVersion) {
        $release = $r;
        break;
    }
}

if (!$release) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Version not found', 'available' => array_column($releases['releases'], 'version')]);
    exit;
}

$filePath = __DIR__ . '/downloads/' . $release['filename'];
$checksumPath = $filePath . '.sha256';

// Check if file exists
if (!file_exists($filePath)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Download file not found']);
    exit;
}

// Return checksum only
if ($checksumOnly) {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: inline; filename="' . $release['filename'] . '.sha256"');
    echo $release['sha256'] . '  ' . $release['filename'];
    exit;
}

// Log download (simple file-based counter)
$statsFile = __DIR__ . '/downloads/stats.json';
$stats = file_exists($statsFile) ? json_decode(file_get_contents($statsFile), true) : [];
$stats[$release['version']] = ($stats[$release['version']] ?? 0) + 1;
$stats['total'] = ($stats['total'] ?? 0) + 1;
$stats['last_download'] = date('Y-m-d H:i:s');
file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT));

// Serve file
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $release['filename'] . '"');
header('Content-Length: ' . filesize($filePath));
header('Content-Transfer-Encoding: binary');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');
header('X-Content-SHA256: ' . $release['sha256']);
header('X-Version: ' . $release['version']);

// Clear output buffer
if (ob_get_level()) {
    ob_end_clean();
}

// Stream file
readfile($filePath);
exit;
