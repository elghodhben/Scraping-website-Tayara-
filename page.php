<?php
# scraping books to scrape: https://books.toscrape.com/
require './vendor/autoload.php';

$httpClient = new \GuzzleHttp\Client();

$response = $httpClient->get('https://books.toscrape.com/');
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
fputcsv($csvFile, array('Nom du produit', 'Prix', 'Lien du produit', 'Lien de l\'image'));

$doc = new DOMDocument();
$doc->loadHTML($htmlString);
$xpath = new DOMXPath($doc);
$titles = $xpath->evaluate('//ol[@class="row"]//li//article//h3/a');
$prices = $xpath->evaluate('//ol[@class="row"]//li//article//div[@class="product_price"]//p[@class="price_color"]');

foreach ($titles as $key => $title) {
echo $title->textContent . ' @ '. $prices[$key]->textContent.PHP_EOL;
    // Écrire les informations dans le fichier CSV
    fputcsv($csvFile, array($title->textContent, $prices[$key]->textContent.PHP_EOL));
}

// Fermer le fichier CSV
fclose($csvFile);

echo 'Les informations ont été récupérées et enregistrées dans products.csv';
