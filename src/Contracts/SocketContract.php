<?php namespace Dan\Contracts;


interface SocketContract {

    public function getStream();

    public function handle($resource);

    public function getName();

}