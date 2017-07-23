<?php


class Stats
{

    public $disks;
    public $uptime;
    private $stats;

    public function __construct()
    {
        // mock for Windows
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->mock();
        } else {
            $cached = json_decode(file_get_contents("./data/stats.json"), true);
            $dateSaved = new DateTime($cached['cachedAt']['date']);
            $now = new DateTime();
            $diff = $now->diff($dateSaved);

            // if stale, get from shell then cache
            if ($diff->days > 0 || $diff->h > 1) {
                $disksToFind = ["/home/kirito/.Private", "/dev/sdb1"];
                $this->disks = $this->parseDisks($disksToFind);
                $this->stats = $this->generateStats();
                $this->cache();
            } else {
                $this->stats = $cached['stats'];
                $this->disks = $cached['disks'];
            }

        }

    }

    /**
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

    public function getStats(): array
    {
        return $this->stats;
    }

    private function mock(): array
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
                "name" => $element[0],
                "value" => substr($element[2], 0, -1),
                "max" => substr($element[1], 0, -1),
                "min" => 0,
            ];
        }, $result);

        return $result;
    }

    private function cache(): void
    {
        $data = [
            "disks" => $this->disks,
            "stats" => $this->stats,
            "cachedAt" => new DateTime()
        ];
        file_put_contents("./data/stats.json", json_encode($data));

    }
}