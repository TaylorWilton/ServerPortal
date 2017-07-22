<?php

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class HackerNews
{

    private $topPosts;

    public function __construct(int $numberOfPosts = 10)
    {
        $cached = json_decode(file_get_contents("./data/hackernews.json"), true);
        $dateSaved = new DateTime($cached['cachedAt']['date']);
        $now = new DateTime();
        $diff = $now->diff($dateSaved);

        // if stale, get from HN then cache
        if ($diff->days > 0 || $diff->h > 1) {
            $this->topPosts = $this->GetTopHNPosts($numberOfPosts);
            $this->cache();
        } else {
            $this->topPosts = $cached;
        }
    }

    public function getTopPosts(): array
    {
        return $this->topPosts;
    }


    private function GetTopHNPosts(int $numberOfPosts): array
    {
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

    private function cache()
    {
        $this->topPosts["cachedAt"] = new DateTime();
        file_put_contents("./data/hackernews.json", json_encode($this->topPosts));
    }
}