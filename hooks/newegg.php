<?php

use Dan\Helpers\Web;

$regex = "/(?:.*)(?:www.newegg.com|newegg.com)\/Product\/Product\.aspx\?.*?Item=([-_a-zA-Z0-9]+)(?:.*)/";

hook(['regex' => $regex], function(array $eventData, array $matches) {

    $headers = [
        'User-Agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3',
        'Referer'       => 'http://www.newegg.com/'
    ];

    foreach ($matches[1] as $match)
    {
        $json = Web::json("http://www.ows.newegg.com/Products.egg/{$match}/ProductDetails", [], $headers, false);

        $data = [];

        $data[] = "{cyan}" . (strlen($json['Title']) > 50 ? substr($json['Title'], 0, 50) . '...' : $json['Title']);
        $data[] = "{yellow}{$json['OriginalPrice']}";
        $data[] = "{yellow}{$json['FinalPrice']}";

        $reviews = substr($json['ReviewSummary']['TotalReviews'], 1, -1);
        $rating = $json['ReviewSummary']['Rating'];

        if ($reviews == '')
            $reviews = 0;

        $data[] = "{green}{$rating}/5 eggs from {$reviews} reviews";

        if ($json['IsFeaturedItem'])
            $data[] = "{blue}Featured";

        if ($json['Instock'])
            $data[] = "{green}In Stock";
        else
            $data[] = "{maroon}Out Of Stock";

        if ($json['FreeShippingFlag'])
            $data[] = "{green}Free Shipping";


        if ($json['IsShellShockerItem'])
            $data[] = "{orange}Shell Shocker";

        $data = implode(" {reset}| ", $data);

        return "{reset}[ {$data} {reset}]";
    }
});