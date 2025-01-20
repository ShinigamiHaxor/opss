<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scrape Title dan Description dari Google Search</title>
</head>
<body>
    <h2>Masukkan Keyword untuk mencari di Google</h2>
    <form method="POST" action="">
        <label for="keyword">Masukkan Keyword:</label>
        <input type="text" id="keyword" name="keyword" required>
        <button type="submit">Submit</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $keyword = urlencode(trim($_POST['keyword'])) . '+"slot"';
        $apiKey = 'AIzaSyCKRl5l-I3C1vU6w-MdP_ebI5zfYy_YMMY';
        $searchEngineId = 'd1153f15138534b3f';
        $resultsPerPage = 5; 
        $totalPages = 5; 
        $titleFile = 'title.txt';
        $descFile = 'desc.txt';

        function get_web_content($url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout untuk permintaan
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        }

        function get_title_and_description($url) {
            $html = get_web_content($url);
            if (!$html) {
                return ['title' => null, 'description' => null];
            }
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $titleTag = $dom->getElementsByTagName('title');
            $title = $titleTag->length > 0 ? $titleTag->item(0)->nodeValue : null;
            $metaTags = $dom->getElementsByTagName('meta');
            $description = null;
            foreach ($metaTags as $meta) {
                if ($meta->getAttribute('name') === 'description') {
                    $description = $meta->getAttribute('content');
                    break;
                }
            }
            return ['title' => $title, 'description' => $description];
        }

        function save_to_file($filename, $data) {
            if (!empty($data)) {
                file_put_contents($filename, $data . PHP_EOL, FILE_APPEND);
            }
        }

        for ($page = 1; $page <= $totalPages; $page++) {
            $googleApiUrl = "https://www.googleapis.com/customsearch/v1?key=$apiKey&cx=$searchEngineId&q=$keyword&num=$resultsPerPage&start=" . (($page - 1) * $resultsPerPage + 1);
            $searchResults = get_web_content($googleApiUrl);
            $results = json_decode($searchResults, true);
            
            if (isset($results['items'])) {
                foreach ($results['items'] as $item) {
                    $resultUrl = $item['link'];
                    $data = get_title_and_description($resultUrl);
                    
                    if (is_null($data['title']) || is_null($data['description'])) {
                        $ampUrl = $resultUrl . '/amp';
                        $data = get_title_and_description($ampUrl);
                    }

                    save_to_file($titleFile, $data['title']);
                    save_to_file($descFile, $data['description']);
                }
            }
        }
        echo "<p>SUKSES!! Scraping telah disimpan ke dalam file.</p>";
    }
    ?>
</body>
</html>