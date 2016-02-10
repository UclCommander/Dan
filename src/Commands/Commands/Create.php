<?php namespace Dan\Commands\Commands;


use Dan\Console\OutputStyle;
use Dan\Core\Dan;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Create
{
    /**
     * @var \Dan\Console\OutputStyle
     */
    protected $output;

    protected $aliases = [];

    protected $console = false;

    protected $private = false;

    protected $rank = null;

    protected $help = '';

    protected $requiresIrc = false;

    protected $group;

    /**
     * Setup constructor.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $inputInterface
     * @param \Symfony\Component\Console\Output\OutputInterface $outputInterface
     */
    public function __construct(InputInterface $inputInterface, OutputInterface $outputInterface)
    {
        new Dan($inputInterface, $outputInterface, true);

        $this->output = new OutputStyle($inputInterface, $outputInterface);
        $this->aliases = new Collection();
    }

    /**
     *
     */
    public function create()
    {
        $this->aliases->push($this->output->ask('Name of the command'));

        $this->aliases = $this->aliases->merge(explode(',', $this->output->ask('Any command aliases (comma separated)')));

        if ($this->output->confirm('Can this command be ran in the console?')) {
            $this->console = true;
            $this->requiresIrc = $this->output->confirm('Since it can be ran in a console, does it need an IRC connection specified?');
        }

        if ($this->output->confirm('Can this command be ran in a private message?')) {
            $this->private = true;
        }

        $this->rank = $this->output->ask('What rank does the command need?', 'vhoaqASC');

        $this->help = $this->output->ask('Help text');

        $directories = filesystem()->directories(addonsPath('commands'));

        foreach($directories as $k => $directory) {
            $directories[$k] = basename($directory);
        }

        $this->group = $this->output->choice("What group does the command belong in?", $directories);

        $this->createCommand();

        $this->output->success('Command created. Happy coding!');
    }

    /**
     *
     */
    protected function createCommand()
    {
        $aliases = $this->aliases->implode("', '");

        $options = "\n";

        if ($this->private) {
            $options .= "    ->allowPrivate()\n";
        }

        if ($this->console) {
            $options .= "    ->allowConsole()\n";
        }

        if ($this->requiresIrc) {
            $options .= "    ->requiresIrcConnection()\n";
        }

        $command = <<<PHP
<?php

use Dan\Contracts\UserContract;

command(['{$aliases}']){$options}    ->rank('{$this->rank}')
    ->helpText('{$this->help}')
    ->handler(function (UserContract \$user, \$message) {

    });
PHP;

        filesystem()->put(addonsPath("commands/{$this->group}/{$this->aliases[0]}.php"), $command);
    }
}