<?php

/***
 *    ___                                          _
 *   / __\___  _ __ ___  _ __ ___   __ _ _ __   __| | ___
 *  / /  / _ \| '_ ` _ \| '_ ` _ \ / _` | '_ \ / _` |/ _ \
 * / /__| (_) | | | | | | | | | | | (_| | | | | (_| | (_) |
 * \____/\___/|_| |_| |_|_| |_| |_|\__,_|_| |_|\__,_|\___/
 *
 * Commando - A Command Framework virion for PocketMine-MP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Written by @CortexPE <https://CortexPE.xyz>
 *
 */


namespace ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\constraint;

use pocketmine\command\CommandSender;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\IRunnable;

abstract class BaseConstraint {
	/** @var IRunnable */
	protected $context;

	/**
	 * BaseConstraint constructor.
	 *
	 * "Context" is required so that this new-constraint-system doesn't hinder getting command info
	 */
	public function __construct(IRunnable $context) {
		$this->context = $context;
	}

	public function getContext(): IRunnable {
		return $this->context;
	}

	abstract public function test(CommandSender $sender, string $aliasUsed, array $args): bool;

	abstract public function onFailure(CommandSender $sender, string $aliasUsed, array $args): void;

	abstract public function isVisibleTo(CommandSender $sender): bool;
}