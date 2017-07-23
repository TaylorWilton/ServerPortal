<?php

namespace MAL;

class MALDataLocal extends MALData implements iMALData
{

    public function __construct(string $username)
    {
        $json = file_get_contents("./data/{$username}_mal.json");
        $this->data = $this->parseJSON($json);
    }
}
