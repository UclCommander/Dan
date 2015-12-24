<?php namespace Dan\Contracts;


interface ShortLinkContract
{

    /**
     * Create the short link
     *
     * @param $link
     * @return mixed
     */
    public function create($link);
}