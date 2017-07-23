<?php

class RSSParser
{

    private $feedURL;
    private $feedTitle;
    private $data;

    public function __construct(string $feedURL, string $feedTitle)
    {
        $this->feedURL = $feedURL;
        $this->feedTitle = $feedTitle;

        $data = simplexml_load_file($feedURL);
        $this->data = $data;
    }

    public function getFeed(): array
    {
        $results = [
            "title" => $this->feedTitle,
            "items" => []
        ];
        $counter = 0;
        foreach ($this->data->channel->item as $item) {
            if ($counter > 9) {
                break;
            }
            $results["items"][] = [
                "title" => (string)$item->title,
                "link" => (string)$item->link
            ];
            $counter++;
        }

        return $results;
    }
}