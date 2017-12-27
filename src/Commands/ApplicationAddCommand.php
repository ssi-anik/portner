<?php namespace Portner\Commands;

use Portner\Service\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class ApplicationAddCommand extends Command
{
	private $storage = null;

	public function __construct (Storage $storage) {
		parent::__construct();
		$this->storage = $storage;
	}

	protected function configure () {
		$this->setName("application:add")->setDescription("Add an application")->setDefinition([
			new InputOption('name', null, InputOption::VALUE_REQUIRED),
			new InputOption('services', null, InputOption::VALUE_REQUIRED),
		]);
	}

	protected function execute (InputInterface $input, OutputInterface $output) {
		$io = new SymfonyStyle($input, $output);

		$name = trim($input->getOption('name'));
		if (empty($name)) {
			$io->error("Application name is must to add an application.");

			return;
		}

		if ($this->storage->checkIfApplicationNameExists($name)) {
			$io->error("Application name already exists.");

			return;
		}

		$availableServices = $this->storage->getServices();
		$userInputServices = $input->getOption('services');

		if (empty($userInputServices)) {
			$availableServiceNames = array_map(function ($service) {
				return $service['name'];
			}, $availableServices);

			$userProvidedServices = $this->askForServicesToUse($input, $output, $availableServiceNames);
		} else {
			$userProvidedServices = array_map(function ($service) {
				return trim($service);
			}, explode(",", $userInputServices));
		}
		$doesExist = $this->storage->checkIfServiceExists($userProvidedServices);

		if (is_array($doesExist)) {
			$io->error(sprintf("Services '%s' not available in list", implode(", ", $doesExist)));

			return;
		}

		$calculatedPortsWithServices = $this->calculatePorts($availableServices, $userProvidedServices);

		$suggestedServices = array_map(function ($row) {
			return $row['service'];
		}, $calculatedPortsWithServices);

		$suggestedPorts = array_map(function ($row) {
			return $row['port'];
		}, $calculatedPortsWithServices);

		$answers = $this->confirmSuggestion($input, $output, $io, $suggestedServices, $suggestedPorts);
		$finalServices = array_keys($answers);
		$finalPorts = array_values($answers);
		$table = new Table($output);
		$table->setHeaders([ 'Services', 'Ports' ])->setRows([
			[
				implode("\n", $finalServices),
				implode("\n", $finalPorts),
			],
		])->render();

		if (!$this->confirmBeforeSave($input, $output)) {
			$io->warning("Application not saved!");

			return;
		}

		$isStored = $this->storage->saveNewApplication($name, $finalServices, $finalPorts);
		if ($isStored) {
			$io->success("Application '{$name}' added successfully.");
		}
//		$this->storage->updateLastUsedPortsOnServices($finalServices, $finalPorts);
	}

	private function confirmBeforeSave (InputInterface $input, OutputInterface $output) {
		$helper = $this->getHelper('question');
		$question = new ConfirmationQuestion("Do you want to save application? [Y/n]: ", true);

		return $helper->ask($input, $output, $question);
	}

	private function confirmSuggestion (InputInterface $input, OutputInterface $output, SymfonyStyle $io, $suggestedServices, $suggestedPorts) {
		$helper = $this->getHelper('question');
		$confirmed = [];
		foreach ($suggestedServices as $index => $service) {
			do {
				$question = new Question("Service '{$service}' will use port - {$suggestedPorts[$index]}: ", $suggestedPorts[$index]);
				$confirmedPort = $helper->ask($input, $output, $question);
				$isConfirmed = is_numeric($confirmedPort);
				if (!$isConfirmed) {
					$io->error("Invalid port - {$confirmedPort}.");
				}

				if ($confirmedPort > 65535) {
					$isConfirmed = false;
					$io->error("Port is greater than 65535");
				}

			} while (!$isConfirmed);

			$confirmed[$service] = $confirmedPort;
		}

		return $confirmed;
	}

	private function calculatePorts ($availableServices, $userProvidedServices) {
		$suggestion = [];
		foreach ($userProvidedServices as $userService) {
			$collection = collect($availableServices);
			if ($predefinedService = $collection->where('name', $userService)->first()) {
				do {
					if (array_key_exists('last_used_port', $predefinedService)) {
						$availablePort = $predefinedService['last_used_port'] + 1;
					} else {
						$availablePort = $predefinedService['expose_at'];
					}
				} while ($this->storage->checkIfPortUsedInApplication($userService, $availablePort));

				$suggestion[] = [ 'service' => $userService, 'port' => $availablePort ];
			}
		}

		return $suggestion;
	}

	private function askForServicesToUse (InputInterface $input, OutputInterface $output, $services) {
		$helper = $this->getHelper('question');
		$question = new ChoiceQuestion('Which services you want to use? (separate by comma)', $services);
		$question->setMultiselect(true);

		return $helper->ask($input, $output, $question);
	}
}