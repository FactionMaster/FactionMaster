<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster
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
 * @author ShockedPlot7560
 * @link https://github.com/ShockedPlot7560
 *
 *
 */

namespace ShockedPlot7560\FactionMaster\Task;

use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function call_user_func;

class MenuSendTask extends Task {
	private $condition;
	private $onSuccess;
	private $onTimeOut;
	private $timeOut;
	private $tick = 0;

	public function __construct(callable $condition, callable $onSuccess, callable $onTimeOut) {
		$this->condition = $condition;
		$this->onSuccess = $onSuccess;
		$this->onTimeOut = $onTimeOut;
		$this->timeOut = (int) Utils::getConfig("timeout-task");
	}

	public function onRun(): void {
		$result = call_user_func($this->condition);
		if ($result === true && $this->tick < $this->timeOut) {
			call_user_func($this->onSuccess);
			throw new CancelTaskException();
		} elseif ($this->tick >= $this->timeOut) {
			call_user_func($this->onTimeOut);
			throw new CancelTaskException();
		}
		$this->tick++;
	}
}