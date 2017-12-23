<?php namespace Portner\Commands;

use Portner\Service\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ServiceAddCommand extends Command
{
	private $storage = null;

	public function __construct (Storage $storage) {
		parent::__construct();
		$this->storage = $storage;
	}

	protected function configure () {
		$this->setName("service:add")->setDefinition([
			new InputOption('name', null, InputOption::VALUE_REQUIRED),
			new InputOption('port', null, InputOption::VALUE_REQUIRED),
			new InputOption('expose-at', null, InputOption::VALUE_OPTIONAL),
		])->setDescription("Add a new service.");
	}

	protected function execute (InputInterface $input, OutputInterface $output) {
		$io = new SymfonyStyle($input, $output);
		$name = trim($input->getOption('name'));
		$port = intval($input->getOption('port'));
		if (!$port) {
			$io->getErrorStyle()->error("Port must be present to add a service.");

			return;
		}

		$exposeStartAt = intval($input->getOption('expose-at')) ?: $port;
		try {
			$this->storage->addService($name, $port, $exposeStartAt);
			$io->success("Service '{$name}' added successfully.");
		} catch (\Exception $exception) {
			$io->getErrorStyle()->error($exception->getMessage());
		}
	}
}