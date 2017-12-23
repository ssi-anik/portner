<?php namespace Portner\Commands;

use Portner\Service\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ServiceListCommand extends Command
{
	private $storage;

	public function __construct (Storage $storage) {
		parent::__construct();
		$this->storage = $storage;
	}

	protected function configure () {
		$this->setName('service:list')->setDescription("Get the list of services.");
	}

	protected function execute (InputInterface $input, OutputInterface $output) {
		$io = new SymfonyStyle($input, $output);
		if (!($services = $this->storage->getServices())) {
			$io->warning("No service is added yet.");

			return;
		}

		$table = new Table($output);
		$table->setHeaders([ 'Service Name', 'Actual Port', 'Start host port at' ]);
		$table->setRows($services);
		$table->render();
	}
}