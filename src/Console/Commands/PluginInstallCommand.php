<?php

namespace Dan\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginInstallCommand extends Command
{
    protected function configure()
    {
        $this->setName('plugin:install')
            ->setDescription('Installs the given plugin')
            ->addArgument('repo', InputArgument::REQUIRED, 'Git repo')
            ->addArgument('branch', InputArgument::OPTIONAL, 'Git branch', 'dev-master');
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
        $composer = ROOT_DIR.'/plugins/composer.json';

        if (!file_exists($composer)) {
            file_put_contents($composer, json_encode($this->composerJson(), JSON_PRETTY_PRINT));
        }

        $repo = $input->getArgument('repo');

        if (strpos($repo, 'git@') === false) {
            $repo = "git@github.com:{$repo}.git";
        }

        $name = rtrim(last(explode(':', $repo)), '.git');
        $json = json_decode(file_get_contents($composer), true);

        foreach ($json['repositories'] as $repository) {
            if ($repository['url'] == $repo) {
                throw new \Exception('This plugin is already installed');
            }
        }

        $json['repositories'][] = [
            'type'  => 'vcs',
            'url'   => $repo,
        ];
        $json['require'][$name] = $input->getArgument('branch');

        file_put_contents($composer, json_encode($json, JSON_PRETTY_PRINT));

        shell_exec('cd '.ROOT_DIR.'/plugins && composer install');
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
