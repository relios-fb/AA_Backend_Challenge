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
    public function getPage(string $url,int $connectTimeout, int $timeout, int $maxRedirect): array
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

        $html = curl_exec($ch);
        curl_close($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $loadTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

        return ['html' => $html, 'response' => $response, 'load_time' => $loadTime];
    }


    /*
     * Gets all html anchors given a DomDocument
     *
     * Returns an array of links
     */
    public function getAnchors(\DOMDocument $dom) : array
    {
        $hrefs = array();

        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $anchor)
        {
            array_push($hrefs, $anchor->getAttribute('href'));
        }
        return $hrefs;
    }

    /*
     * Gets all html anchors given a DomDocument
     *
     * Returns an array of links separated into external and internal links.
     */
    public function getLinks(\DOMDocument $dom, string $url) : array
    {
        $iLinks = array();
        $eLinks = array();

        $links = $dom->getElementsByTagName("a");

        foreach ($links as $link)
        {
            if (strpos($link->getAttribute('href'), $url)) {
                array_push($iLinks, $link->getAttribute('href'));
            } else {
                array_push($eLinks, $link->getAttribute('href'));
            }
        }

        return ['internal' => $iLinks, 'external' => $eLinks];
    }

    /*
     * Gets all img tags given a DOMDocument
     * Does not account for SVGs
     *
     * Returns an array of image sources
     */
    public function getImages(\DOMDocument $dom) : array
    {
        $imageArray = array();
        $images = $dom->getElementsByTagName("img");
        foreach ($images as $image)
        {
            array_push($imageArray, $image->getAttribute('src'));
        }

        return $imageArray;
    }



    /*
     * Gets the length of a title given a DOMDocument
     *
     * Returns the title length
     */
    public function getTitleLength(\DOMDocument $dom) : int {
        $title = '';
        $list = $dom->getElementsByTagName("title");
        if ($list->length > 0) {
            $title = $list->item(0)->textContent;
        }
        return strlen($title);
    }


}