<?php
// Minimal multipart POST using stream context. Writes a tiny JPEG from base64 and posts it with CSRF header and session cookie.
$base = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=';
$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'scan.jpg';
file_put_contents($tmp, base64_decode($base));

// Get create page to obtain cookies and CSRF
$opts = [
  'http' => [
    'method' => 'GET',
    'header' => "User-Agent: PHP-post-test\r\n",
    'ignore_errors' => true,
  ]
];
$context = stream_context_create($opts);
$create = file_get_contents('http://127.0.0.1:8000/documents/create', false, $context);
$csrf = null;
if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $create, $m)) {
    $csrf = $m[1];
}
$cookie = null;
foreach ($http_response_header as $h) {
    if (stripos($h, 'Set-Cookie:') === 0) { $cookie = trim(substr($h, 11)); break; }
}
if (!$csrf) { echo "No CSRF\n"; exit(1); }
// trim cookie attributes (keep only name=value)
if ($cookie && strpos($cookie, ';') !== false) {
  $cookie = substr($cookie, 0, strpos($cookie, ';'));
}

// Build multipart body
$boundary = '----PHPFormBoundary' . md5(time());
$eol = "\r\n";
$body = '';
$body .= '--'.$boundary.$eol;
$body .= 'Content-Disposition: form-data; name="title"'.$eol.$eol;
$body .= 'PHP multipart test'.$eol;
$body .= '--'.$boundary.$eol;
$body .= 'Content-Disposition: form-data; name="file"; filename="scan.jpg"'.$eol;
$body .= 'Content-Type: image/jpeg'.$eol.$eol;
$body .= file_get_contents($tmp).$eol;
$body .= '--'.$boundary.'--'.$eol;

$opts = [
  'http' => [
    'method' => 'POST',
    'header' => "Content-Type: multipart/form-data; boundary={$boundary}\r\n" .
                "X-CSRF-TOKEN: {$csrf}\r\n" .
                ($cookie ? "Cookie: {$cookie}\r\n" : '') .
                "Accept: application/json\r\n" .
                "User-Agent: PHP-post-test\r\n",
    'content' => $body,
    'ignore_errors' => true,
    'timeout' => 30,
  ]
];
$context = stream_context_create($opts);
$result = file_get_contents('http://127.0.0.1:8000/documents', false, $context);
echo "HTTP RESPONSE:\n";
foreach ($http_response_header as $h) echo $h."\n";
echo "\nBODY:\n".substr($result,0,400)."\n\n";

// Dump last DB rows
try {
    $db = __DIR__ . '/../database/database.sqlite';
    if (!file_exists($db)) { echo "NO_DB\n"; exit(0); }
    $pdo = new PDO('sqlite:'.$db);
    $rows = $pdo->query('SELECT id,title,filename,created_at FROM documents ORDER BY id DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
    echo "DB ROWS:\n".json_encode($rows)."\n";
} catch (Exception $e) { echo "DB ERR: ".$e->getMessage()."\n"; }
