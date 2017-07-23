<?php

namespace MAL;

class MALParser
{

    private $totalShowsCompleted;

    function __construct(iMALData $MALData)
    {
        $this->data = $MALData->getData()['anime'];

        usort($this->data, function ($a, $b) {
            return $b['my_score'] <=> $a['my_score'];
        });
    }

    /**
     * @param string $name
     * @param bool $exact
     * @return array
     */
    function findShow(string $name, bool $exact = true)
    {
        if ($exact) {
            $key = array_search($name, array_column($this->data, 'series_title'));
            // since 0 is false, but also a valid array key, this step is needed
            if ($key === false) {
                return [];
            }
            return $this->data[$key];
        }

        $matches = [];

        foreach ($this->data as $anime) {
            if (stripos($anime['series_title'], $name) !== false) {
                $matches[] = $anime;
            }
        }
        return $matches;
    }

    function topN(int $number = 5): array
    {
        return array_slice($this->data, 0, $number);
    }

    function bottomN(int $number = 5): array
    {
        return array_slice(array_reverse($this->data), 0, $number);
    }

    function whereScoreIs(int $score): array
    {
        return array_filter($this->data, function ($v, $k) use ($score) {
            return $v['my_score'] == $score;
        }, ARRAY_FILTER_USE_BOTH);
    }

    function currentlyWatching(): array
    {
        return array_filter($this->data, function ($v, $k) {
            return $v['my_status'] == 1;
        }, ARRAY_FILTER_USE_BOTH);
    }

    function currentlyAiring(): array
    {
        $airing = array_filter($this->data, function ($v, $k) {
            $end = $v['series_end'];
            if ($end === '0000-00-00') {
                return true;
            }

            $endDate = date_create_from_format("Y-m-d", $end);
            $now = new \DateTime();

            return $now < $endDate;


        }, ARRAY_FILTER_USE_BOTH);

        $airing = array_map(function ($element) {
            $startDate = new \DateTime($element['series_start']);
            $startDate->add(new \DateInterval('P1D'));
            $element['dayAiring'] = $startDate->format('l');
            return $element;
        }, $airing);

        $days = array_unique(array_column($airing, 'dayAiring'));
        $result = [];
        foreach ($days as $day) {
            $result[] = [
                "dayName" => $day,
                "shows" => array_filter($airing, function ($v) use ($day) {
                    return $v['dayAiring'] == $day;
                })
            ];
        }

        return $result;
    }

    function totalShowsCompleted(): int
    {
        // cache on first call
        if ($this->totalShowsCompleted) {
            return $this->totalShowsCompleted;
        }

        $this->totalShowsCompleted = count(
            array_filter($this->data, function ($v, $k) {
                return $v['my_status'] == 2;
            }, ARRAY_FILTER_USE_BOTH));

        return $this->totalShowsCompleted;
    }

}