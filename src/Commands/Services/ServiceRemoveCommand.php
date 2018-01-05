<?php namespace Portner\Commands\Services;

use Portner\Service\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class ServiceRemoveCommand extends Command
{
	private $storage = null;

	public function __construct (Storage $storage) {
		parent::__construct();
		$this->storage = $storage;
	}

	protected function configure () {
		$this->setName("service:remove")->setAliases([ 'sr' ])->setDefinition([
			new InputOption('name', null, InputOption::VALUE_REQUIRED),
		])->setDescription("Remove a service.");
	}

	protected function execute (InputInterface $input, OutputInterface $output) {
		$io = new SymfonyStyle($input, $output);
		$inputNames = $input->getOption('name');
		if (!$inputNames || empty($inputNames)) {
			$availableServiceNames = array_map(function ($service) {
				return $service['name'];
			}, $this->storage->getServices());

			if (empty($availableServiceNames)) {
				$io->error("You don't have any service.");

				return;
			}

			$names = array_unique($this->askUserForServicesToRemove($input, $output, $availableServiceNames));
		} else {
			$names = array_filter(array_map('trim', explode(",", $inputNames)));
		}

		if (!$names) {
			$io->getErrorStyle()->error("Name is required to remove a service.");

			return;
		}

		try {
			$this->storage->removeService($names);
			$names = implode(", ", $names);
			$io->success("Service '{$names}' removed successfully.");
		} catch (\Exception $exception) {
			$io->getErrorStyle()->error($exception->getMessage());

			return;
		}

		return;
	}

	private function askUserForServicesToRemove (InputInterface $input, OutputInterface $output, $services) {
		$helper = $this->getHelper('question');
		$question = new ChoiceQuestion('Which services you want to remove? (separate by comma)', $services);
		$question->setMultiselect(true);

		return $helper->ask($input, $output, $question);
	}
}