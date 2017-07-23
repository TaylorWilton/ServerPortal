<?php

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Settings
{

    public $location;
    public $lat;
    public $lon;
    public $darkSkyAPIKey;
    public $MALUsername;
    public $debug;
    public $feeds;

    public function __construct(string $filename = "config.yml")
    {
        try {
            $config = Yaml::parse(file_get_contents($filename));
        } catch (ParseException $e) {
            exit("unable to parse the YAML string");
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        $this->location = $config['weatherLocation'];
        $this->lat = $config['latitude'];
        $this->lon = $config['longitude'];
        $this->darkSkyAPIKey = $config['DarkSkyApiKey'];
        $this->debug = $config['Debug'];
        $this->MALUsername = $config['MALUsername'];
        $this->feeds = $config['feeds'];
    }
}