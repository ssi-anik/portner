<?php namespace Portner\Commands;

use Portner\Service\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ServiceRemoveCommand extends Command
{
	private $storage = null;

	public function __construct (Storage $storage) {
		parent::__construct();
		$this->storage = $storage;
	}

	protected function configure () {
		$this->setName("service:remove")->setDefinition([
			new InputOption('name', null, InputOption::VALUE_REQUIRED),
		])->setDescription("Remove a service.");
	}

	protected function execute (InputInterface $input, OutputInterface $output) {
		$io = new SymfonyStyle($input, $output);

		$name = trim($input->getOption('name'));

		if (!$name) {
			$io->getErrorStyle()->error("Name is required to remove a service.");

			return;
		}

		try {
			$this->storage->removeService($name);
			$io->success("Service '{$name}' removed successfully.");
		} catch (\Exception $exception) {
			$io->getErrorStyle()->error($exception->getMessage());
		}
	}
}