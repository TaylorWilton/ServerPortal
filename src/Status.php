<?php


class Status
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
            $this->disks = $this->parseDisks();
            $this->uptime = $this->parseUptime();
        }


    }

    /**
     * @return string
     */
    private function parseUptime(): string
    {
        exec("uptime", $result);
        $data = explode(' ', $result[0]);

        $data[4] = str_replace(",", "", $data[4]);

        return "{$data[3]}  {$data[4]}";
    }

    public function getStats()
    {
        return $this->stats;
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

    private function parseDisks(): array
    {
        $result = [];
        exec("df -h", $result);

        return $result;
    }
}