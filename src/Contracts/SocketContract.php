<?php namespace Dan\Contracts;


interface SocketContract {

    /**
     * @return string
     */
    public function getName();

    /**
     * @return resource
     */
    public function getStream();

    /**
     * @param $resource
     */
    public function handle($resource);
}