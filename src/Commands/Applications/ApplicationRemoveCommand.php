<?php namespace Portner\Commands\Applications;

use Portner\Service\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class ApplicationRemoveCommand extends Command
{
	private $storage = null;

	public function __construct (Storage $storage) {
		parent::__construct();
		$this->storage = $storage;
	}

	protected function configure () {
		$this->setName("application:remove")->setDescription("Remove an application")->setDefinition([
			new InputOption('name', null, InputOption::VALUE_REQUIRED),
		]);
	}

	protected function execute (InputInterface $input, OutputInterface $output) {
		$io = new SymfonyStyle($input, $output);

		$inputNames = $input->getOption('name');
		if (!$inputNames || empty($inputNames)) {
			$availableApplicationNames = array_map(function ($application) {
				return $application['name'];
			}, $this->storage->getApplications());

			if (empty($availableApplicationNames)) {
				$io->error("You don't have any application.");

				return;
			}

			$names = array_unique($this->askUserForApplicationsToRemove($input, $output, $availableApplicationNames));
		} else {
			$names = array_filter(array_map('trim', explode(",", $inputNames)));
		}

		if (!$names) {
			$io->getErrorStyle()->error("Name is required to remove a service.");

			return;
		}

		try {
			$this->storage->removeApplication($names);
			$names = implode(", ", $names);
			$io->success("Service '{$names}' removed successfully.");
		} catch (\Exception $exception) {
			$io->getErrorStyle()->error($exception->getMessage());

			return;
		}

		return;
	}

	private function askUserForApplicationsToRemove (InputInterface $input, OutputInterface $output, $services) {
		$helper = $this->getHelper('question');
		$question = new ChoiceQuestion('Which applications you want to remove? (separate by comma)', $services);
		$question->setMultiselect(true);

		return $helper->ask($input, $output, $question);
	}
}