<?php

namespace Dan\Contracts;

interface TitleFetcherContract
{
    public function fetchTitle($url) : array;
}
