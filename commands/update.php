<?php

use Dan\Core\Dan;
use Dan\Helpers\Hooks;
use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Location $location */
/** @var string $message */
/** @var string $entry */

if($entry == 'use' || $entry == 'console')
{
    $report = function($location) {
        $v = Dan::getCurrentGitVersion();
        message($location, "{reset}[ {green}Up to date {reset}| Currently on {yellow}{$v['id']}{reset} | {cyan}{$v['message']} {reset}]");
    };

    if(!PHAR)
    {
        $shell = shell_exec(sprintf("cd %s && git pull origin dan4", ROOT_DIR));

        if($shell == null)
        {
            message($location, "Unable to update. Please use git clone to update the bot.");
            return;
        }

        if(strpos($shell, "up-to-date"))
        {
            $report($location);
        }
        else
        {
            message($location, "Update found!");

            if(strpos($shell, 'src/'))
            {
                if(!function_exists('pcntl_exec'))
                {
                    message($location, "Core files have been changed, but was unable to restart. PHP needs to be compiled with --enable-pcntl for automatic restarts.");
                    return;
                }

                message($location, "Core files changed, restarting bot.");

                Dan::quit("Updating bot.");
                pcntl_exec(ROOT_DIR . '/dan', ["--location={$location}", '--from=update']);
                return;
            }

            if(strpos($shell, 'hooks/'))
            {
                message($location, "Hooks changed, reloading.");
                Hooks::registerHooks();
            }

            $report($location);
        }
    }
    else
    {
        message($location, "PHAR updates currently need to be done manually.");
    }
}

if($entry == 'help')
{
    return [
        "Updates the bot"
    ];
}