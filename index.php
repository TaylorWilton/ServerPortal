<?php
require 'vendor/autoload.php';


use MAL\MAL;
use MAL\MALParser;

$klein = new Klein\Klein;

$klein->respond(function (
    \Klein\Request $request,
    \Klein\Response $response,
    \Klein\ServiceProvider $service,
    \Klein\App $app
) use ($klein) {

    $app->register('twig', function () {
        $loader = new Twig_Loader_Filesystem('views');
        return new Twig_Environment($loader);
    });
    $app->register('settings', function () {
        return new Settings("config.yml");

    });
});

$klein->respond('/getStats', function (\Klein\Request $request, \Klein\Response $response) {
    $stats = new Stats();
    $response->json(["stats" => $stats->getStats(), "disks" => $stats->disks,]);
});

$klein->respond('/getData',
    function (\Klein\Request $request, \Klein\Response $response, \Klein\ServiceProvider $service, \Klein\App $app) {
        // get settings config from $app
        $settings = $app->settings;

        $weather = new Weather($settings->darkSkyAPIKey, $settings->lat, $settings->lon, $settings->location);
        $hackerNews = new HackerNews(10);
        $malData = new MAL($settings->MALUsername);
        $malParser = new MALParser($malData->getDataSource());

        $feeds = array_map(function ($feed) {
            $rss = new RSSParser($feed['link'], $feed['title']);
            return $rss->getFeed();
        }, $settings->feeds);

        $dashboardData = [
            "forecast" => $weather->getForecast(),
            "hackernews" => $hackerNews->getTopPosts(),
            "airingAnime" => $malParser->currentlyAiring(),
            "feeds" => $feeds
        ];

        $response->json($dashboardData);
    });

// HOME PAGE
$klein->respond('/',
    function (\Klein\Request $request, \Klein\Response $response, \Klein\ServiceProvider $service, $app) {
        // render the dashboard, but let vueJS fill in the data
        echo $app->twig->render('dashboard.twig', []);
    });

$klein->dispatch();
