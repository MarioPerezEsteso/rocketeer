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

namespace Rocketeer\TestCases\Modules;

use Rocketeer\Console\Commands\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Building
{
    /**
     * Get and execute a command.
     *
     * @param Command|string|null $command
     * @param array               $arguments
     * @param array               $options
     *
     * @return CommandTester
     */
    protected function executeCommand($command = null, $arguments = [], $options = [])
    {
        $command = $this->command($command);

        // Execute
        $tester = new CommandTester($command);
        $tester->execute($arguments, $options + ['interactive' => false]);

        return $tester;
    }

    /**
     * Get a pretend AbstractTask to run bogus commands.
     *
     * @param string $task
     * @param array  $options
     * @param array  $expectations
     *
     * @return \Rocketeer\Tasks\AbstractTask
     */
    protected function pretendTask($task = 'Deploy', $options = [], array $expectations = [])
    {
        $this->pretend($options, $expectations);

        return $this->task($task);
    }

    /**
     * Get AbstractTask instance.
     *
     * @param string $task
     * @param array  $options
     *
     * @return \Rocketeer\Tasks\AbstractTask
     */
    protected function task($task = null, $options = [])
    {
        if ($options) {
            $this->mockCommand($options);
        }

        if (!$task) {
            return $this->task;
        }

        return $this->builder->buildTask($task);
    }

    /**
     * Get a command instance.
     *
     * @param string|Command $command
     *
     * @return Command
     */
    protected function command($command)
    {
        // Fetch command from Container if necessary
        if (!$command instanceof AbstractCommand) {
            $command = $command ? $command : null;
            $command = $this->console->get($command);
        } elseif (!$command->getContainer()) {
            $command->setContainer($this->container);
            $command->setHelperSet(new HelperSet(['question' => new QuestionHelper()]));
        }

        return $command;
    }
}
