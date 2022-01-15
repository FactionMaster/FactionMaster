<?php

namespace ShockedPlot7560\FactionMaster\Command\Argument;

use pocketmine\command\CommandSender;

use function in_array;

class EnumArgument extends EnumBaseArgument {
	public function getTypeName(): string {
		return "enum";
	}

	public function canParse(string $testString, CommandSender $sender): bool {
		return in_array($testString, $this->enumValues, true);
	}

	public function getNetworkType(): int {
		//NOOP
		return 0;
	}

	public function parse(string $argument, CommandSender $sender) {
		return $argument;
	}
}