<?php
namespace Rocketeer\Tasks;

use DateTime;
use Rocketeer\Traits\Task;

/**
 * Rollback to the previous release, or to a specific one
 */
class Rollback extends Task
{
	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Get previous release
		$rollbackRelease = $this->getRollbackRelease();
		if (!$rollbackRelease) {
			$this->command->error('Rocketeer could not rollback as no releases have yet been deployed');
		}

		// If no release specified, display the available ones
		if ($this->command->option('list')) {
			$releases = $this->releasesManager->getReleases();
			$this->command->info('Here are the available releases :');

			foreach ($releases as $key => $name) {
				$name = DateTime::createFromFormat('YmdHis', $name);
				$name = $name->format('Y-m-d H:i:s');

				$this->command->comment(sprintf('[%d] %s', $key, $name));
			}

			// Get actual release name from date
			$rollbackRelease = $this->command->ask('Which one do you want to go back to ? (0)', 0);
			$rollbackRelease = $releases[$rollbackRelease];
		}

		// Rollback release
		$this->command->info('Rolling back to release '.$rollbackRelease);
		$this->updateSymlink($rollbackRelease);

		return $this->history;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the release to rollback to
	 *
	 * @return integer
	 */
	protected function getRollbackRelease()
	{
		$release = array_get($this->command->argument(), 'release');
		if (!$release) {
			$release = $this->releasesManager->getPreviousRelease();
		}

		return $release;
	}
}
