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

            if (is_null($message)) {
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

                $connection->getChannel($channel)->message("{$message}");
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
                return "[ GitHub - New issue ] <cyan>{$repo}</cyan> - <light_cyan>{$title}</light_cyan> - <orange>{$user}</orange> - <light_cyan>{$body}</light_cyan> - {$url}";
            }

            if ($request->get('action') == 'closed') {
                return "[ GitHub - Closed issue ] <cyan>{$repo}</cyan> - <light_cyan>{$title}</light_cyan> - <orange>{$sender}</orange> - {$url}";
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

            return "[ GitHub - New Comment ] <cyan>{$repo}</cyan> - <light_cyan>{$title}</light_cyan> - <orange>{$commenter}</orange> - <light_cyan>{$comment}</light_cyan> - {$url}";
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

            return "[ GitHub - New Comment ] <cyan>{$repo}</cyan> - <yellow>{$title}</yellow> - <orange>{$commenter}</orange> - <light_cyan>{$comment}</light_cyan> - {$url}";
        }

        /**
         * @param \Dan\Web\Request $request
         *
         * @return string
         */
        protected function push(Request $request)
        {
            $repo = $request->get('repository.full_name');

            $message = $this->cleanString($request->get('head_commit.message')) ?? '(no description)';
            $author = $request->get('head_commit.author.name');
            $commitId = substr($request->get('head_commit.commit_id'), 0, 7);
            $url = shortLink($request->get('head_commit.url'));

            return "[ GitHub - New Commit ] <cyan>{$repo}</cyan> - <yellow>{$commitId}</yellow> - <light_cyan>{$message}</light_cyan> - <orange>{$author}</orange> - {$url}";
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

            if ($request->get('action') == 'opened') {
                return "[ GitHub - New Pull Request ] <cyan>{$repo}</cyan> - <light_cyan>{$title}</light_cyan> - <orange>{$author}</orange> - <light_cyan>{$message}</light_cyan> - {$url}";
            }

            if ($request->get('action') == 'closed') {
                return "[ GitHub - Closed Pull Request ] <cyan>{$repo}</cyan> - <light_cyan>{$title}</light_cyan> - <orange>{$author}</orange> - {$url}";
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
