<?php

namespace Dan\Contracts;

interface ShortLinkContract
{
    /**
     * @param $link
     *
     * @return mixed
     */
    public function create($link);
}
