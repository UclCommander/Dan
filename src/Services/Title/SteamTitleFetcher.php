<?php

namespace Dan\Services\Title;

use Dan\Contracts\TitleFetcherContract;
use Dan\Support\Url;
use Dan\Support\Web;
use DOMXPath;

class SteamTitleFetcher implements TitleFetcherContract
{
    /**
     * @throws \Exception
     *
     * @return array|void
     */
    public function fetchRandomGame() : array
    {
        $game = false;
        $i = 0;

        while (!str_contains($game, 'app')) {
            if ($i > 3) {
                $game = false;
                break;
            }

            $game = Url::getFinalUrl('http://store.steampowered.com/explore/random');
            $i++;
        }

        if (!$game) {
            throw new \Exception('Error fetching random steam game');
        }

        return $this->fetchTitle($game);
    }

    /**
     * @param $url
     *
     * @throws \Exception
     *
     * @return array
     */
    public function fetchTitle($url) : array
    {
        $cookie = 'birthtime=28801; path=/; domain=store.steampowered.com';

        if (ends_with($url, 'agecheck')) {
            $game = str_replace('agecheck', '', $url);
            $appId = last(array_filter(explode('/', $game)));
            $cookie = "mature_content=1; path=/app/{$appId}; domain=store.steampowered.com";
        }

        $dom = Web::dom($url, [], [], [
            CURLOPT_COOKIE => $cookie,
        ]);

        $xpath = new DOMXPath($dom);

        $tags = false;
        $releaseDate = false;
        $rating = false;
        $discount = false;
        $tagList = [];

        $title = trim($xpath->query('//div[@class="apphub_AppName"]')->item(0)->textContent);
        $item = $xpath->query('//div[@class="release_date"]/span[@class="date"]');

        if ($item->length) {
            $releaseDate = trim($item->item(0)->textContent);
        }

        $releaseTime = strtotime($releaseDate);
        $price = (time() < $releaseTime ? 'Coming Soon' : false);
        $item = $xpath->query('//meta[@itemprop="price"]');

        if ($item->length) {
            if ($item->item(0)->attributes->length) {
                $price = trim($item->item(0)->attributes->getNamedItem('content')->nodeValue);
                if ($price == '0.00') {
                    $price = 'Free';
                } else {
                    $price = "\${$price}";
                }
            }
        }

        $description = substr(trim($xpath->query('//div[@class="game_description_snippet"]')->item(0)->textContent), 0, 75);
        $item = $xpath->query('//span[contains(@class, "game_review_summary")]');

        if ($item->length) {
            $rating = trim($item->item(0)->textContent);
        }

        $item = $xpath->query('//div[@class="discount_pct"]');

        if ($item->length) {
            $discount = trim($item->item(0)->textContent);
        }

        $items = $xpath->query('//a[@class="app_tag"]');

        if ($items->length) {
            for ($i = 0; $i < min(3, $items->length); $i++) {
                $tagList[] = trim($items->item($i)->textContent);
            }
        }

        $colors = [
            'overwhelmingly-positive' => 'light_green',
            'very-positive'           => 'light_green',
            'positive'                => 'green',
            'mostly-positive'         => 'green',
            'mixed'                   => 'orange',
            'mostly-negative'         => 'red',
            'negative'                => 'red',
            'very-negative'           => 'maroon',
            'overwhelmingly-negative' => 'maroon',
        ];

        if (empty(trim($title))) {
            return []; // Let's forget about empty title games
        }

        if (count($tagList)) {
            $tags = implode(', ', $tagList);
        }

        return [
            "<cyan>{$title}</cyan>",
            ($price ? "<light_cyan>{$price}</light_cyan>".($discount ? " <lord_gaben_has_spoken> {$discount} </lord_gaben_has_spoken>" : '') : ''),
            ($rating ? "<{$colors[str_slug($rating)]}>{$rating}</{$colors[str_slug($rating)]}>" : ''),
            ($releaseDate ? "<light_blue>{$releaseDate}</light_blue>" : ''),
            ($tags ? "<fg=purple;options=bold>{$tags}</>" : ''),
            $description,
            shortLink($url),
        ];
    }
}
