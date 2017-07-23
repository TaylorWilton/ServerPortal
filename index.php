<?php
require 'vendor/autoload.php';


use MAL\MAL;
use MAL\MALParser;

$klein = new Klein\Klein;

$klein->respond(function ($request, $response, $service, $app) use ($klein) {

    $app->register('twig', function () {
        $loader = new Twig_Loader_Filesystem('views');
        return new Twig_Environment($loader);
    });
});

$klein->respond('/getData', function (\Klein\Request $request, \Klein\Response $response) {
    $settings = new Settings("config.yml");

    $weather = new Weather($settings->darkSkyAPIKey, $settings->lat, $settings->lon, $settings->location);
    $hackerNews = new HackerNews(10);
    $malData = new MAL("DrDeakz");
    $malParser = new MALParser($malData->getDataSource());
    $status = new Status();

    $bbc = new RSSParser("http://feeds.bbci.co.uk/news/technology/rss.xml", "BBC News - Technology");

    $dashboardData = [
        "forecast" => $weather->getForecast(),
        "hackernews" => $hackerNews->getTopPosts(),
        "airingAnime" => $malParser->currentlyAiring(),
        "disks" => $status->disks,
        "status" => $status->getStats(),
        "bbc" => $bbc->getFeed()
    ];

    $response->json($dashboardData);
});


$klein->respond('/', function ($request, $response, $service, $app) {
    echo $app->twig->render('dashboard.twig', []);
});

$klein->dispatch();
