<?php namespace Portner\Commands;

use Portner\Service\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
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
		$this->setName("application:list")->setDescription("Get the list of applications.");
	}

	protected function execute (InputInterface $input, OutputInterface $output) {
		$io = new SymfonyStyle($input, $output);
		$applications = $this->storage->getApplications();
		if (!$applications) {
			$io->warning("No application is saved yet.");

			return;
		}

		$table = new Table($output);
		$table->setHeaders([ "Application name", "Services", "Ports used" ])->setRows(array_map(function ($row) {
			return [
				'name'     => $row['name'],
				'services' => implode("\n", $row['services']),
				'ports'    => implode("\n", $row['ports']),
			];
		}, $applications));
	}
}