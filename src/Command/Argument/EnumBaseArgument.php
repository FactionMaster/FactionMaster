<?php

namespace ShockedPlot7560\FactionMaster\Command\Argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\args\BaseArgument;

abstract class EnumBaseArgument extends BaseArgument {
	/** @var string */
	protected $name;
	/** @var array */
	protected $enumValues;
	/** @var CommandParameter */
	protected $parameterData;

	public function __construct(string $name, array $enumValues, bool $optionnal = false) {
		$this->name = $name;
		$this->enumValues = $enumValues;
		$this->optionnal = $optionnal;

		$this->parameterData = new CommandParameter();
		$this->parameterData->paramName = $name;
		$this->parameterData->flags = 1;
		$this->parameterData->paramType = AvailableCommandsPacket::ARG_FLAG_ENUM | AvailableCommandsPacket::ARG_FLAG_VALID;
		$this->parameterData->enum = new CommandEnum($name, $enumValues);
		$this->parameterData->isOptional = $optionnal;
	}

	abstract public function canParse(string $testString, CommandSender $sender): bool;

	/**
	 * @return mixed
	 */
	abstract public function parse(string $argument, CommandSender $sender);

	public function getName(): string {
		return $this->name;
	}

	/**
	 * Returns how much command arguments
	 * it takes to build the full argument
	 */
	public function getSpanLength(): int {
		return 1;
	}

	abstract public function getTypeName(): string;

	public function getNetworkParameterData():CommandParameter {
		return $this->parameterData;
	}
}
