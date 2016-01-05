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
        $data    = $args->get('data');
        $repo    = $data['repository'];
        $config  = config('web.routes.github_push');

        if (!array_key_exists($repo['full_name'], $config)) {
            return;
        }

        $config = $config[$repo['full_name']];

        $message = '';

        if(isset($data['commits'])) {
            $commit = head($data['commits']);

            $added = "+".count($commit['added']);
            $removed = "-".count($commit['removed']);
            $changed = "*".count($commit['modified']);

            $branch = last(explode('/', $data['ref']));

            $compiled = [
                "[ Github - New Commit ] <cyan>{$repo['full_name']}</cyan>",
                "<orange>{$branch}</orange>",
                "<yellow>" . substr($commit['id'], 0, 8) . "</yellow>",
                "<cyan>{$commit['author']['name']}</cyan>",
                "<light_cyan>{$commit['message']}</light_cyan>",
                "<green>$added</green>/<red>$removed</red>/<orange>$changed</orange>",
                shortLink($commit['url']),
            ];

            $message = implode(' - ', array_filter($compiled));

        } elseif (isset($data['issue'])) {
            $issue = $data['issue'];

            if($data['action'] != 'opened' && $data['action'] != 'closed') {
                return;
            }

            $labels = [];

            foreach ($issue['labels'] as $label) {
                $labels[] = $label['name'];
            }

            $compiled = [
                "[ Github - " . ucfirst($data['action']) . " Issue ] <cyan>{$repo['full_name']}</cyan>",
                "Created by <cyan>{$issue['assignee']['login']}</cyan>",
                "<light_cyan>{$issue['title']}</light_cyan>",
                shortLink($issue['url']),
            ];

            $message = implode(' - ', array_filter($compiled));
        }

        foreach ($config['post_to'] as $network => $channel) {
            if (!Dan::hasConnection($network)) {
                continue;
            }

            $connection = connection($network);

            if (!$connection->inChannel($channel)) {
                continue;
            }

            $connection->getChannel($channel)->message($message);
        }
    });