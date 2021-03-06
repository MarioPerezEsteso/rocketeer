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

namespace Rocketeer\Services\Connections\Shell\Modules;

use Mockery\MockInterface;
use Rocketeer\Binaries\Php;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\TestCases\RocketeerTestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

class BinariesTest extends RocketeerTestCase
{
    public function testCanSetCustomPathsForBinaries()
    {
        $binary = __DIR__.'/../../../bin/rocketeer';
        $this->mockConfig(['paths.composer' => $binary]);

        $this->assertEquals($binary, $this->task->which('composer'));
    }

    public function testConsidersAllPossibleWhichOutputs()
    {
        $this->mock(Bash::class, 'Bash', function ($mockery) {
            return $mockery
                ->shouldReceive('runSilently')->with('which foobar')->andReturn('foobar not found')
                ->shouldReceive('runSilently')->with('which npm')->andReturn('which: no npm in (/usr/local/bin:/bin:/usr/bin)');
        });

        $this->assertEquals(false, $this->bash->rawWhich('foobar'));
        $this->assertEquals(false, $this->bash->rawWhich('npm'));
    }

    public function testStoredPathsAreInvalidatedIfIncorrect()
    {
        $this->mockRemote([
            'which foobar' => null,
            'which composer' => 'composer',
        ]);

        $this->localStorage->set('paths.production.composer', 'foobar');

        $this->assertEquals('composer', $this->task->which('composer'));
        $this->assertEquals('composer', $this->localStorage->get('paths.production.composer'));
    }

    public function testPathsAreScopedToConnection()
    {
        $this->mockRemote([
            'which production' => 'production',
            'which staging' => 'staging',
        ]);

        $this->localStorage->set('paths.production.composer', 'production');
        $this->localStorage->set('paths.staging.composer', 'staging');

        $this->assertEquals('production', $this->task->which('composer'));
        $this->connections->setCurrentConnection('staging');
        $this->assertEquals('staging', $this->task->which('composer'));
    }

    public function testCanSetPathToPhpAndArtisan()
    {
        $this->usesLaravel(true);

        $this->mockConfig([
            'paths.php' => static::$binaries['php'],
            'paths.artisan' => static::$binaries['php'],
        ]);

        $this->assertEquals(static::$binaries['php'].' '.static::$binaries['php'].' migrate --force',
            $this->task->artisan()->migrate());
    }

    public function testAlwaysRespectsCustomPath()
    {
        $this->mockConfig([
            'paths.php' => 'foo',
            'paths.composer' => 'php /some/composer.phar',
        ]);

        $this->assertEquals('foo', $this->bash->php()->getBinary());
        $this->assertEquals('php /some/composer.phar', $this->bash->composer()->getBinary());
    }

    public function testFetchesBinaryIfNotSpecifiedOrNull()
    {
        $this->usesLaravel(true);

        $this->mockConfig([
            'paths.php' => static::$binaries['php'],
        ]);

        $this->assertEquals(static::$binaries['php'].' artisan migrate --force', $this->task->artisan()->migrate());
    }

    public function testCanGetBinary()
    {
        $whichGrep = exec('which grep');
        $grep = $this->task->which('grep');

        $this->assertEquals($whichGrep, $grep);
    }

    public function testCanRunComposer()
    {
        $this->usesComposer();
        $this->mock('rocketeer.command', AbstractCommand::class, function (MockInterface $mock) {
            return $mock
                ->shouldIgnoreMissing()
                ->shouldReceive('getOutput')->andReturn(new ConsoleOutput())
                ->shouldReceive('line')
                ->shouldReceive('option')->andReturn([]);
        });

        $this->pretendTask('Dependencies')->execute();

        $this->assertCount(2, $this->history->getFlattenedHistory()[0]);
    }

    public function testDoesntRunComposerIfNotNeeded()
    {
        $this->usesComposer(false);
        $this->mock('rocketeer.command', AbstractCommand::class, function (MockInterface $mock) {
            return $mock
                ->shouldIgnoreMissing()
                ->shouldReceive('getOutput')->andReturn(new ConsoleOutput())
                ->shouldReceive('line')
                ->shouldReceive('option')->andReturn([]);
        });

        $this->pretendTask('Dependencies')->execute();
        $this->assertEmpty($this->history->getFlattenedHistory());
    }

    public function testCanGetBinariesViaMagicMethods()
    {
        $binary = $this->bash->php();
        $this->assertInstanceOf(Php::class, $binary);
    }

    public function testCanRunBinariesMethodsViaMagicMethods()
    {
        $this->mockRemote([
            'which composer' => 'composer',
            'composer --help' => 'Usage: foobar',
        ]);

        $results = $this->bash->composer('--help');

        $this->assertContains('Usage:', $results);
    }
}
