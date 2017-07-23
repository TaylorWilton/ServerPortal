<?php

namespace MAL;

use DateTime;

class MAL
{
    private $username;
    private $queryURL;
    private $data;
    private $debug = false;
    private $MALDataSource;

    function __construct(string $username)
    {
        $this->username = $username;
        $this->MALDataSource = new MALDataLocal($username);
        $cached = $this->MALDataSource->getData();
        $dateSaved = new DateTime($cached['cachedAt']['date']);
        $now = new DateTime();
        $diff = $now->diff($dateSaved);

        if ($diff->days > 0 || $diff->h > 5) {
            $this->MALDataSource = new MALDataRemote($username);
            $this->data = $this->MALDataSource->getData();
            $this->cache();
        } else {
            $this->data = $cached;
        }

    }

    private function cache()
    {
        $this->data["cachedAt"] = new DateTime();
        file_put_contents("./data/{$this->username}_mal.json", json_encode($this->data));
    }

    /**
     * @return iMALData
     */
    public function getDataSource(): iMALData
    {
        return $this->MALDataSource;
    }

    function dump()
    {
        return json_encode($this->data, true);
    }

}