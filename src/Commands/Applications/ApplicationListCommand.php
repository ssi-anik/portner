<?php namespace Portner\Commands\Applications;

use Portner\Service\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ApplicationListCommand extends Command
{
	private $storage = null;

	public function __construct (Storage $storage) {
		parent::__construct();
		$this->storage = $storage;
	}

	protected function configure () {
		$this->setName("application:list")->setAliases([ 'al' ])->setDescription("Get the list of applications.");
	}

	protected function execute (InputInterface $input, OutputInterface $output) {
		$io = new SymfonyStyle($input, $output);
		$applications = $this->storage->getApplications();
		if (!$applications) {
			$io->warning("No application is saved yet.");

			return;
		}

		$rows = [];

		foreach ($applications as $application) {
			$rows[] = [
				'name'     => $application['name'],
				'services' => implode("\n", $application['services']),
				'ports'    => implode("\n", $application['ports']),
			];

			if (next($applications)) {
				$rows[] = new TableSeparator();
			}
		}

		(new Table($output))->setHeaders([ "Application name", "Services", "Ports used" ])->setRows($rows)->render();
	}
}