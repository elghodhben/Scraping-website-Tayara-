<?php
# scraping books to scrape: https://books.toscrape.com/
require './vendor/autoload.php';

$httpClient = new \GuzzleHttp\Client();

$response = $httpClient->get('https://pharma-shop.tn/841-hydratants-toutes-peaux?fbclid=IwAR1YsPvKs5krEeV64TRmMafp-b_1cqPOPqEXBzZl1ARt8u0cDQkuLy7FL8Q');
$htmlString = (string) $response->getBody();

// Check if HTML content is successfully retrieved
if (!$htmlString) {
    die('Failed to retrieve HTML content from the URL.');
}

//add this line to suppress any warnings
libxml_use_internal_errors(true);

// Créer un fichier CSV
$csvFile = fopen('products.csv', 'w');

// En-têtes du fichier CSV
fputcsv($csvFile, array('Product Name', 'Desciptions', 'price', 'Url OF Image'));

$doc = new DOMDocument();
$doc->loadHTML($htmlString);

$xpath = new DOMXPath($doc);

$imageUrls = $xpath->evaluate('//div[@class="product-image"]//a//img/@data-full-size-image-url');
$titles = $xpath->evaluate('//div[@class="product-meta"]//div[@class="product-description"]//h2/a');
$descriptions = $xpath->evaluate('//div[@class="product-meta"]//div[@class="product-description"]//h2/a');
$prices = $xpath->evaluate('//div[@class="product-meta"]//div[@class="product-description"]//div[@class="product-price-and-shipping"]//span[@class="price"]');

foreach ($titles as $key => $title) {
    // echo $title->textContent . ' @ ' . $prices[$key]->textContent . PHP_EOL;

    fputcsv($csvFile, array($title->textContent, $descriptions[$key]->textContent, $prices[$key]->textContent, $imageUrls[$key]->nodeValue));
}

// Fermer le fichier CSV
fclose($csvFile);

echo 'Les informations ont été récupérées et enregistrées dans products.csv';
