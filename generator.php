<?php
function feedback404() {
    header("HTTP/1.0 404 Not Found");
    echo "404 Not Found";
}

function getFileRowCount($filename) {
    $file = fopen($filename, "r");
    $rowCount = 0;

    while (!feof($file)) {
        fgets($file);
        $rowCount++;
    }

    fclose($file);

    return $rowCount;
}

// Mengambil URL host dan path
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$dir = rtrim(dirname($_SERVER['PHP_SELF']), '/');
$urlPath = "$protocol://$host$dir/";

// Buka file list.txt untuk diproses
$filename = "list.txt";
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$titles = file('title.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$descriptions = file('desc.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Mengacak data
shuffle($titles);
shuffle($descriptions);

// Menentukan jumlah iterasi berdasarkan data yang lebih sedikit
$count = min(count($titles), count($descriptions));

// Membuat sitemap.xml
$sitemapFile = fopen("sitemap.xml", "w");
fwrite($sitemapFile, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
fwrite($sitemapFile, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL);

foreach ($lines as $target_string) {
    $target_string = strtolower($target_string);
    $BRAND = strtoupper($target_string);

    // Membuat folder dan file index.html
    $folderPath = __DIR__ . "/$BRAND";
    $filePath = $folderPath . "/index.html";

    if (!is_dir($folderPath)) {
        if (!mkdir($folderPath, 0777, true)) {
            error_log("Failed to create directory: $folderPath");
            continue;
        }
    }

    ob_start(); 
    include 'template.php';
    $html_content = ob_get_clean(); 

    if (file_put_contents($filePath, $html_content) === false) {
        error_log("Failed to write file: $filePath");
    }

    // Membuat URL untuk sitemap
    $htmlURL = $urlPath . $BRAND;

    fwrite($sitemapFile, '  <url>' . PHP_EOL);
    fwrite($sitemapFile, '    <loc>' . $htmlURL . '</loc>' . PHP_EOL);

    date_default_timezone_set('Asia/Jakarta');
    $currentTime = date('Y-m-d\\TH:i:sP');
    fwrite($sitemapFile, '    <lastmod>' . $currentTime . '</lastmod>' . PHP_EOL);
    fwrite($sitemapFile, '    <changefreq>daily</changefreq>' . PHP_EOL);
    fwrite($sitemapFile, '  </url>' . PHP_EOL);
}

fwrite($sitemapFile, '</urlset>' . PHP_EOL);
fclose($sitemapFile);

echo "[INFO] ==> FILES SUCCES CREATED<br>\n";
echo "[INFO] ==> INDEX SUCCES CREATED<br>\n";
echo "[INFO] ==> SITEMAP SUCCES CREATED<br>\n";
?>