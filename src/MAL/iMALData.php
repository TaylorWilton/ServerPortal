<?php

namespace MAL;
interface iMALData
{
    function __construct(string $username);

    function getData(): array;

}