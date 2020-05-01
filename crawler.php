<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE); //hehe xd
unset($argv[0]);    // remove first argument (crawler.php)
$start = $argv[1]; // This is the starting url (passed by php commandline)
if (!$start){
    echo("Tja das wars jetzt komplett...");
}
$crawling = array();            //to remember urls to be crawled
$already_crawled = array();     //to remember crawled urls

function follow_links($url) {

    global $already_crawled;    // access to crawl list.
    global $crawling;           // access to crawl list.
    $context = stream_context_create(array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: kekBot/0.2\n")));     //give Useragent Context

    $doc = new DOMDocument();   // new Dom Object

    @$doc->loadHTML(@file_get_contents($url, false, $context)); // Download the page

    $linklist = $doc->getElementsByTagName("a");     // Array for all elements with an a tag.

    foreach ($linklist as $link) {      // Loop through collected links.
        $l =  $link->getAttribute("href");
        // Process all kinds of link implementation.
        if (substr($l, 0, 1) == "/" && substr($l, 0, 2) != "//") {
            $l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
        } else if (substr($l, 0, 2) == "//") {
            $l = parse_url($url)["scheme"].":".$l;
        } else if (substr($l, 0, 2) == "./") {
            $l = parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l, 1);
        } else if (substr($l, 0, 1) == "#") {
            $l = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$l;
        } else if (substr($l, 0, 3) == "../") {
            $l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
        } else if (substr($l, 0, 11) == "javascript:") {
            continue;
        } else if (substr($l, 0, 5) != "https" && substr($l, 0, 4) != "http") {
            $l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
        }

        if (!in_array($l, $already_crawled)) {  // Checks if the link is already crawled.
            $already_crawled[] = $l;
            $crawling[] = $l;
            echo get_details($l)."\n";  // Output for title, descriptions, keywords, url.
        }

    }
    array_shift($crawling); // Arrayshift to remove crawled page.
    foreach ($crawling as $site) {
        follow_links($site);
    }

}

function get_details($url) {
    $context = stream_context_create(array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: kekBot/0.2\n")));// The array for our User Agent.
    $doc = new DOMDocument();       //new Dom Object
    @$doc->loadHTML(@file_get_contents($url, false, $context)); // Download the page
    $description = "";      //set vars for description and Keywords
    $keywords = "";

    $metas = $doc->getElementsByTagName("meta");    // search meta tags.

    for ($i = 0; $i < $metas->length; $i++) {       // check all meta tags.
        $meta = $metas->item($i);

        if (strtolower($meta->getAttribute("name")) == "keywords")      // Check if there are any keywords.
            $keywords = $meta->getAttribute("content");                 // get keywords.
        if (strtolower($meta->getAttribute("name")) == "description")   // Check if there is an description.
            $description = $meta->getAttribute("content");              // get description.

    }

    $title = $doc->getElementsByTagName("title");   // Array for page titles.
    $title = $title->item(0)->nodeValue;

    return("Titel: ".$title . "\nURL: " . $url . "\nBeschreibung: " . $description . "\nKeywords: " . $keywords . "\n");
}

follow_links($start);   // Start crawling.