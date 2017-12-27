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
		$availableServices = collect($this->getServices());

		if ($availableServices->where('name', $name)->first()) {
			throw new \Exception("Service '{$name}' already exists.");
		}

		if ($service = $availableServices->where('expose_at', $exposeAt)->first()) {
			throw new \Exception("Service '{$name}' start expose at port - {$exposeAt} is already assigned to '{$service['name']}'.");
		}

		$newServicesArray = $availableServices->push($newService)->toArray();

		$this->writeToFile([ self::SERVICES_KEY => $newServicesArray, ]);

		return $this;
	}

	public function updateLastUsedPortsOnServices ($services, $ports) {
		$availableServices = $this->getServices();
		$mergedServices = [];
		foreach ($availableServices as $availableService) {
			if (in_array($availableService['name'], $services)) {
				$index = array_search($availableService['name'], $services);
				$availableService['last_used_port'] = $ports[$index];
			}

			$mergedServices[] = $availableService;
		}
		$this->writeToFile([ self::SERVICES_KEY => $mergedServices, ]);
	}

	public function removeService ($names) {
		$availableServices = collect($this->getServices());

		$mismatchedServices = collect($names)
			->diff($availableServices->whereIn('name', $names)->pluck('name'))
			->implode(", ");

		if ($mismatchedServices) {
			throw new \Exception("Service '{$mismatchedServices}' is not available.");
		}

		$filteredServices = $availableServices->whereNotIn('name', $names);

		$this->writeToFile([ self::SERVICES_KEY => $filteredServices->all() ]);
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
		$this->writeToFile([ self::APPLICATION_KEY => $availableApplications, ]);

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

	public function checkIfApplicationExistsWith ($key, $value) {
		$availableApplications = $this->getApplications();
		$foundApplications = [];
		foreach ($availableApplications as $application) {
			$innerValue = $application[$key];
			if (is_array($innerValue) && in_array($value, $innerValue)) {
				$foundApplications[] = $application;
			} elseif (is_string($innerValue) && strpos($innerValue, $value) !== false) {
				$foundApplications[] = $application;
			}
		}

		return $foundApplications;
	}

	public function removeApplication ($names) {
		$availableApplications = collect($this->getApplications());

		$mismatchedServices = collect($names)
			->diff($availableApplications->whereIn('name', $names)->pluck('name'))
			->implode(", ");

		if ($mismatchedServices) {
			throw new \Exception("Application '{$mismatchedServices}' is not available.");
		}

		$filteredApplications = $availableApplications->whereNotIn('name', $names);

		$this->writeToFile([ self::APPLICATION_KEY => $filteredApplications->all() ]);
	}

	protected function getData () {
		return $this->data;
	}

	private function writeToFile ($data) {
		$file = fopen($this->path, "w+");
		$previousData = $this->getData();
		$newData = [];
		foreach ($previousData as $key => $value) {
			if (array_key_exists($key, $data)) {
				$newData[$key] = $data[$key];
			} else {
				$newData[$key] = $value;
			}
		}
		$this->data = $newData;
		fwrite($file, json_encode($newData));
		fclose($file);
	}
}