<?php namespace Dan\Services\ShortLinks;


use Dan\Contracts\ShortLinkContract;
use Dan\Helpers\Web;

class Links implements ShortLinkContract
{
    /**
     * Create the short link
     *
     * @param $link
     * @return mixed
     */
    public function create($link)
    {
        $data = Web::post('https://links.ml/add', ['url' => $link]);

        $json = json_decode($data, true);

        if ($json == false) {
            return $link;
        }

        if (!$json['success']) {
            return $link;
        }

        return $json['url'];
    }
}