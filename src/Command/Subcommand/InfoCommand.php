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

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use pocketmine\command\CommandSender;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\args\RawStringArgument;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;
use function floor;
use function strlen;

class InfoCommand extends FactionSubCommand {

	public function getId(): string {
		return "COMMAND_INFO_DESCRIPTION_GLOBAL";
	}

	protected function prepare(): void {
		$this->registerArgument(0, new RawStringArgument("name", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$faction = MainAPI::getFactionOfPlayer($sender->getName());
		if (!isset($args['name']) && !$faction instanceof FactionEntity) {
			$this->sendUsage();
			return;
		} elseif (isset($args['name'])) {
			$faction = MainAPI::getFaction($args['name']);
		}
		if ($faction === null) {
			$sender->sendMessage(Utils::getText($sender->getName(), "FACTION_DONT_EXIST"));
			return;
		}
		$middleString = ".[ §a" . $faction->getName() . " §6].";
		$lenMiddle = strlen($middleString) - 4;
		$bottom = "";
		for ($i = 0; $i < floor((48 - $lenMiddle) / 2); $i++) {
			$bottom .= "_";
		}
		$sender->sendMessage("§6" . $bottom . $middleString . $bottom);
		$description = ($faction->getDescription() === "" ? Utils::getText($sender->getName(), "COMMAND_NO_DESCRIPTION") : $faction->getDescription());
		$sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_DESCRIPTION", ['description' => $description]));
		switch ($faction->getVisibilityId()) {
			case Ids::PUBLIC_VISIBILITY:
				$visibility = "§a" . Utils::getText($sender->getName(), "PUBLIC_VISIBILITY_NAME");
				break;
			case Ids::PRIVATE_VISIBILITY:
				$visibility = "§4" . Utils::getText($sender->getName(), "PRIVATE_VISIBILITY_NAME");
				break;
			case Ids::INVITATION_VISIBILITY:
				$visibility = "§6" . Utils::getText($sender->getName(), "INVITATION_VISIBILITY_NAME");
				break;
			default:
				$visibility = "Unknow";
				break;
		}
		$sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_VISIBILITY", ['visibility' => $visibility]));
		$sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_LEVEL", ['level' => $faction->getLevel(), 'power' => $faction->getPower()]));
		$ally = "";
		foreach ($faction->getAlly() as $key => $ally) {
			if ($key == count($faction->getAlly()) - 1) {
				$ally .= $ally;
			} else {
				$ally .= $ally . ", ";
			}
		}
		if (count($faction->getAlly()) == 0) {
			$ally = Utils::getText($sender->getName(), "COMMAND_NO_ALLY");
		}
		$sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_ALLY", ['ally' => $ally]));
		$members = "";
		$i = 0;
		foreach ($faction->getMembers() as $member => $rank) {
			switch ($rank) {
				case Ids::OWNER_ID:
					$members .= Utils::getText($sender->getName(), "COMMAND_INFO_MEMBER_OWNER", ['name' => $member]);
					break;
				case Ids::COOWNER_ID:
					$members .= Utils::getText($sender->getName(), "COMMAND_INFO_MEMBER_COOWNER", ['name' => $member]);
					break;
				case Ids::MEMBER_ID:
					$members .= Utils::getText($sender->getName(), "COMMAND_INFO_MEMBER_MEMBER", ['name' => $member]);
					break;
				case Ids::RECRUIT_ID:
					$members .= Utils::getText($sender->getName(), "COMMAND_INFO_MEMBER_RECRUIT", ['name' => $member]);
					break;
			}
			if ($i != count($faction->getMembers()) - 1) {
				$members .= " / ";
			}
			$i++;
		}
		$sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_MEMBER", ['members' => $members]));
		$Date = $faction->getDate();
		$sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_DATE", ['date' => $Date->format("d M")]));
	}
}