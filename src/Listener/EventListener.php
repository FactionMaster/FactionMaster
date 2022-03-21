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

namespace ShockedPlot7560\FactionMaster\Listener;

use pocketmine\block\Chest;
use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\Furnace;
use pocketmine\block\ItemFrame;
use pocketmine\block\Trapdoor;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\Bucket;
use pocketmine\item\Hoe;
use pocketmine\item\ItemIds;
use pocketmine\item\Shovel;
use pocketmine\player\Player;
use pocketmine\world\format\Chunk;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\ClaimEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\InvitationTable;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Manager\LeaderboardManager;
use ShockedPlot7560\FactionMaster\Task\DatabaseTask;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Leaderboard;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;
use function floor;
use function mb_substr;
use function str_replace;
use function strlen;
use function substr;
use function time;
use function trim;

class EventListener implements Listener {
	public static array $cooldownPowerVictim = [];
	public static array $cooldownPowerKiller = [];

	/** @var Main */
	private $main;

	public function __construct(Main $Main) {
		$this->main = $Main;
	}

	public function onBreak(BlockBreakEvent $event): void {
		$block = $event->getBlock();
		$level = $block->getPosition()->getWorld();
		$chunkX = (int) floor($block->getPosition()->getFloorX()/16);
		$chunkZ = (int) floor($block->getPosition()->getFloorZ()/16);
		$chunk = $level->getChunk($chunkX, $chunkZ);

		if (($factionClaim = MainAPI::getFactionClaim($level->getDisplayName(), $chunkX, $chunkZ)) !== null) {
			$factionPlayer = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
			if (!$factionPlayer instanceof FactionEntity) {
				$event->cancel();
				$event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_BREAK_CLAIM"));
				return;
			}
			if ($factionClaim->getFlag() !== null) {
				$event->cancel();
				switch ($factionClaim->getFlag()) {
					case Ids::FLAG_WARZONE:
						$event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_BREAK_WARZONE"));
						break;
					case Ids::FLAG_SPAWN:
						$event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_BREAK_SPAWN"));
						break;
				}
				return;
			}
			if ($factionPlayer instanceof FactionEntity && $factionClaim->getFactionName() !== $factionPlayer->getName()) {
				$event->cancel();
				$event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_BREAK_CLAIM"));
				return;
			}
		}
	}

	public function onPlace(BlockPlaceEvent $event): void {
		$block = $event->getBlock();
		$level = $block->getPosition()->getWorld();
		$chunkX = (int) floor($block->getPosition()->getFloorX()/16);
		$chunkZ = (int) floor($block->getPosition()->getFloorZ()/16);

		if (($factionClaim = MainAPI::getFactionClaim($level->getDisplayName(), $chunkX, $chunkZ)) !== null) {
			$factionPlayer = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
			if (!$factionPlayer instanceof FactionEntity) {
				$event->cancel();
				$event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_PLACE_CLAIM"));
				return;
			}
			if ($factionClaim->getFlag() !== null) {
				$event->cancel();
				switch ($factionClaim->getFlag()) {
					case Ids::FLAG_WARZONE:
						$event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_PLACE_WARZONE"));
						break;
					case Ids::FLAG_SPAWN:
						$event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_PLACE_SPAWN"));
						break;
				}
				return;
			}
			if ($factionPlayer instanceof FactionEntity && $factionClaim->getFactionName() !== $factionPlayer->getName()) {
				$event->cancel();
				$event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_PLACE_CLAIM"));
				return;
			}
		}
	}

	public function onDamage(EntityDamageByEntityEvent $event): void {
		$victim = $event->getEntity();
		$damager = $event->getDamager();
		if ($victim instanceof Player && $damager instanceof Player) {
			$damager = $damager->getName();
			if (MainAPI::sameFaction($victim->getName(), $damager)) {
				$event->cancel();
			}
			$victimFaction = MainAPI::getFactionOfPlayer($victim->getName());
			$damagerFaction = MainAPI::getFactionOfPlayer($damager);
			if ($damagerFaction instanceof FactionEntity
					&& $victimFaction instanceof FactionEntity
					&& MainAPI::isAlly($damagerFaction->getName(), $victimFaction->getName())) {
				$event->cancel();
			}
		} else {
			return;
		}
	}

	public function onDeath(PlayerDeathEvent $event): void {
		$entity = $event->getEntity();
		$cause = $entity->getLastDamageCause();
		$config = ConfigManager::getConfig();

		if ($cause instanceof EntityDamageByEntityEvent) {
			$damager = $cause->getDamager();
			if ($damager instanceof Player) {
				$victimInventoryArmor = $entity->getArmorInventory();

				if (!$config->get('allow-no-stuff')) {
					if ($victimInventoryArmor->getHelmet()->getId() == ItemIds::AIR
						&& $victimInventoryArmor->getChestplate()->getId() == ItemIds::AIR
						&& $victimInventoryArmor->getLeggings()->getId() == ItemIds::AIR
						&& $victimInventoryArmor->getBoots()->getId() == ItemIds::AIR) {
						return;
					}
				}

				$damagerName = $damager->getName();
				$victimName = $entity->getName();

				$victimFaction = MainAPI::getFactionOfPlayer($victimName);
				$damagerFaction = MainAPI::getFactionOfPlayer($damagerName);
				if ($damagerFaction instanceof FactionEntity) {
					if ($victimFaction instanceof FactionEntity) {
						$powerDamager = $config->get("power-win-per-kill") * $config->get("faction-multiplicator");
						$powerVictim = $config->get('power-loose-per-kill') * -1 * $config->get("faction-multiplicator");
					} else {
						$powerDamager = $config->get("power-win-per-kill");
					}
				} elseif ($victimFaction instanceof FactionEntity) {
					$powerVictim = $config->get("power-loose-per-death") * -1;
				}
				if (isset($powerDamager) && $damagerFaction instanceof FactionEntity) {
					$cooldown = $config->get("killer-cooldown", 0);
					if (!isset(self::$cooldownPowerKiller[$damagerFaction->getName()]) || $cooldown <= 0 || self::$cooldownPowerKiller[$damagerFaction->getName()] + $cooldown < time()) {
						MainAPI::changePower($damagerFaction->getName(), $powerDamager);
						self::$cooldownPowerKiller[$damagerFaction->getName()] = time();
					}
				}

				if (isset($powerVictim) && $victimFaction instanceof FactionEntity) {
					$cooldown = $config->get("victim-cooldown", 0);
					if (!isset(self::$cooldownPowerVictim[$victimFaction->getName()]) || $cooldown <= 0 || self::$cooldownPowerVictim[$victimFaction->getName()] + $cooldown < time()) {
						MainAPI::changePower($victimFaction->getName(), $powerVictim);
						self::$cooldownPowerVictim[$victimFaction->getName()] = time();
					}
				}

				if ($damagerFaction instanceof FactionEntity) {
					MainAPI::addXP($damagerFaction->getName(), $config->get("xp-win-per-kill"));
				}
			}
		}
	}

	public function onInteract(PlayerInteractEvent $event): void {
		$block = $event->getBlock();
		$item = $event->getItem();
		$player = $event->getPlayer();
		$level = $player->getWorld();
		$chunkX = (int) floor($block->getPosition()->getFloorX()/16);
		$chunkZ = (int) floor($block->getPosition()->getFloorZ()/16);
		$chunk = $level->getChunk($chunkX, $chunkZ);

		if (!$chunk instanceof Chunk) {
			return;
		}

		if ($item instanceof Hoe || $item instanceof Shovel || $item instanceof Bucket
				|| $block instanceof Chest || $block instanceof Door
				|| $block instanceof Trapdoor || $block instanceof FenceGate
				|| $block instanceof Furnace || $block instanceof ItemFrame) {
			if (($factionClaim = MainAPI::getFactionClaim($level->getDisplayName(), $chunkX, $chunkZ)) !== null) {
				$factionPlayer = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
				if (!$factionPlayer instanceof FactionEntity) {
					$event->cancel();
					$event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_INTERACT_CLAIM"));
					return;
				}
				if ($factionClaim->getFlag() !== null) {
					$event->cancel();
					switch ($factionClaim->getFlag()) {
						case Ids::FLAG_WARZONE:
							$event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_INTERACT_WARZONE"));
							break;
						case Ids::FLAG_SPAWN:
							$event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_INTERACT_SPAWN"));
							break;
					}
					return;
				}
				if ($factionPlayer instanceof FactionEntity && $factionClaim->getFactionName() !== $factionPlayer->getName()) {
					$event->cancel();
					$event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_INTERACT_CLAIM"));
					return;
				}
			}
		}
	}

	public function onJoin(PlayerJoinEvent $event): void {
		$leaderboards = ConfigManager::getLeaderboardConfig()->get("leaderboards");
		if ($leaderboards === false) {
			$leaderboards = [];
		}
		foreach ($leaderboards as $leaderboard) {
			if ($leaderboard["active"] == true) {
				$entity = new Leaderboard($leaderboard["slug"], $leaderboard["position"]);
				LeaderboardManager::placeScoreboard($entity, [$event->getPlayer()]);
			}
		}
	}

	public function onLogin(PlayerLoginEvent $event): void {
		$playerName = $event->getPlayer()->getName();
		$userEntity = MainAPI::getUser($playerName);
		if ($userEntity === null) {
			MainAPI::$languages[$playerName] = Utils::getConfigLang("default-language");
			MainAPI::addUser($playerName);
		} else {
			MainAPI::$languages[$playerName] = $userEntity->getLanguage();
		}
		Utils::newMenuSendTask(new MenuSendTask(
			function () use ($playerName) {
				return MainAPI::getUser($playerName) instanceof UserEntity;
			},
			function () use ($playerName) {
				$user = MainAPI::getUser($playerName);
				if ($user->getFactionName() !== null) {
					Main::getInstance()->getServer()->getAsyncPool()->submitTask(
						new DatabaseTask(
							"SELECT * FROM " . FactionTable::TABLE_NAME . " WHERE name = :name",
							[
								"name" => $user->getFactionName(),
							],
							function ($result) use ($user) {
								if (count($result) > 0) {
									$faction = $result[0];
									MainAPI::$factions[$user->getFactionName()] = $faction;
								}
							},
							FactionEntity::class
						));
				}
				Main::getInstance()->getServer()->getAsyncPool()->submitTask(
					new DatabaseTask(
						"SELECT * FROM " . InvitationTable::TABLE_NAME . " WHERE sender = :name OR receiver = :name" . ($user->getFactionName() !== null ? " OR sender = :factionName OR receiver = :factionName" : ""),
						($user->getFactionName() !== null ? [
							"name" => $playerName,
							"factionName" => $user->getFactionName(),
						] : [
							"name" => $playerName,
						]),
						function ($result) {
							foreach ($result as $invitation) {
								MainAPI::$invitation[$invitation->getSenderString() . "|" . $invitation->getReceiverString() . "|" . $invitation->getType()] = $invitation;
							}
						},
						InvitationEntity::class
					));
				Main::getInstance()->getServer()->getAsyncPool()->submitTask(
					new DatabaseTask(
						"SELECT * FROM " . UserTable::TABLE_NAME . " WHERE name = :name",
						[
							"name" => $playerName,
						],
						function ($result) use ($playerName) {
							if (count($result) > 0) {
								MainAPI::$users[$playerName] = $result[0];
							}
						},
						UserEntity::class
					));
			},
			function () use ($event) {
				$event->getPlayer()->kick(Utils::getText($event->getPlayer()->getName(), "ERROR_DATA_SAVING"));
			}
		));
		return;
	}

	public function onMove(PlayerMoveEvent $event): void {
		$config = ConfigManager::getConfig();
		if (Utils::getConfig("message-alert") === true) {
			if (!isset(Main::$activeTitle[$event->getPlayer()->getName()])
					|| (time() - Main::$activeTitle[$event->getPlayer()->getName()]) > (int) Utils::getConfig("message-alert-cooldown")) {
				$to = $event->getTo();
				$chunkX = (int) floor($to->getFloorX()/16);
				$chunkZ = (int) floor($to->getFloorZ()/16);
				$claim = $to->getWorld()->getChunk($chunkX, $chunkZ);

				$claim = MainAPI::getFactionClaim($to->getWorld()->getDisplayName(), $chunkX, $chunkZ);
				if ($claim instanceof ClaimEntity) {
					$faction = MainAPI::getFactionClaim($claim->getLevelName(), $claim->getX(), $claim->getZ());
					$color = "§f";
					$print = false;
					if ($faction->getFlag() === null) {
						$userEntity = MainAPI::getUser($event->getPlayer()->getName());
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
						$needles = ["{factionName}", "{colorStatus}", "{x}", "{z}", "{world}"];
						$replace = [$claim->getFactionName(), $color, $claim->getX(), $claim->getZ(), $claim->getLevelName()];
						$title = str_replace($needles, $replace, Utils::getConfig("message-alert-title"));
						$subtitle = str_replace($needles, $replace, Utils::getConfig("message-alert-subtitle"));
						$event->getPlayer()->sendTitle(
							$title,
							$subtitle
						);
						Main::$activeTitle[$event->getPlayer()->getName()] = time();
						$print = true;
					} elseif ($config->get("message-alert-flag-enabled") === true) {
						switch ($faction->getFlag()) {
							case Ids::FLAG_SPAWN:
								$color = $config->get("spawn-color");
								break;
							case Ids::FLAG_WARZONE:
								$color = $config->get("warzone-color");
								break;
						}
						$print = true;
					} elseif ($config->get("message-alert-flag-enabled") === false) {
						$print = false;
					}
					if ($print == true) {
						$needles = ["{factionName}", "{colorStatus}", "{x}", "{z}", "{world}"];
						$replace = [$claim->getFactionName(), $color, $claim->getX(), $claim->getZ(), $claim->getLevelName()];
						$title = str_replace($needles, $replace, Utils::getConfig("message-alert-title"));
						$subtitle = str_replace($needles, $replace, Utils::getConfig("message-alert-subtitle"));
						$event->getPlayer()->sendTitle(
							$title,
							$subtitle
						);
						Main::$activeTitle[$event->getPlayer()->getName()] = time();
					}
				}
			}
		}
	}

	public function onChat(PlayerChatEvent $event): void {
		if (mb_substr(trim($event->getMessage()), 0, 1) === Utils::getConfig("faction-chat-symbol") && Utils::getConfig("faction-chat-active") === true) {
			$faction = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
			if ($faction instanceof FactionEntity) {
				$message = str_replace(
					[ "{factionName}", "{playerName}", "{message}" ],
					[ $faction->getName(), $event->getPlayer()->getName(), substr(trim($event->getMessage()), 1, strlen(trim($event->getMessage())))],
					Utils::getConfig("faction-chat-message")
				);
				foreach ($faction->getMembers() as $name => $rank) {
					$player = $this->main->getServer()->getPlayerExact($name);
					if ($player instanceof Player) {
						$player->sendMessage($message);
					}
				}
				$event->cancel();
			}
		} elseif (mb_substr(trim($event->getMessage()), 0, 1) === Utils::getConfig("ally-chat-symbol") && Utils::getConfig("ally-chat-active") === true) {
			$faction = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
			if ($faction instanceof FactionEntity) {
				$message = str_replace(
					[ "{factionName}", "{playerName}", "{message}" ],
					[ $faction->getName(), $event->getPlayer()->getName(), substr(trim($event->getMessage()), 1, strlen(trim($event->getMessage())))],
					Utils::getConfig("ally-chat-message")
				);
				foreach ($faction->getAllyInstance() as $ally) {
					if ($ally instanceof FactionEntity) {
						foreach ($ally->getMembers() as $name => $rank) {
							$player = $this->main->getServer()->getPlayerExact($name);
							if ($player instanceof Player) {
								$player->sendMessage($message);
							}
						}
					}
				}
				foreach ($faction->getMembers() as $name => $rank) {
					$player = $this->main->getServer()->getPlayerExact($name);
					if ($player instanceof Player) {
						$player->sendMessage($message);
					}
				}
				$event->cancel();
			}
		}
	}
}