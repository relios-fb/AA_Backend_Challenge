<?php

require_once("Helper/Crawler.php");

CONST MAX_CRAWL = 6;
CONST WEBSITE = "https://agencyanalytics.com";

$data = array('numPages' => 0, 'numImages' => 0, 'iLinks' => 0, 'eLinks' => 0, 'avgPageLoad' => 0, 'avgWordCount' => 0, 'avgTitleLength' => 0);
$table = array();
$urls = array(WEBSITE);

$images = array();
$internalLinks = array();
$externalLinks = array();



$baseUrl = WEBSITE;
$url = WEBSITE;

function imitateMerge(&$array1, &$array2)
{
    foreach($array2 as $i) {
        $array1[] = $i;
    }
}

function pushArrayUnique(&$array1, &$array2)
{
    foreach($array2 as $value) {
        if( !in_array($value,$array1))
        {
            array_push($array1,$value);
        }
    }
}

function wordCount(string $html)
{

    // Get rid of style, script etc
    $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
        '@<head>.*?</head>@siU',            // Lose the head section
        '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
        '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
    );

    $contents = preg_replace($search, '', $html);

    $result = array_count_values(
        str_word_count(
            strip_tags($contents), 1
        )
    );
    return array_sum($result);
}
for ($i = 0; $i < MAX_CRAWL; $i++)
{
    if (!isset($urls[$i])) {
        break;
    }

    $pageData = Helper\Crawler::getPage($url, 30, 30, 5);

    $row = ['url' => $url, 'status' => $pageData['response']];
    array_push($table, $row);

    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML($pageData['html']);

    $pageLinks = \Helper\Crawler::getWebsiteAnchors($dom, $baseUrl);
    pushArrayUnique($urls, $pageLinks);

    $pageImages = \Helper\Crawler::getImages($dom);
    imitateMerge($images, $pageImages);

    $links = \Helper\Crawler::getLinkTypes($dom, $baseUrl);
    imitateMerge($externalLinks, $links['external']);
    imitateMerge($internalLinks, $links['internal']);

    $data['avgPageLoad'] += $pageData['load_time'];
    $data['avgWordCount'] += wordCount($pageData['html']);
    $data['avgTitleLength'] += \Helper\Crawler::getTitleLength($dom);
    $url = isset($urls[$i + 1]) ? $urls[$i + 1] :  $url;

}
$urls = array_slice($urls, 0, $i);
$data['numPages'] = $i;
$data['numImages'] = count(array_unique($images));
$data['iLinks'] = count(array_unique($internalLinks));
$data['eLinks'] = count(array_unique($externalLinks));
$data['avgWordCount'] = $data['avgWordCount'] / $i;
$data['avgPageLoad'] = $data['avgPageLoad'] / $i;
$data['avgTitleLength'] = $data['avgTitleLength'] / $i;



