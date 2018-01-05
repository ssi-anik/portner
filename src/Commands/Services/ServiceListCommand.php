<?php namespace Portner\Commands\Services;

use Portner\Service\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
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
		$this->setName('service:list')->setAliases([ 'sl' ])->setDescription("Get the list of services.");
	}

	protected function execute (InputInterface $input, OutputInterface $output) {
		$io = new SymfonyStyle($input, $output);
		if (!($services = $this->storage->getServices())) {
			$io->warning("No service is added yet.");

			return;
		}

		$rows = [];
		foreach ($services as $service) {
			$rows[] = [
				'name'           => $service['name'],
				'port'           => $service['port'],
				'expose_at'      => $service['expose_at'],
				'last_used_port' => array_key_exists('last_used_port', $service) ? $service['last_used_port'] : '',
			];
			if (next($services)) {
				$rows[] = new TableSeparator();
			}
		}

		(new Table($output))->setHeaders([ 'Service Name', 'Actual Port', 'Host port expose at', 'Last used port' ])
							->setRows($rows)
							->render();
	}
}