<?php

namespace MAL;

abstract class MALData
{
    protected $data;

    public function getData(): array
    {
        return $this->data;
    }

    protected function parseJSON($json): array
    {
        $mal_data = json_decode($json, true);
        $cachedAt = isset($mal_data['cachedAt']) ? $mal_data['cachedAt'] : null;
        return [
            "myinfo" => $mal_data['myinfo'],
            "anime" => $mal_data['anime'],
            "cachedAt" => $cachedAt
        ];
    }
}

