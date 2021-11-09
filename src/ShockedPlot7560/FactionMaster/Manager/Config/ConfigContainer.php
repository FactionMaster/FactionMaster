<?php

declare(strict_types=1);

namespace ShockedPlot7560\FactionMaster\Manager\Config;

use pocketmine\utils\Config;
use function in_array;

class ConfigContainer {
	const ALLOWED_PROVIDER = ["MYSQL", "SQLITE"];

	private $path;
	private $type;
	private $config;
	private $data;

	public function __construct(string $path, int $type = Config::YAML) {
		$this->path = $path;
		$this->type = $type;
	}

	public function getType(): int {
		return $this->type;
	}

	public function getPath(): string {
		return $this->path;
	}

	public function saveConfig(): void {
		$this->config = new Config($this->getPath(), $this->getType());
		$this->data = $this->getConfig()->getAll();
	}

	public function getData(): array {
		return $this->data;
	}

	public function getDatabaseData(): ?array {
		$databaseData = $this->getData()[$this->getProvider() . "_database"];
		if ($this->getProvider() === "MYSQL") {
			if (!isset($databaseData["name"]) || !isset($databaseData["host"])
					|| !isset($databaseData["user"]) || !isset($databaseData["pass"])) {
				throw new ConfigException("The data for the MYSQL provider does not meet the requirements.");
			}
			return [
				"provider" => $this->getProvider(),
				"name" => $databaseData["name"],
				"host" => $databaseData["host"],
				"user" => $databaseData["user"],
				"pass" => $databaseData["pass"]
			];
		} elseif ($this->getProvider() === "SQLITE") {
			return [
				"provider" => $this->getProvider(),
				"name" => $databaseData["name"],
			];
		} else {
			return null;
		}
	}

	public function getProvider(): string {
		if (in_array(($provider = $this->getData()["PROVIDER"]), self::ALLOWED_PROVIDER, true)) {
			return $provider;
		} else {
			throw new ConfigException("The given provider must contain a permitted value.");
		}
	}

	protected function getConfig(): Config {
		return $this->config;
	}
}