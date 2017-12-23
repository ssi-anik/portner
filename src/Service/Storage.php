<?php namespace Portner\Service;

class Storage
{
	private $data, $path;
	const SERVICES_KEY = 'services';
	const APPLICATION_KEY = 'applications';

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
				throw new \Exception("Service '{$name}' expose at port - {$exposeAt} is already assigned to '{$service['name']}'.");
			}
		}

		$availableServices[] = $newService;
		$this->writeToFile([ self::SERVICES_KEY => $availableServices ]);

		return $this;
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
		return $this->data[self::SERVICES_KEY];
	}

	public function getApplications () {
		return $this->data[self::APPLICATION_KEY];
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