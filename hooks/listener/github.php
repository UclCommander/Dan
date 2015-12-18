<?php

/**
 * Github push hook.
 */

use Dan\Core\Dan;
use Illuminate\Support\Collection;

hook('github_push')
    ->http()
    ->post('/github/push')
    ->func(function(Collection $args) {
        $data   = $args->get('data');
        $commit = head($data['commits']);
        $repo   = $data['repository'];

        $config = config('web.routes.github_push');

        if(!array_key_exists($repo['full_name'], $config))
            return;

        $config = $config[$repo['full_name']];

        $added      = "+" . count($commit['added']);
        $removed    = "-" . count($commit['removed']);
        $changed    = "*" . count($commit['modified']);

        $branch = last(explode('/', $data['ref']));

        $compiled = [
            "<cyan>{$repo['name']}</cyan>",
            "<yellow>{$repo['owner']['name']}</yellow>",
            "<light_cyan>" . cleanString($repo['description']) . "</light_cyan>",
            "<orange>{$branch}</orange>",
            "{$repo['language']}",
            "<cyan>{$commit['author']['name']}</cyan>",
            "<yellow>" . substr($commit['id'], 0, 8) . "</yellow>",
            "<light_cyan>{$commit['message']}</light_cyan>",
            "<green>$added</green>",
            "<red>$removed</red>",
            "<orange>$changed</orange>",
        ];

        foreach($config['post_to'] as $network => $channel)
        {
            if(!Dan::hasConnection($network))
                continue;

            $connection = connection($network);

            if(!$connection->inChannel($channel))
                continue;

            $connection->getChannel($channel)->message("[ " . implode(' | ', array_filter($compiled)) . " ]");
            $connection->getChannel($channel)->message("[ {$commit['url']} ]");
        }
    });