<?php namespace Portner\Commands;

use Portner\Service\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ApplicationSuggestCommand extends Command
{
	public $storage = null;

	public function __construct (Storage $storage) {
		parent::__construct();
		$this->storage = $storage;
	}

	protected function configure () {
		$this->setName("application:suggestion")
			 ->setDescription("Suggest the ports")
			 ->addOption('services', null, InputOption::VALUE_REQUIRED);
	}

	protected function execute (InputInterface $input, OutputInterface $output) {
		$io = new SymfonyStyle($input, $output);

		$services = explode(",", trim($input->getOption('services')));
		if (empty($services)) {
			$io->getErrorStyle()->error("For suggestion you need to add service names.");

			return;
		}

		var_dump($services);
	}
}