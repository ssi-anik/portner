<?php namespace Portner\Service;

class Storage
{
	private $data, $path;
	const SERVICES_KEY = 'services';
	const APPLICATION_KEY = 'applications';

	/*
	 * {
	 *     "services":
	 * 		[
	 * 			{
	 * 				"name": "",
	 * 				"port": "",
	 * 				"expose_at": "",
	 * 				"last_used_port": ""
	 * 			}
	 * 		],
	 *     "applications":
	 * 		[
	 * 			{
	 * 				"name": "",
	 * 				"services": [],
	 * 				"ports": []
	 * 			}
	 * 		]
	 * }
	 * */

	public function __construct ($path, $data) {
		$this->data = $data;
		$this->path = $path;
	}

	public function addService ($name, $port, $exposeAt) {
		$newService = [
			'name'      => $name,
			'port'      => $port,
			'expose_at' => $exposeAt,
		];
		$availableServices = $this->getServices();
		foreach ($availableServices as $service) {
			if ($service['name'] == $name) {
				throw new \Exception("Service '{$name}' already exists.");
			}

			if ($service['expose_at'] == $exposeAt) {
				throw new \Exception("Service '{$name}' start expose at port - {$exposeAt} is already assigned to '{$service['name']}'.");
			}
		}

		$availableServices[] = $newService;
		$this->writeToFile([
			self::SERVICES_KEY    => $availableServices,
			self::APPLICATION_KEY => $this->getApplications(),
		]);

		return $this;
	}

	public function updateLastUsedPortsOnServices ($services, $ports) {
		$availableServices = $this->getServices();
		foreach ($availableServices as $service) {
			if (in_array($service, $services)) {
				if (array_key_exists('last_used_port', $service)) {
					$index = array_search($service, $services);
					$service['last_used_port'] = $ports[$index];
				}
			}
		}

		$this->writeToFile([
			self::SERVICES_KEY    => $availableServices,
			self::APPLICATION_KEY => $this->getApplications(),
		]);
	}

	public function removeService ($name) {
		$availableServices = $this->getServices();
		$untouchedServices = [];
		foreach ($availableServices as $service) {
			if ($service['name'] != $name) {
				$untouchedServices[] = $service;
			}
		}

		if (count($untouchedServices) == count($availableServices)) {
			throw new \Exception("Service '{$name}' does not exist.");
		}

		$this->writeToFile([ self::SERVICES_KEY => $untouchedServices ]);
	}

	public function getServices () {
		return array_key_exists(self::SERVICES_KEY, $this->data) ? $this->data[self::SERVICES_KEY] : [];
	}

	public function getApplications () {
		return array_key_exists(self::APPLICATION_KEY, $this->data) ? $this->data[self::APPLICATION_KEY] : [];
	}

	public function saveNewApplication ($name, $services, $ports) {
		$newApplication = [
			'name'     => $name,
			'services' => $services,
			'ports'    => $ports,
		];

		$availableApplications = $this->getApplications();
		$availableApplications[] = $newApplication;
		$this->writeToFile([
			self::SERVICES_KEY    => $this->getServices(),
			self::APPLICATION_KEY => $availableApplications,
		]);

		return true;
	}

	public function checkIfApplicationNameExists ($name) {
		foreach ($this->getApplications() as $application) {
			if ($name == $application['name']) {
				return true;
			}
		}

		return false;
	}

	public function checkIfPortUsedInApplication ($service, $port) {
		foreach ($this->getApplications() as $application) {
			$services = $application['services'];
			$ports = $application['ports'];
			if (!in_array($service, $services)) {
				continue;
			}

			$index = array_search($service, $services);
			if ($ports[$index] == $port) {
				return true;
			}
		}

		return false;
	}

	public function checkIfServiceExists ($providedServices) {
		if (!is_array($providedServices)) {
			$providedServices = (array) $providedServices;
		}

		$availableServiceNames = array_map(function ($service) {
			return $service['name'];
		}, $this->getServices());
		$differences = array_diff($providedServices, $availableServiceNames);

		return empty($differences) ? true : $differences;
	}

	protected function getData () {
		return $this->data;
	}

	private function writeToFile ($data) {
		$file = fopen($this->path, "w+");
		$previousData = $this->getData();
		$newData = array_merge($previousData, $data);
		fwrite($file, json_encode($newData));
		fclose($file);
	}
}