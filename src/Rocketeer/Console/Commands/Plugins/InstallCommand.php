<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Console\Commands\Plugins;

use Rocketeer\Console\Commands\AbstractPluginCommand;

class InstallCommand extends AbstractPluginCommand
{
    /**
     * @var string
     */
    protected $pluginTask = 'Installer';

    /**
     * The default name.
     *
     * @var string
     */
    protected $name = 'plugin:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install a plugin';
}
