<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Binaries\Scm;

use Illuminate\Support\Arr;
use Rocketeer\Abstracts\AbstractBinary;
use Rocketeer\Interfaces\ScmInterface;

/**
 * The Mercury implementation of the ScmInterface
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Hg extends AbstractBinary implements ScmInterface
{
    /**
     * The core binary
     *
     * @type string
     */
    protected $binary = 'hg';

    /**
     * Check if the SCM is available
     *
     * @return string
     */
    public function check()
    {
        return $this->getCommand('--version');
    }

    /**
     * Get the current state
     *
     * @return string
     */
    public function currentState()
    {
        return $this->getCommand('identify -i');
    }

    /**
     * Get the current branch
     *
     * @return string
     */
    public function currentBranch()
    {
        return $this->getCommand('branch');
    }

    /**
     * Clone a repository
     *
     * @param string $destination
     *
     * @return string
     */
    public function checkout($destination)
    {
        $arguments = [
            $this->quote($this->credentials->getRepositoryEndpoint()),
            '-b '.$this->credentials->getRepositoryBranch(),
            $this->quote($destination),
        ];

        return $this->clone($arguments, $this->getCredentials());
    }

    /**
     * Resets the repository
     *
     * @return string
     */
    public function reset()
    {
        return $this->getCommand('update --clean');
    }

    /**
     * Updates the repository
     *
     * @return string
     */
    public function update()
    {
        return $this->pull();
    }

    /**
     * Checkout the repository's submodules
     *
     * @return string|null
     */
    public function submodules()
    {
        return;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the credentials required for cloning
     *
     * @return array
     */
    private function getCredentials()
    {
        $options = ['--config ui.interactive' => 'no', '--config auth.x.prefix' => 'http://'];

        $credentials = $this->credentials->getRepositoryCredentials();
        if ($user = Arr::get($credentials, 'username')) {
            $options['--config auth.x.username'] = $user;
        }
        if ($pass = Arr::get($credentials, 'password')) {
            $options['--config auth.x.password'] = $pass;
        }

        return $options;
    }
}