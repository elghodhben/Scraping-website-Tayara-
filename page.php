<?php
require './vendor/autoload.php';

$httpClient = new \GuzzleHttp\Client();
$baseUrl = 'https://pharma-shop.tn/841-hydratants-toutes-peaux?fbclid=IwAR1YsPvKs5krEeV64TRmMafp-b_1cqPOPqEXBzZl1ARt8u0cDQkuLy7FL8Q&page=';

// create a file CSV
$csvFile = fopen('products.csv', 'w');

// Header of file CSV
fputcsv($csvFile, array('Product Name', 'Descriptions', 'Price', 'URL of Image'));

for ($page = 1; ; $page++) {
    $url = $baseUrl . $page;

    $response = $httpClient->get($url);
    $htmlString = (string) $response->getBody();

    // Check if HTML content is successfully retrieved
    if (!$htmlString) {
        break; // Stop if failed to retrieve HTML content
    }

    libxml_use_internal_errors(true);

    $doc = new DOMDocument();
    $doc->loadHTML($htmlString);

    $xpath = new DOMXPath($doc);

    $imageUrls = $xpath->evaluate('//div[@class="product-image"]//a//img/@data-full-size-image-url');
    $titles = $xpath->evaluate('//div[@class="product-meta"]//div[@class="product-description"]//h2/a');
    $descriptions = $xpath->evaluate('//div[@class="product-meta"]//div[@class="product-description"]//h2/a');
    $prices = $xpath->evaluate('//div[@class="product-meta"]//div[@class="product-description"]//div[@class="product-price-and-shipping"]//span[@class="price"]');

    foreach ($titles as $key => $title) {
        fputcsv($csvFile, array($title->textContent, $descriptions[$key]->textContent, $prices[$key]->textContent, $imageUrls[$key]->nodeValue));
    }

    // If there is no "next" link, break the loop
    $nextLink = $xpath->evaluate('//nav[@class="pagination"]//div[@class="row"]//ul[@class="page-list clearfix text-sm-right"]//li/a/@href');
    if (!$nextLink || !$nextLink->item(0)) {
        break;
    }
}

// Close the file
fclose($csvFile);

echo 'Les informations ont été récupérées et enregistrées dans products.csv';
?>
