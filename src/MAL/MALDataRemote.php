<?php

namespace MAL;

class MALDataRemote extends MALData implements iMALData
{
    function __construct(string $username)
    {
        $url = "https://myanimelist.net/malappinfo.php?u=" . $username . "&status=all&type=anime";
        $xml = simplexml_load_string(file_get_contents($url));
        $json = json_encode($xml);
        $this->data = $this->parseJSON($json);
    }
}