<?php

class Stats implements iCacheable
{

    public $disks;
    private $stats;

    public function __construct(array $disksToFind)
    {
        // mock for Windows
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->mock();
        } else {
            $cached = $this->loadCache();

            if (empty($cached) || $this->isStale($cached)) {
                $this->disks = $this->parseDisks($disksToFind);
                $this->stats = $this->generateStats();
                $this->cache();
            } else {
                $this->stats = $cached['stats'];
                $this->disks = $cached['disks'];
            }
        }
    }

    private function mock()
    {
        $this->disks = [
            0 => [
                "name" => "/home/Kirito",
                "value" => 14,
                "max" => 115,
                "min" => 0,
            ],
            1 => [
                "name" => "/media/library",
                "value" => 202,
                "max" => 466,
                "min" => 0
            ]
        ];

        $this->stats = [
            [
                "name" => "Server Uptime",
                "value" => "15 days",
            ],
            [
                "name" => "Download Speed",
                "value" => "520.50Mbit/s",
            ],
            [
                "name" => "Upload Speed",
                "value" => "203.54Mbit/s"
            ]
        ];
    }

    function loadCache(): array
    {
        $result = file_get_contents("./data/stats.json");
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

        // 15 minutes old, or over a day
        return ($diff->days > 0 || $diff->m > 15);
    }

    private function parseDisks(array $find): array
    {
        $result = [];
        exec("df -h", $result);
        $result = array_map(function ($element) {
            return preg_split('/\s+/', $element);
        }, $result);
        $result = array_filter($result, function ($element) use ($find) {
            foreach ($find as $name) {
                if ($element[0] == $name) {
                    return true;
                }
            }
            return false;
        });

        $result = array_map(function ($element) {
            return [
                "name" => $element[5],
                "value" => substr($element[2], 0, -1),
                "max" => substr($element[1], 0, -1),
                "min" => 0,
            ];
        }, $result);

        return $result;
    }

    /**
     *
     * @return array
     */
    private function generateStats(): array
    {
        exec("uptime", $result);
        $data = explode(' ', $result[0]);

        $data[4] = str_replace(",", "", $data[4]);

        $uptime = "{$data[3]}  {$data[4]}";

        exec('speedtest-cli', $speeds);

        return [
            [
                "name" => "Uptime",
                "value" => $uptime
            ],
            [
                "name" => "Download Speed",
                "value" => explode(': ', $speeds[6])[1]
            ],
            [
                "name" => "Upload Speed",
                "value" => explode(': ', $speeds[8])[1]
            ]
        ];
    }

    function cache()
    {
        $data = [
            "disks" => $this->disks,
            "stats" => $this->stats,
            "cachedAt" => new DateTime()
        ];
        file_put_contents("./data/stats.json", json_encode($data));

    }

    public function getStats(): array
    {
        return $this->stats;
    }
}