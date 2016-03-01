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
            $repo = $request->get('repository.full_name');

            $message = $this->cleanString($request->get('head_commit.message')) ?? '(no description)';
            $author = $request->get('head_commit.author.name');
            $commitId = substr($request->get('head_commit.commit_id'), 0, 7);
            $url = shortLink($request->get('head_commit.url'));

            return "[ GitHub - {$repo} ] New commit by <orange>{$author}</orange> - <yellow>{$commitId}</yellow> - <light_cyan>{$message}</light_cyan> - {$url}";
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

            return "[ GitHub - {$repo} ] Branch <light_cyan>{$branch}</light_cyan> created by <orange>{$by}</orange>";
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

            return "[ GitHub - {$repo} ] Branch <light_cyan>{$branch}</light_cyan> deleted by <orange>{$by}</orange>";
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
