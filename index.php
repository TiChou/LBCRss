<?php
/**
 * Code minimalisme de génération de flux RSS pour Leboncoin.fr
 * @version 1.0
 */

$dirname = dirname(__FILE__);

require $dirname."/lib/feedgenerator/FeedGenerator.php";
require $dirname."/lib/lbc.php";

date_default_timezone_set("Europe/Paris");

if (empty($_GET["url"])) {
    require $dirname."/form.php";
    return;
}

try {
    $_GET["url"] = Lbc::formatUrl($_GET["url"]);
} catch (Exception $e) {
    echo "Cette adresse ne semble pas valide.";
    exit;
}

$content = file_get_contents($_GET["url"]);
$content = mb_convert_encoding($content, "ISO-8859-15", "WINDOWS-1252");
$ads = Lbc_Parser::process($content, $_GET);

$title = "Leboncoin";
$urlParams = parse_url($_GET["url"]);
if (!empty($urlParams["query"])) {
    parse_str($urlParams["query"], $aQuery);
    if (!empty($aQuery["q"])) {
        $title .= " - ".$aQuery["q"];
    }
}

$feeds = new FeedGenerator();
$feeds->setGenerator(new RSSGenerator);
$feeds->setTitle($title);
$feeds->setLink("http://www.leboncoin.fr");
$feeds->setDescription("Flux RSS de la recherche : ".$_GET["url"]);

if (count($ads)) {
    foreach ($ads AS $ad) {
        $item = new FeedItem(
            md5($ad->getId().$ad->getDate()),
            $ad->getTitle(),
            $ad->getLink(),
            require $dirname."/view.phtml"
        );
        $item->pubDate = date('D, d M Y H:i:s O', $ad->getDate());
        $feeds->addItem($item);
    }
}
$feeds->display();
