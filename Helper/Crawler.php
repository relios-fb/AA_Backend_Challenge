<?php

namespace Helper;

class Crawler {

    CONST USER_AGENT = 'Googlebot/2.1 (+http://www.googlebot.com/bot.html)';
    CONST ENCODING = 'gzip,deflate';


    /*
     * Gets the contents of a webpage using curl
     *
     * Returns an array containing the html, response code, and the total time elapsed
     */
    public function getPage(string $url,int $connectTimeout, int $timeout, string $maxRedirect): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $maxRedirect);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING, self::ENCODING);

        $curl = curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $loadTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $html = file_get_contents($url);

        return ['curl' => $curl, 'response' => $response, 'load_time' => $loadTime, 'html' => $html];
    }


    public function getAnchors(\DOMDocument $doc) : array
    {
        $hrefs = array();

        $anchors = $doc->getElementsByTagName('a');
        foreach ($anchors as $anchor)
        {
            array_push($hrefs, $anchor->getAttribute('href'));
        }
        return $hrefs;
    }

    public function parseData(\DOMDocument $doc) : array
    {
        $images = array();
        $iLinks = array();
        $eLinks = array();
        $wordCount = 0;
        $titles = array();

        $imageElements = $doc->getElementsByTagName('img');
        foreach ($imageElements as $imageElement)
        {
            if (!in_array($imageElement->nodeValue, $images))
            {
                array_push($images, $imageElement->nodeValue);
            }
        }
    }

    public function countLinks(\DOMNodeList $links, string $url) : array
    {
        $iLinks = 0;
        $eLinks = 0;

        foreach ($links as $link)
        {
            if (strpos($link->getAttribute('href'), $url)) {
                $iLinks++;
            } else {
                $eLinks++;
            }
        }

        return ['internal' => $iLinks, 'external' => $eLinks];
    }

    public function countImages(\DOMNodeList $images) : int
    {
        $imageArray = array();

        foreach ($images as $image)
        {
            if (!in_array($image->getAttribute('src'), $imageArray)) {
                array_push($imageArray, $image->getAttribute('src'));
            }
        }

        return count($imageArray);
    }

    public function wordCount(string $html)
    {
        return (array_count_values(str_word_count(strip_tags(strtolower($html)), 1)));
    }

    public function getTitleLength(\DOMNodeList $doc) : int {
        $title = '';
        $list = $doc->getElementsByTagName("title");
        if ($list->length > 0) {
            $title = $list->item(0)->textContent;
        }
        return count($title);
    }


}