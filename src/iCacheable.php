<?php

interface iCacheable
{

    function cache();

    function isStale(array $cache): bool;

    function loadCache(): array;

}