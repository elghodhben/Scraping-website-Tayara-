<?php
require './vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Function to fetch data for a specific page
function fetchDataForPage($page) {
    $httpClient = new Client();
    $baseUrl = 'https://www.tayara.tn/search/?page=';

    try {
        $response = $httpClient->get($baseUrl . $page);
        return (string) $response->getBody();
    } catch (RequestException $e) {
        echo 'Error fetching URL: ' . $e->getMessage();
        return false;
    }
}

// Set initial file name
$fileName = 'tayara.csv';

// Check if the file already exists, if so, increment the file name
$i = 1;
while (file_exists($fileName)) {
    $fileName = 'tayara_' . $i . '.csv';
    $i++;
}

// Create the CSV file
$csvFile = fopen($fileName, 'w');

// Header of file CSV
fputcsv($csvFile, array('Category', 'Description', 'Price', 'Location', 'Image URL'));

// Starting page
$page = 1;

do {
    // Fetch data for the current page
    $htmlString = fetchDataForPage($page);

    // Check if HTML content is successfully retrieved
    if (!$htmlString) {
        break; // Stop if failed to retrieve HTML content
    }

    libxml_use_internal_errors(true);

    $doc = new DOMDocument();
    $doc->loadHTML($htmlString);

    $xpath = new DOMXPath($doc);

    $articles = $xpath->query('//article[@class="mx-0"]');

    if ($articles->length === 0) {
        // No more articles found, stop looping
        break;
    }

    foreach ($articles as $article) {
        
        $title = $description = $price = $imageUrl = $category = $location = null;

        $titleNode = $xpath->query('.//h2', $article)->item(0);
        $priceNode = $xpath->query('.//data', $article)->item(0);
        $imageNode = $xpath->query('.//img/@src', $article)->item(0);
        $categoryNode = $xpath->query('.//span[contains(@class, "text-neutral-500")]', $article)->item(0);
        $locationNode = $xpath->query('.//div[contains(@class, "text-gray-800")]', $article)->item(1);

        if ($titleNode) {
            $title = $titleNode->textContent;
        }
        if ($priceNode) {
            $price = $priceNode->getAttribute('value');
        }
        if ($imageNode) {
            $imageUrl = $imageNode->nodeValue;
        }
        if ($categoryNode) {
            $category = $categoryNode->textContent;
        }
        if ($locationNode) {
            $location = $locationNode->textContent;
        }

        fputcsv($csvFile, array($category, $title,  $price.' TND' , $location, $imageUrl));
    }

    // Move to the next page
    $page++;

} while (true); // Continue looping indefinitely

// Close the file
fclose($csvFile);

echo 'The information has been retrieved and saved in ' . $fileName . '!';
?>
