<?php

use Dan\Web\Request;

route('github.event')
    ->config([
        'uclcommander/dan' => [
            'post_to' => [
                'network' => '#channel'
            ]
        ]
    ])
    ->path('/github/event')
    ->post(new class {

        public function run(Request $request)
        {
            $type = $request->header('x-github-event');

            if (!method_exists($this, $type)) {
                console()->debug("GitHub event {$type} doesn't exist.");
                return;
            }

            $message = $this->$type($request);

            if (empty($message)) {
                return;
            }

            $config = config('github_event.'.$request->get('repository.full_name'));

            foreach ($config['post_to'] as $network => $channel) {
                if (!connection()->hasConnection($network)) {
                    continue;
                }

                /** @var \Dan\Irc\Connection $connection */
                $connection = connection($network);

                if (!$connection->inChannel($channel)) {
                    continue;
                }

                $channel = $connection->getChannel($channel);

                foreach ((array) $message as $send) {
                    $channel->message($send);
                }
            }
        }

        /**
         * @param \Dan\Web\Request $request
         *
         * @return string
         */
        protected function ping(Request $request)
        {
            return "[ GitHub ] Received ping from GitHub for repository {$request->get('repository.full_name')}.";
        }

        /**
         * @param \Dan\Web\Request $request
         *
         * @return string
         */
        protected function issues(Request $request)
        {
            $repo = $request->get('repository.full_name');
            $user = $request->get('issue.user.login');
            $title = $request->get('issue.title');
            $body = $this->cleanString($request->get('issue.body')) ?? '(no description)';
            $url = shortLink($request->get('issue.html_url'));
            $sender = $request->get('sender.login');

            if ($request->get('action') == 'opened') {
                return "[ GitHub - {$repo} ] New issue created by <orange>{$user}</orange> - <cyan>{$title}</cyan> - <light_cyan>{$body}</light_cyan> - {$url}";
            }

            if ($request->get('action') == 'closed') {
                return "[ GitHub - {$repo} ] Issue <cyan>{$title}</cyan> closed by <orange>{$sender}</orange> - {$url}";
            }
        }

        /**
         * @param \Dan\Web\Request $request
         *
         * @return string
         */
        protected function issue_comment(Request $request)
        {
            $title = $request->get('issue.title');
            $commenter = $request->get('comment.user.login');
            $comment = $this->cleanString($request->get('comment.body')) ?? '(no description)';
            $repo = $request->get('repository.full_name');
            $url = shortLink($request->get('comment.html_url'));

            return "[ GitHub - {$repo} ] New comment by <orange>{$commenter}</orange> on issue <cyan>{$title}</cyan> - <light_cyan>{$comment}</light_cyan> - {$url}";
        }

        /**
         * @param \Dan\Web\Request $request
         *
         * @return string
         */
        protected function commit_comment(Request $request)
        {
            $title = substr($request->get('comment.commit_id'), 0, 7);
            $commenter = $request->get('comment.user.login');
            $comment = $this->cleanString($request->get('comment.body')) ?? '(no description)';
            $repo = $request->get('repository.full_name');
            $url = shortLink($request->get('comment.html_url'));

            return "[ GitHub - {$repo} ] New comment by <orange>{$commenter}</orange> on commit <yellow>{$title}</yellow> - <light_cyan>{$comment}</light_cyan> - {$url}";
        }

        /**
         * @param \Dan\Web\Request $request
         *
         * @return string
         */
        protected function push(Request $request)
        {
            // Ignored deleted commits
            if ($request->get('deleted', false)) {
                return null;
            }

            // Pushing to a branch
            if (strpos($request->get('ref'), 'refs/heads') === 0) {
                $repo = $request->get('repository.full_name');

                $message = $this->cleanString($request->get('head_commit.message')) ?? '(no description)';
                $author = $request->get('head_commit.author.name');
                $commitId = substr($request->get('head_commit.id'), 0, 7);
                $url = shortLink($request->get('head_commit.url'));

                return "[ GitHub - {$repo} ] New commit by <orange>{$author}</orange> - <yellow>{$commitId}</yellow> - <light_cyan>{$message}</light_cyan> - {$url}";
            }
        }

        /**
         * @param \Dan\Web\Request $request
         *
         * @return string
         */
        protected function pull_request(Request $request)
        {
            $repo = $request->get('repository.full_name');

            $title = $request->get('pull_request.title');
            $message = $this->cleanString($request->get('pull_request.body')) ?? '(no description)';
            $author = $request->get('pull_request.user.login');
            $url = shortLink($request->get('pull_request.url'));
            $by = $request->get('sender.login');

            if ($request->get('action') == 'opened') {
                return "[ GitHub - {$repo} ] <orange>{$author}</orange> opened a new Pull Request. - <cyan>{$title}</cyan> - <light_cyan>{$message}</light_cyan> - {$url}";
            }

            if ($request->get('action') == 'closed') {
                return "[ GitHub - {$repo} ] Pull request <cyan>{$title}</cyan> closed by <orange>{$by}</orange> - {$url}";
            }
        }

        /**
         * @param \Dan\Web\Request $request
         *
         * @return string
         */
        protected function create(Request $request)
        {
            $repo = $request->get('repository.full_name');
            $branch = $request->get('ref');
            $by = $request->get('sender.login');

            if ($request->get('ref_type') == 'branch') {
                return "[ GitHub - {$repo} ] Branch <light_cyan>{$branch}</light_cyan> created by <orange>{$by}</orange>";
            }

            if ($request->get('ref_type') == 'tag') {
                return "[ GitHub - {$repo} ] Tag <light_cyan>{$branch}</light_cyan> created by <orange>{$by}</orange>";
            }
        }

        /**
         * @param \Dan\Web\Request $request
         *
         * @return string
         */
        protected function delete(Request $request)
        {
            $repo = $request->get('repository.full_name');
            $branch = $request->get('ref');
            $by = $request->get('sender.login');

            if ($request->get('ref_type') == 'branch') {
                return "[ GitHub - {$repo} ] Branch <light_cyan>{$branch}</light_cyan> deleted by <orange>{$by}</orange>";
            }

            if ($request->get('ref_type') == 'tag') {
                return "[ GitHub - {$repo} ] Tag <light_cyan>{$branch}</light_cyan> deleted by <orange>{$by}</orange>";
            }
        }

        /**
         * @param \Dan\Web\Request $request
         *
         * @return array|string
         */
        protected function release(Request $request)
        {
            if ($request->get('action') == 'published') {
                $url = shortLink($request->get('release.html_url'));
                $title = $request->get('release.name');
                $tag = $request->get('release.tag_name');
                $author = $request->get('release.author.login');
                $description = $request->get('release.body');
                $repo = $request->get('repository.full_name');
                $downloads = (array) $request->get('release.assets');

                $lines = " [ GitHub - {$repo} ] New release by <orange>{$author}</orange> - <yellow>{$tag}</yellow> - <cyan>{$title}</cyan> - <light_cyan>{$description}</light_cyan> - {$url}";

                $list = [];

                foreach ($downloads as $download) {
                    $url = shortLink($download['browser_download_url']);
                    $readable = convert($download['size']);
                    $list[] = "<orange>{$download['name']}</orange> - {$url} - <yellow>{$readable}</yellow>";
                }

                $lines[] = '[ ' .  implode(' | ', $list ). ' ]';

                return $lines;
            }
        }

        /**
         * @param $string
         *
         * @return mixed
         */
        protected function cleanString($string)
        {
            return str_replace(["\n", "\r", '  '], ' ', $string);
        }
    });
