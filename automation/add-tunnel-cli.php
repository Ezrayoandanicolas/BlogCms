<?php
/**
 * CLI: Add hostname to Cloudflare Tunnel + DNS CNAME
 * Usage: php add-tunnel-cli.php --hostname blog.example.com --service http://localhost:7999
 *
 * Reads CLOUDFLARE_EMAIL, CLOUDFLARE_API_KEY, CLOUDFLARE_TUNNEL_ID from .env
 */

// Parse CLI arguments
$longopts = [
    'hostname:',  // e.g. blog.example.com or example.com
    'service:',   // e.g. http://localhost:7999
];
$options = getopt('', $longopts);

if (empty($options['hostname']) || empty($options['service'])) {
    echo json_encode(['success' => false, 'message' => 'Usage: php add-tunnel-cli.php --hostname example.com --service http://localhost:7999']) . "\n";
    exit(1);
}

$hostname = $options['hostname'];
$serviceUrl = $options['service'];

// Load .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

$email = getenv('CLOUDFLARE_EMAIL') ?: '';
$apiKey = getenv('CLOUDFLARE_API_KEY') ?: '';
$tunnelId = getenv('CLOUDFLARE_TUNNEL_ID') ?: '';
$tunnelId = $tunnelId ?: '08bcb3d5-97b3-4897-8d60-92c3affc7e25'; // fallback default

if ($email === '' || $apiKey === '') {
    echo json_encode(['success' => false, 'message' => 'CLOUDFLARE_EMAIL or CLOUDFLARE_API_KEY not set in .env']) . "\n";
    exit(1);
}

// Parse hostname → subdomain + domain
$parts = explode('.', $hostname);
if (count($parts) < 2) {
    echo json_encode(['success' => false, 'message' => "Invalid hostname: $hostname"]) . "\n";
    exit(1);
}

$subdomain = count($parts) > 2 ? $parts[0] : '@';
$domain = count($parts) > 2 ? implode('.', array_slice($parts, 1)) : $hostname;

echo "🔧 Cloudflare Tunnel Setup\n";
echo "   Hostname : $hostname\n";
echo "   Subdomain: $subdomain\n";
echo "   Domain   : $domain\n";
echo "   Service  : $serviceUrl\n";
echo "   Tunnel ID: $tunnelId\n\n";

function cf_api($url, $email, $apiKey, $method = 'GET', $data = null)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Auth-Email: {$email}",
        "X-Auth-Key: {$apiKey}",
        'Content-Type: application/json'
    ]);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $error) {
        return ['success' => false, 'errors' => [['message' => $error ?: 'Curl failed']], 'http_code' => $httpCode];
    }
    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        return ['success' => false, 'errors' => [['message' => 'Invalid Cloudflare response']], 'http_code' => $httpCode];
    }
    $decoded['http_code'] = $httpCode;
    return $decoded;
}

function ensure_catch_all_rule(array $ingress)
{
    $catchAll = ['service' => 'http_status:404'];
    $normalized = [];
    foreach ($ingress as $rule) {
        if (!is_array($rule) || empty($rule['service'])) continue;
        $isCatchAll = empty($rule['hostname']) && empty($rule['path']) && $rule['service'] === 'http_status:404';
        if ($isCatchAll) continue;
        $normalized[] = $rule;
    }
    $normalized[] = $catchAll;
    return $normalized;
}

// Step 1: Get zone info
echo "📡 Mencari zone Cloudflare untuk {$domain}...\n";
$zoneRes = cf_api(
    'https://api.cloudflare.com/client/v4/zones?name=' . urlencode($domain),
    $email, $apiKey
);

if (empty($zoneRes['success']) || empty($zoneRes['result'][0]['id'])) {
    echo json_encode(['success' => false, 'message' => "Domain {$domain} tidak ditemukan di Cloudflare"]) . "\n";
    exit(1);
}

$zoneId = $zoneRes['result'][0]['id'];
$accountId = $zoneRes['result'][0]['account']['id'] ?? '';
echo "   ✅ Zone ID: {$zoneId}\n";
echo "   ✅ Account ID: {$accountId}\n";

// Step 2: Get tunnel config
echo "\n📡 Mengambil konfigurasi tunnel...\n";
$configRes = cf_api(
    "https://api.cloudflare.com/client/v4/accounts/{$accountId}/cfd_tunnel/{$tunnelId}/configurations",
    $email, $apiKey
);

if (empty($configRes['success'])) {
    $errMsg = $configRes['errors'][0]['message'] ?? 'Gagal ambil config tunnel';
    echo json_encode(['success' => false, 'message' => $errMsg]) . "\n";
    exit(1);
}

$config = $configRes['result'] ?? $configRes;
$ingress = $config['config']['ingress'] ?? [];

// Step 3: Add/update hostname in ingress
echo "   📝 Menambah hostname ke tunnel config...\n";
$fullHostname = $subdomain === '@' ? $domain : $subdomain . '.' . $domain;
$updated = false;
foreach ($ingress as $i => $rule) {
    if (($rule['hostname'] ?? null) === $fullHostname) {
        $ingress[$i]['service'] = $serviceUrl;
        $updated = true;
        echo "   🔄 Update existing rule: {$fullHostname}\n";
        break;
    }
}
if (!$updated) {
    $ingress[] = [
        'hostname' => $fullHostname,
        'service'  => $serviceUrl,
    ];
    echo "   ➕ Add new rule: {$fullHostname}\n";
}

$ingress = ensure_catch_all_rule($ingress);

// Step 4: PUT tunnel config
echo "   💾 Menyimpan konfigurasi tunnel...\n";
$putRes = cf_api(
    "https://api.cloudflare.com/client/v4/accounts/{$accountId}/cfd_tunnel/{$tunnelId}/configurations",
    $email, $apiKey, 'PUT',
    ['config' => ['ingress' => $ingress]]
);

if (empty($putRes['success'])) {
    $errMsg = $putRes['errors'][0]['message'] ?? 'Gagal update config tunnel';
    echo json_encode(['success' => false, 'message' => $errMsg]) . "\n";
    exit(1);
}
echo "   ✅ Tunnel config updated\n";

// Step 5: DNS CNAME record
$dnsTarget = $tunnelId . '.cfargotunnel.com';
echo "\n📡 Mengupdate DNS CNAME...\n";

$dnsLookup = cf_api(
    "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records?type=CNAME&name=" . urlencode($fullHostname),
    $email, $apiKey
);

$dnsPayload = [
    'type'    => 'CNAME',
    'name'    => $fullHostname,
    'content' => $dnsTarget,
    'ttl'     => 1,
    'proxied' => true,
];

$existingDns = $dnsLookup['result'][0] ?? null;
if ($existingDns) {
    $dnsRes = cf_api(
        "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records/{$existingDns['id']}",
        $email, $apiKey, 'PUT', $dnsPayload
    );
    $dnsAction = 'updated';
} else {
    $dnsRes = cf_api(
        "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records",
        $email, $apiKey, 'POST', $dnsPayload
    );
    $dnsAction = 'created';
}

if (empty($dnsRes['success'])) {
    $errMsg = $dnsRes['errors'][0]['message'] ?? 'Gagal update DNS';
    echo json_encode(['success' => false, 'message' => $errMsg]) . "\n";
    exit(1);
}

echo "   ✅ DNS CNAME {$dnsAction}: {$fullHostname} → {$dnsTarget}\n";
echo "\n✅ Cloudflare tunnel setup complete!\n";
echo json_encode([
    'success' => true,
    'message' => "Tunnel configured: {$fullHostname} → {$serviceUrl}",
    'data' => [
        'hostname' => $fullHostname,
        'service'  => $serviceUrl,
        'dns'      => $dnsTarget,
    ]
]) . "\n";
