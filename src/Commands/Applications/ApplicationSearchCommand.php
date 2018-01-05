<?php namespace Portner\Commands\Applications;

use Portner\Service\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ApplicationSearchCommand extends Command
{
	private $storage = null;

	public function __construct (Storage $storage) {
		parent::__construct();
		$this->storage = $storage;
	}

	protected function configure () {
		$this->setName("application:search")
			 ->setAliases([ 'as' ])
			 ->setDescription("Search within applications by name or port or service")
			 ->setDefinition([
				 new InputOption('name', null, InputOption::VALUE_REQUIRED),
				 new InputOption('service', null, InputOption::VALUE_REQUIRED),
				 new InputOption('port', null, InputOption::VALUE_REQUIRED),
			 ]);
	}

	protected function execute (InputInterface $input, OutputInterface $output) {
		$io = new SymfonyStyle($input, $output);

		if ($name = trim($input->getOption('name'))) {
			$applications = $this->storage->checkIfApplicationExistsWith("name", $name);
			if ($applications) {
				$this->showTable($output, $this->processApplicationData($applications));
			} else {
				$io->warning("No application found with names.");
			}
		} elseif ($service = trim($input->getOption('service'))) {
			$applications = $this->storage->checkIfApplicationExistsWith("services", $service);
			if ($applications) {
				$this->showTable($output, $this->processApplicationData($applications));
			} else {
				$io->warning("No application found with services.");
			}
		} elseif ($port = trim($input->getOption('port'))) {
			$applications = $this->storage->checkIfApplicationExistsWith("ports", $port);
			if ($applications) {
				$this->showTable($output, $this->processApplicationData($applications));
			} else {
				$io->warning("No application found with port.");
			}
		} else {
			$io->error("Must have to specify any of NAME, SERVICE, PORT.");

			return;
		}

	}

	private function processApplicationData ($applications) {
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

		return $rows;
	}

	private function showTable (OutputInterface $output, $rows) {
		(new Table($output))->setHeaders([ 'Name', 'Services', 'Ports' ])->setRows($rows)->render();
	}
}