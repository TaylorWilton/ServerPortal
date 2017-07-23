<?php

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class HackerNews implements iCacheable
{

    private $topPosts;

    public function __construct(int $numberOfPosts = 10)
    {

        $cached = $this->loadCache();

        if (empty($cached) || $this->isStale($cached)) {
            $this->topPosts = $this->GetTopHNPosts($numberOfPosts);
            $this->cache();
        } else {
            $this->topPosts = $cached;
        }
    }

    function loadCache(): array
    {
        $result = file_get_contents("./data/hackernews.json");
        if (!$result) {
            return [];
        }

        return json_decode($result, true);

    }

    function isStale(array $cache): bool
    {
        $dateSaved = new DateTime($cache['cachedAt']['date']);
        $now = new DateTime();
        $diff = $now->diff($dateSaved);

        return ($diff->days > 0 || $diff->h > 1);
    }

    private function GetTopHNPosts(
        int $numberOfPosts
    ): array {
        $client = new Client(['base_uri' => 'https://hacker-news.firebaseio.com/v0/', 'verify' => false]);

        $topTenHNIds = array_slice(json_decode($client->get("topstories.json")->getBody(),
            true), 0, $numberOfPosts);

        $promises = array_map(function ($id) use ($client) {
            return $client->getAsync(sprintf("item/%s.json", $id));
        }, $topTenHNIds);

        $results = Promise\settle($promises)->wait();

        $topTenHNPosts = array_map(function ($result) {
            return json_decode($result['value']->getBody());
        }, $results);

        return $topTenHNPosts;
    }

    function cache()
    {
        $this->topPosts["cachedAt"] = new DateTime();
        file_put_contents("./data/hackernews.json", json_encode($this->topPosts));
    }

    public function getTopPosts(): array
    {
        return $this->topPosts;
    }
}