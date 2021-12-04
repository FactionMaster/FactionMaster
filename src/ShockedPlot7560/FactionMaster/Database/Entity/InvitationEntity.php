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

namespace ShockedPlot7560\FactionMaster\Database\Entity;

use DateTime;

class InvitationEntity extends EntityDatabase {
	public const MEMBER_INVITATION = "member";
	public const ALLIANCE_INVITATION = "alliance";

	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getSenderString()
	 * @var string
	 */
	public $sender;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getReceiverString()
	 * @var string
	 */
	public $receiver;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getType()
	 * @var string
	 */
	public $type;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getDate(), getDateString()
	 * @var string
	 */
	public $date;

	public function setSenderString(string $sender): void {
		$this->sender = $sender;
	}

	public function setReceiverString(string $receiver): void {
		$this->receiver = $receiver;
	}

	public function setType(string $type): void {
		$this->type = $type;
	}

	public function setDateString(string $date): void {
		$this->date = $date;
	}

	public function setDatetime(DateTime $date): void {
		$this->setDateString($date->format("Y-m-d H:i:s"));
	}

	public function getSenderString(): string {
		return $this->sender;
	}

	public function getReceiverString(): string {
		return $this->receiver;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getDateString(): string {
		return $this->date;
	}

	public function getDate(): DateTime {
		return new DateTime($this->date);
	}
}