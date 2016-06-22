<?php

namespace Dan\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginInstallCommand extends Command
{
    protected function configure()
    {
        $this->setName('plugin:install')
            ->setDescription('Installs the given plugin')
            ->addArgument('repo', InputArgument::REQUIRED, 'Git repo')
            ->addArgument('branch', InputArgument::OPTIONAL, 'Git branch', 'dev-master')
            ->addOption('enable', 'e', InputOption::VALUE_NONE, 'Automatically enable the plugin');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $input->getArgument('repo');

        if (!file_exists(ROOT_DIR.'/plugins/plugins.json')) {
            file_put_contents(ROOT_DIR.'/plugins/plugins.json', json_encode(['repositories' => []], JSON_PRETTY_PRINT));
        }

        $installed = json_decode(file_get_contents(ROOT_DIR.'/plugins/plugins.json'), true);

        if (strpos($repo, '/') === false && strpos($repo, 'danthebot') === false) {
            $repo = "danthebot/{$repo}";
        }

        if (strpos($repo, 'git@') === false) {
            $repo = "git@github.com:{$repo}.git";
        }

        foreach ($installed['repositories'] as $repository) {
            if ($repository == $repo) {
                throw new \Exception('This plugin is already installed');
            }
        }

        $installed['repositories'][] = $repo;
        $name = last(explode('/', rtrim(last(explode(':', $repo)), '.git')));

        file_put_contents(ROOT_DIR.'/plugins/plugins.json', json_encode($installed, JSON_PRETTY_PRINT));
        shell_exec('cd '.ROOT_DIR."/plugins && git clone {$repo} && cd {$name} && composer install");

        if ($input->getOption('enable')) {
            $this->getApplication()
                ->find('plugin:enable')
                ->run(new ArrayInput([
                    'command' => 'plugin:enable',
                    'name'    => $name,
                ]), $output);
        }
    }

    /**
     * @return array
     */
    protected function composerJson()
    {
        return [
            'name'         => 'danthebot/plugins',
            'description'  => 'All plugins',
            'license'      => 'MIT',
            'config'       => [
                'vendor-dir' => '.',
            ],
            'repositories' => [],
            'require'      => [],
        ];
    }
}
