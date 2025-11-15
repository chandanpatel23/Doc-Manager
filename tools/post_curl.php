<?php
// Use curl to GET create page (store cookies), extract CSRF meta, then POST multipart/form-data with same cookie jar and X-CSRF-TOKEN header.
$cookieJar = sys_get_temp_dir() . '/laravel_test_cookies.txt';
if (file_exists($cookieJar)) unlink($cookieJar);

$ch = curl_init('http://127.0.0.1:8000/documents/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$create = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
if ($err) { echo "GET_ERR: $err\n"; exit(1); }

if (!preg_match('/<meta name="csrf-token" content="([^"]+)"/', $create, $m)) {
    echo "No CSRF meta found\n"; exit(1);
}
$csrf = $m[1];

// prepare file
$base = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=';
$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'scan.jpg';
file_put_contents($tmp, base64_decode($base));

// POST multipart with curl
$ch = curl_init('http://127.0.0.1:8000/documents');
$post = [
    'title' => 'Curl multipart with CSRF',
    'file' => new CURLFile($tmp, 'image/jpeg', 'scan.jpg')
];
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-CSRF-TOKEN: $csrf", 'Accept: application/json']);
$res = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

echo "HTTP $http\n";
if ($err) echo "ERR: $err\n";
echo "BODY:\n".substr($res,0,1000)."\n\n";

// Dump DB rows
try {
    $db = __DIR__ . '/../database/database.sqlite';
    if (!file_exists($db)) { echo "NO_DB\n"; exit(0); }
    $pdo = new PDO('sqlite:'.$db);
    $rows = $pdo->query('SELECT id,title,filename,created_at FROM documents ORDER BY id DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
    echo "DB ROWS:\n".json_encode($rows)."\n";
} catch (Exception $e) { echo "DB ERR: ".$e->getMessage()."\n"; }
