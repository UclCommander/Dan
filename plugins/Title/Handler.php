<?php namespace Plugins\Title; 


abstract class Handler {

    protected $domains = [];
    protected $contentType = [];


    public function getDomains()
    {
        return $this->domains;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        //$bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}