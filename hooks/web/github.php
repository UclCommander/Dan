<?php

use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

hook('github')
    ->regex("/(?:.*)https?:\/\/github\.com\/([a-zA-Z0-9-_\.]+)\/?([a-zA-Z0-9-_\.]+)?\/?([a-zA-Z0-9-_\/\.]+)?(?:.*)/")
    ->anon(new class() {

        /** @var \Github\Client  */
        protected $api;

        /** @var \Github\Api\Repo */
        protected $repo;

        /** @var Channel */
        protected $channel;

        public function __construct()
        {
            $this->api  = new \Github\Client();
            $this->repo = $this->api->api('repo');
        }

        /**
         * Runs the hook.
         *
         * @param \Illuminate\Support\Collection $args
         * @return bool|null
         */
        public function run(Collection $args)
        {
            $this->channel = $args->get('channel');

            /** @var array $matches */
            $matches = array_filter(array_flatten($args->get('matches')));

            array_shift($matches);

            /** @var array $data */
            $matches = array_values($matches);

            $user = $matches[0];
            $repo = $matches[1] ?? null;
            $extra = isset($matches[2]) ? explode('/', $matches[2]) : null;

            $data = [];

            switch(count($matches)){
                case 1: {
                    $data = $this->user($user);
                    break;
                }
                case 2: {
                    $data = $this->repo($user, $repo);
                    break;
                }
                case 3: {
                    switch($extra[0]){
                        case 'tree': {
                            $data = $this->repo($user, $repo, ($extra[1] ?? null));
                            break;
                        }
                        case 'blob': {
                            $data = $this->blob($user, $repo, $extra);
                        }
                    }
                }
            }

            if(!empty($data)) {
                $this->channel->message("[ " . implode(' | ', array_filter($data)) . " ]");
                return true;
            }

            return null;
        }

        /**
         * Gets user information
         *
         * @param $user
         * @return array
         */
        public function user($user)
        {
            $userApi    = $this->api->api('user');
            $info       = $userApi->show($user);
            $starred    = count($userApi->starred($user));

            return [
                "<cyan>{$info['login']}</cyan>",
                "<yellow>{$info['location']}</yellow>",
                ($info['bio'] ? "<light_cyan>{$info['bio']}</light_cyan>" : ''),
                "{$info['blog']}",
                "<orange>{$starred}</orange> " . pluralize("Star", $starred),
                "<orange>{$info['public_repos']}</orange> " . pluralize("Repo", $info['public_repos']),
                "<orange>{$info['public_gists']}</orange> " . pluralize("Gist", $info['public_repos']),
                "<orange>{$info['followers']}</orange> " . pluralize("Follower", $info['public_repos']),
                "Following <orange>{$info['following']}</orange>",
            ];
        }

        /**
         * Gets specific file information.
         *
         * @param $user
         * @param $repo
         * @param $file
         * @return array
         */
        public function blob($user, $repo, $file)
        {
            $info   = $this->repo->show($user, $repo);
            $branch = $file[1];
            $path   = implode('/', array_splice($file, 2));

            $fileInfo = $this->repo->contents()->show($user, $repo, $path, $branch);
            $commit   = head($this->repo->commits()->all($user, $repo, ['path' => $path, 'sha' => $branch]));
            $sha      = substr($commit['sha'], 0, 7);

            return [
                "<cyan>{$info['name']}</cyan>",
                "<yellow>{$info['owner']['login']}</yellow>",
                "<light_cyan>" . cleanString($info['description']) . "</light_cyan>",
                "<orange>{$branch}</orange>",
                "{$fileInfo['name']}",
                convert($fileInfo['size']),
                $this->lines($fileInfo['content'], $fileInfo['encoding']) . ' lines',
                "<cyan>" . cleanString($commit['commit']['message']) . "</cyan>",
                "<light_cyan>{$sha}" . "</light_cyan>",
                "<yellow>{$commit['author']['login']}" . "</yellow>"
            ];
        }


        /**
         * Gets repo information.
         *
         * @param $user
         * @param $repo
         * @param null $branch
         * @return array
         */
        public function repo($user, $repo, $branch = null)
        {
            $info       = $this->repo->show($user, $repo);
            $branch     = $branch ?? $info['default_branch'];
            $commits    = $this->repo->commits()->all($user, $repo, ['sha' => $branch]);
            $first      = head($commits);
            $sha        = substr($first['sha'], 0, 7);

            return [
                "<cyan>{$info['name']}</cyan>",
                "<yellow>{$info['owner']['login']}</yellow>",
                "<light_cyan>" . cleanString($info['description']) . "</light_cyan>",
                "<orange>{$branch}</orange>",
                "{$info['language']}",
                "{$info['stargazers_count']} " . pluralize("Stargazer", $info['stargazers_count']),
                "{$info['watchers_count']} " . pluralize("Watcher", $info['watchers_count']),
                "{$info['forks']} " . pluralize("Fork", $info['forks']),
                "{$info['open_issues']} " . pluralize("Open Issue", $info['open_issues']),
                "<cyan>" . cleanString($first['commit']['message']) . "</cyan>",
                "<light_cyan>{$sha}" . "</light_cyan>",
                "<yellow>{$first['author']['login']}" . "</yellow>"
            ];
        }

        /**
         * Counts file lines.
         *
         * @param $content
         * @param $encoding
         * @return int
         */
        public function lines($content, $encoding)
        {
            $data = base64_decode($content);
            $lines = count(explode("\n", $data));
            return $lines;
        }
    });