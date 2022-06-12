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
    public static function getPage(string $url,int $connectTimeout, int $timeout, int $maxRedirect): array
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

        $curlInfo = curl_getinfo($ch);
        $response = $curlInfo['http_code'];
        $loadTime = $curlInfo['total_time'];
        curl_close($ch);
        return ['html' => $html, 'response' => $response, 'load_time' => $loadTime];
    }


    /*
     * Gets all anchors given a DomDocument
     *
     * Returns an array of links
     */
    public static function getWebsiteAnchors(\DOMDocument $dom, string $baseUrl) : array
    {
        $hrefs = array();

        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $anchor)
        {
            $url = $anchor->getAttribute('href');
            $parse = parse_url($url);

            if (!isset($parse['host'])) {
                $path = isset($parse['path']) ? $parse['path'] : '';
                $url = $baseUrl . $path;
                $url = rtrim($url, '/\\');
                array_push($hrefs, $url);
            }
        }
        return $hrefs;
    }

    /*
     * Gets all html anchors given a DomDocument
     *
     * Returns an array of links separated into external and internal links.
     */
    public static function getLinkTypes(\DOMDocument $dom, $baseUrl) : array
    {
        $iLinks = array();
        $eLinks = array();

        $links = $dom->getElementsByTagName("link");

        foreach ($links as $link)
        {
            if ($link->getAttribute('href')[0] == '/' || is_numeric(strpos($link->getAttribute('href'), $baseUrl)) ) {
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
    public static function getImages(\DOMDocument $dom) : array
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
    public static function getTitleLength(\DOMDocument $dom) : int {
        $title = '';
        $list = $dom->getElementsByTagName("title");
        if ($list->length > 0) {
            $title = $list->item(0)->textContent;
        }
        return strlen($title);
    }


}