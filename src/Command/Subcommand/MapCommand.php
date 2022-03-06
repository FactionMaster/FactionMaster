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
use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\ClaimEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\args\RawStringArgument;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Manager\MapManager;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function array_keys;
use function array_map;
use function floor;
use function intval;
use function round;
use function str_replace;
use function strlen;
use function substr;

class MapCommand extends FactionSubCommand {

	public function getId(): string {
		return "COMMAND_MAP_DESCRIPTION";
	}

	const DIRECTION = [
		"N" => 'N',
		"NE" => '/',
		"E" => 'E',
		"SE" => '\\',
		"S" => 'S',
		"SW" => '/',
		"W" => 'W',
		"NW" => '\\',
		"NONE" => '+',
	];

	const NO_SYMBOL = "0";

	protected function prepare(): void {
		if (Utils::getConfig("f-map-task") !== false) {
			$this->registerArgument(0, new RawStringArgument("on|off", true));
		}
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		if (isset($args["on|off"]) && $args["on|off"] === "off") {
			MapManager::remove($sender);
		} else {
			if (isset($args["on|off"]) && $args["on|off"] === "on" && !MapManager::isRegister($sender)) {
				MapManager::add($sender);
			}
			$config = ConfigManager::getConfig();

			$player = $sender;
			$userEntity = MainAPI::getUser($player->getName());
			if (!$userEntity instanceof UserEntity) {
				return;
			}

			$centralChunkX = floor($player->getPosition()->getFloorX()/16);
			$centralChunkZ = floor($player->getPosition()->getFloorZ()/16);
			$centralChunk = $player->getWorld()->getChunk($centralChunkX, $centralChunkZ);
			$symbolData = $config->get("available-symbol");
			$symbolCursor = 0;
			$claimData = [];

			$x = round($player->getPosition()->getX());
			$z = round($player->getPosition()->getZ());
			$chunkFaction = MainAPI::getFactionClaim($player->getWorld()->getDisplayName(), $centralChunkX, $centralChunkZ);
			$factionLabelColor = '§f';
			if ($chunkFaction instanceof ClaimEntity && $chunkFaction->getFlag() === null) {
				if ($chunkFaction instanceof ClaimEntity && $chunkFaction->getFactionName() === $userEntity->getFactionName()) {
					$factionLabelColor = $config->get("claim-own-color");
				} elseif ($chunkFaction instanceof ClaimEntity && MainAPI::isAlly($userEntity->getFactionName(), $chunkFaction->getFactionName())) {
					$factionLabelColor = $config->get("claim-ally-color");
				} else {
					$factionLabelColor = $config->get("claim-ennemie-color");
				}
			} elseif ($chunkFaction instanceof ClaimEntity) {
				switch ($chunkFaction->getFlag()) {
					case Ids::FLAG_SPAWN:
						$factionLabelColor = $config->get("spawn-color");
						break;
					case Ids::FLAG_WARZONE:
						$factionLabelColor = $config->get("warzone-color");
						break;
				}
			}
			$factionLabel = $chunkFaction instanceof ClaimEntity ? $factionLabelColor . $chunkFaction->getFactionName() : $config->get("wilderness-color") . "Wilderness";
			$headerColor = $config->get("map-header-color");
			$middleString = $config->get("map-middle-header");
			$middleString = str_replace(["{{x}}", "{{z}}", "{{factionLabel}}", "{{headerColor}}"], [$x, $z, $factionLabel, $headerColor], $middleString);
			$lenMiddle = strlen($middleString) - 3;
			$bottom = "";
			for ($i = 0; $i < floor((48 - $lenMiddle) / 2); $i++) {
				$bottom .= "_";
			}
			$player->sendMessage($headerColor . $bottom . $middleString . $bottom);
			for ($mZ = 0; $mZ < $config->get("map-height"); $mZ++) {
				$lign = "";
				for ($mX = 0; $mX < $config->get("map-width"); $mX++) {
					$x = $centralChunkX + $mX - ($config->get("map-width") / 2);
					$z = $centralChunkZ + $mZ - ($config->get("map-height") / 2);

					if ($centralChunkX == $x && $centralChunkZ == $z) {
						$lign .= $config->get("player-color") . $config->get("player-symbol");
						continue;
					} else {
						$faction = MainAPI::getFactionClaim($player->getWorld()->getDisplayName(), $x, $z);
						if ($faction instanceof ClaimEntity) {
							$data = [
								"COLOR" => "§f",
								"SYMBOL" => "-",
								"FACTION" => "Unknow"
							];
							if ($faction->getFlag() === null) {
								if (!isset($claimData[$faction->getFactionName()])) {
									if (!isset($symbolData[$symbolCursor])) {
										$lign .= $config->get("claim-color") . self::NO_SYMBOL;
										continue;
									} else {
										if ($userEntity->getFactionName() !== null) {
											if (MainAPI::isAlly($userEntity->getFactionName(), $faction->getFactionName())) {
												$color = $config->get("claim-ally-color");
											} elseif ($faction->getFactionName() === $userEntity->getFactionName()) {
												$color = $config->get("claim-own-color");
											} else {
												$color = $config->get("claim-color");
											}
										} else {
											$color = $config->get("claim-color");
										}
										$claimData[$faction->getFactionName()] = [
											"SYMBOL" => $symbolData[$symbolCursor],
											"COLOR" => $color,
											"FACTION" => $faction->getFactionName(),
										];
										$symbolCursor++;
									}
								}
								$data = $claimData[$faction->getFactionName()];
							} else {
								switch ($faction->getFlag()) {
									case Ids::FLAG_SPAWN:
										$data = [
											"COLOR" => $config->get("spawn-color"),
											"SYMBOL" => $config->get("spawn-symbol"),
											"FACTION" => $faction->getFactionName()
										];
										$claimData[$faction->getFactionName()] = [
											"SYMBOL" => $config->get("spawn-symbol"),
											"COLOR" => $config->get("spawn-color"),
											"FACTION" => $faction->getFactionName(),
										];
										break;
									case Ids::FLAG_WARZONE:
										$data = [
											"COLOR" => $config->get("warzone-color"),
											"SYMBOL" => $config->get("warzone-symbol"),
											"FACTION" => $faction->getFactionName()
										];
										$claimData[$faction->getFactionName()] = [
											"SYMBOL" => $config->get("warzone-symbol"),
											"COLOR" => $config->get("warzone-color"),
											"FACTION" => $faction->getFactionName(),
										];
										break;
								}
							}
							$lign .= $data['COLOR'] . $data['SYMBOL'];
						} else {
							$lign .= $config->get("wilderness-color") . $config->get("wilderness-symbol");
						}
					}
				}
				if ($mZ <= 2) {
					$degrees = ($player->getLocation()->getYaw() - 157) % 360;
					if ($degrees < 0) {
						$degrees += 360;
					}

					$Direction = array_keys(self::DIRECTION)[intval(floor($degrees / 45))];
					$Compass = [["NW", "N", "NE"], ["W", "NONE", "E"], ["SW", "S", "SE"]];
					$Compass = array_map(function (array $DirectionsData) use ($Direction) {
						$text = "";
						foreach ($DirectionsData as $DirectionData) {
							if ($Direction === $DirectionData) {
								$text .= ConfigManager::getConfig()->get("compass-color");
							} else {
								$text .= ConfigManager::getConfig()->get("compass-color-actual");
							}
							$text .= self::DIRECTION[$DirectionData];
						}
						return $text;
					}, $Compass);
					$lign = $Compass[$mZ] . substr($lign, 12, strlen($lign));
				}
				$player->sendMessage($lign);
			}
			$text = "";
			foreach ($claimData as $key => $Claim) {
				$newString = " §r" . $Claim['COLOR'] . $Claim['SYMBOL'] . ": " . $Claim['FACTION'];
				if (strlen($text . $newString) > $config->get("map-width")) {
					$player->sendMessage($text . $newString);
					$text = "";
				} else {
					$text .= $newString;
				}
			}
			$player->sendMessage($text);
		}
	}
}