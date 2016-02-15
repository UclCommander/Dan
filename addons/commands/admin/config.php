<?php


use Dan\Contracts\UserContract;
use Dan\Irc\Location\Channel;

command(['config'])
    ->allowPrivate()
    ->allowConsole()
    ->rank('S')
    ->helpText([
        'config reload - Reloads configuration from file.',
        'config set <key> <value> - Sets <value> on <key>.',
        'config get <key> - Gets the value of <key>.',
        //'config add <key> <value> - Adds <value> to the <key> array.',
        //'config remove <key> <value> - Removes <value> from the <key> array.',
    ])
    ->handler(new class {

        /**
         * @var array
         */
        protected $protected = [
            'irc.servers.*.user.pass',
            'irc.servers.*.control_channel',
            'irc.servers.*.channels'
        ];

        /**
         * @param \Dan\Contracts\UserContract $user
         * @param $message
         * @param \Dan\Irc\Location\Channel $channel
         *
         * @return bool
         */
        public function run(UserContract $user, $message, Channel $channel)
        {
            $userMethod = $channel ? 'notice' : 'message';

            $data = explode(' ', $message, 3);

            $key = $data[1] ?? null;
            $value = $data[2] ?? null;

            $method = 'config'.ucfirst(strtolower($data[0]));

            if (method_exists($this, $method)) {
                $output = $this->$method($key, $value);
            } else {
                return false;
            }

            $user->$userMethod($output);
        }

        /**
         * @return string
         */
        protected function configReload($key, $value)
        {
            dan('config')->load();
            return 'Config reloaded';
        }

        /**
         * @param $key
         * @param $value
         *
         * @return \Dan\Config\Config|mixed|string
         */
        protected function configGet($key, $value)
        {
            if (!config()->has($key)) {
                return "This key doesn't exist.";
            }

            foreach($this->protected as $pkey) {
                if(fnmatch($pkey, $key)) {
                    return 'This value is protected';
                }
            }

            $fetched = config($key);

            if (is_array($fetched)) {
                $fetched = json_encode($this->protectValues($fetched, $key));
            }

            return $fetched;
        }

        /**
         * @param $key
         * @param $value
         *
         * @return string
         */
        protected function configSet($key, $value)
        {
            if (!config()->has($key)) {
                return "This key doesn't exist.";
            }

            config()->set($key, $value);
            return 'Value set.';
        }

        /**
         * @param $array
         * @param $key
         *
         * @return mixed
         */
        protected function protectValues($array, $key)
        {
            foreach ($array as $k => $v) {
                foreach($this->protected as $protected) {
                    if (fnmatch($protected, "{$key}.{$k}")) {
                        $array[$k] = '[PROTECTED]';
                    } else if (is_array($v)) {
                        $array[$k] = $this->protectValues($v, "{$key}.{$k}");
                    }
                }
            }

            return $array;
        }

    });