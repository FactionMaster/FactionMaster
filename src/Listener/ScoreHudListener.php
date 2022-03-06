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

use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\Server;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\AllianceBreakEvent;
use ShockedPlot7560\FactionMaster\Event\AllianceCreateEvent;
use ShockedPlot7560\FactionMaster\Event\DescriptionChangeEvent;
use ShockedPlot7560\FactionMaster\Event\FactionClaimEvent;
use ShockedPlot7560\FactionMaster\Event\FactionCreateEvent;
use ShockedPlot7560\FactionMaster\Event\FactionDeleteEvent;
use ShockedPlot7560\FactionMaster\Event\FactionHomeCreateEvent;
use ShockedPlot7560\FactionMaster\Event\FactionHomeDeleteEvent;
use ShockedPlot7560\FactionMaster\Event\FactionJoinEvent;
use ShockedPlot7560\FactionMaster\Event\FactionLeaveEvent;
use ShockedPlot7560\FactionMaster\Event\FactionLevelChangeEvent;
use ShockedPlot7560\FactionMaster\Event\FactionOptionUpdateEvent;
use ShockedPlot7560\FactionMaster\Event\FactionPowerEvent;
use ShockedPlot7560\FactionMaster\Event\FactionPropertyTransferEvent;
use ShockedPlot7560\FactionMaster\Event\FactionUnclaimEvent;
use ShockedPlot7560\FactionMaster\Event\FactionXPChangeEvent;
use ShockedPlot7560\FactionMaster\Event\MemberChangeRankEvent;
use ShockedPlot7560\FactionMaster\Event\MessageChangeEvent;
use ShockedPlot7560\FactionMaster\Event\VisibilityChangeEvent;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;
use function is_string;

class ScoreHudListener implements Listener {

	/** @var Server */
	private $server;

	public function __construct(Main $main) {
		$this->server = $main->getServer();
	}

	public function onTagResolve(TagsResolveEvent $event): void {
		$player = $event->getPlayer();
		$tag = $event->getTag();
		switch ($tag->getName()) {
			case Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION:
				$faction = MainAPI::getFactionOfPlayer($player->getName());
				if ($faction instanceof FactionEntity) {
					$tag->setValue($faction->getDescription());
				} else {
					$tag->setValue("");
				}
				break;
			case Ids::HUD_FACTIONMASTER_FACTION_LEVEL:
				$faction = MainAPI::getFactionOfPlayer($player->getName());
				if ($faction instanceof FactionEntity) {
					$tag->setValue($faction->getLevel());
				} else {
					$tag->setValue(0);
				}
				break;
			case Ids::HUD_FACTIONMASTER_FACTION_MESSAGE:
				$faction = MainAPI::getFactionOfPlayer($player->getName());
				if ($faction instanceof FactionEntity) {
					$tag->setValue($faction->getMessage());
				} else {
					$tag->setValue("");
				}
				break;
			case Ids::HUD_FACTIONMASTER_FACTION_NAME:
				$faction = MainAPI::getFactionOfPlayer($player->getName());
				if ($faction instanceof FactionEntity) {
					$tag->setValue($faction->getName());
				} else {
					$tag->setValue(Utils::getText($player->getName(), "NO_FACTION_TAG"));
				}
				break;
			case Ids::HUD_FACTIONMASTER_FACTION_POWER:
				$faction = MainAPI::getFactionOfPlayer($player->getName());
				if ($faction instanceof FactionEntity) {
					$tag->setValue($faction->getPower());
				} else {
					$tag->setValue(0);
				}
				break;
			case Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY:
				$faction = MainAPI::getFactionOfPlayer($player->getName());
				if ($faction instanceof FactionEntity) {
					switch ($faction->getVisibilityId()) {
						case Ids::PUBLIC_VISIBILITY:
							$visibility = "§a" . Utils::getText($player->getName(), "PUBLIC_VISIBILITY_NAME");
							break;
						case Ids::PRIVATE_VISIBILITY:
							$visibility = "§4" . Utils::getText($player->getName(), "PRIVATE_VISIBILITY_NAME");
							break;
						case Ids::INVITATION_VISIBILITY:
							$visibility = "§6" . Utils::getText($player->getName(), "INVITATION_VISIBILITY_NAME");
							break;
						default:
							$visibility = "Unknow";
							break;
					}
					$tag->setValue($visibility);
				} else {
					$tag->setValue(Utils::getText($player->getName(), "NO_FACTION_TAG"));
				}
				break;
			case Ids::HUD_FACTIONMASTER_FACTION_XP:
				$faction = MainAPI::getFactionOfPlayer($player->getName());
				if ($faction instanceof FactionEntity) {
					$tag->setValue($faction->getXP());
				} else {
					$tag->setValue(0);
				}
				break;
			case Ids::HUD_FACTIONMASTER_PLAYER_RANK:
				$user = MainAPI::getUser($player->getName());
				if ($user instanceof UserEntity) {
					if ($user->getRank() !== null && $user->getFactionName() !== null) {
						switch ($user->getRank()) {
							case Ids::RECRUIT_ID:
								$rank = Utils::getText($player->getName(), "RECRUIT_RANK_NAME");
								break;
							case Ids::MEMBER_ID:
								$rank = Utils::getText($player->getName(), "MEMBER_RANK_NAME");
								break;
							case Ids::COOWNER_ID:
								$rank = Utils::getText($player->getName(), "COOWNER_RANK_NAME");
								break;
							case Ids::OWNER_ID:
								$rank = Utils::getText($player->getName(), "OWNER_RANK_NAME");
								break;
							default:
								$rank = "Unknow";
								break;
						}
						$tag->setValue($rank);
					} else {
						$tag->setValue(Utils::getText($player->getName(), "NO_FACTION_TAG"));
					}
				} else {
					$tag->setValue(Utils::getText($player->getName(), "NO_FACTION_TAG"));
				}
				break;
			case Ids::HUD_FACTIONMASTER_FACTION_MAX_ALLY:
			case Ids::HUD_FACTIONMASTER_FACTION_MAX_CLAIM:
			case Ids::HUD_FACTIONMASTER_FACTION_MAX_HOME:
			case Ids::HUD_FACTIONMASTER_FACTION_MAX_PLAYER:
			case Ids::HUD_FACTIONMASTER_FACTION_NUMBER_ALLY:
			case Ids::HUD_FACTIONMASTER_FACTION_NUMBER_CLAIM:
			case Ids::HUD_FACTIONMASTER_FACTION_NUMBER_HOME:
			case Ids::HUD_FACTIONMASTER_FACTION_NUMBER_PLAYER:
				$faction = MainAPI::getFactionOfPlayer($player->getName());
				$value = 0;
				if ($faction instanceof FactionEntity) {
					switch ($tag->getName()) {
						case Ids::HUD_FACTIONMASTER_FACTION_MAX_ALLY:
							$value = $faction->getMaxAlly();
							break;
						case Ids::HUD_FACTIONMASTER_FACTION_MAX_CLAIM:
							$value = $faction->getMaxClaim();
							break;
						case Ids::HUD_FACTIONMASTER_FACTION_MAX_HOME:
							$value = $faction->getMaxHome();
							break;
						case Ids::HUD_FACTIONMASTER_FACTION_MAX_PLAYER:
							$value = $faction->getMaxPlayer();
							break;
						case Ids::HUD_FACTIONMASTER_FACTION_NUMBER_ALLY:
							$value = count($faction->getAlly());
							break;
						case Ids::HUD_FACTIONMASTER_FACTION_NUMBER_CLAIM:
							$value = count(MainAPI::getClaimsFaction($faction->getName()));
							break;
						case Ids::HUD_FACTIONMASTER_FACTION_NUMBER_HOME:
							$value = count(MainAPI::getFactionHomes($faction->getName()));
							break;
						case Ids::HUD_FACTIONMASTER_FACTION_NUMBER_PLAYER:
							$value = count($faction->getMembers());
							break;
					}
				}
				$tag->setValue($value);
				break;
		}
	}

	public function onFactionOptionUpdate(FactionOptionUpdateEvent $event): void {
		$faction = $event->getFaction();
		if ($faction instanceof FactionEntity) {
			foreach ($faction->getMembers() as $name => $rank) {
				$player = $this->server->getPlayerExact($name);
				$optionSlug = null;
				switch ($event->getOption()) {
					case "maxAlly":
						$optionSlug = Ids::HUD_FACTIONMASTER_FACTION_MAX_ALLY;
						break;
					case "maxHome":
						$optionSlug = Ids::HUD_FACTIONMASTER_FACTION_MAX_HOME;
						break;
					case "maxClaim":
						$optionSlug = Ids::HUD_FACTIONMASTER_FACTION_MAX_CLAIM;
						break;
					case "maxPlayer":
						$optionSlug = Ids::HUD_FACTIONMASTER_FACTION_MAX_PLAYER;
						break;
				}
				if ($optionSlug !== null) {
					if ($player instanceof Player) {
						$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
							$optionSlug,
							$event->getValue()
						));
						$ev->call();
					}
				}
			}
		}
	}

	public function onFactionCreate(FactionCreateEvent $event): void {
		$player = $event->getPlayer();
		$faction = $event->getFaction();
		if ($faction instanceof FactionEntity) {
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_NAME,
				$faction->getName()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_POWER,
				$faction->getPower()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_LEVEL,
				$faction->getLevel()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_XP,
				$faction->getXP()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_MESSAGE,
				$faction->getMessage()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION,
				$faction->getDescription()
			));
			switch ($faction->getVisibilityId()) {
				case Ids::PUBLIC_VISIBILITY:
					$visibility = "§a" . Utils::getText($player->getName(), "PUBLIC_VISIBILITY_NAME");
					break;
				case Ids::PRIVATE_VISIBILITY:
					$visibility = "§4" . Utils::getText($player->getName(), "PRIVATE_VISIBILITY_NAME");
					break;
				case Ids::INVITATION_VISIBILITY:
					$visibility = "§6" . Utils::getText($player->getName(), "INVITATION_VISIBILITY_NAME");
					break;
				default:
					$visibility = "Unknow";
					break;
			}
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY,
				$visibility
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_MAX_HOME,
				$faction->getMaxHome()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_MAX_ALLY,
				$faction->getMaxAlly()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_MAX_CLAIM,
				$faction->getMaxClaim()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_MAX_PLAYER,
				$faction->getMaxPlayer()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_NUMBER_CLAIM,
				count(MainAPI::getClaimsFaction($faction->getName()))
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_NUMBER_ALLY,
				count($faction->getAlly())
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_NUMBER_HOME,
				count(MainAPI::getFactionHomes($faction->getName()))
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_NUMBER_PLAYER,
				count($faction->getMembers())
			));
			$ev->call();
		}
		$user = MainAPI::getUser($player->getName());
		if ($user instanceof UserEntity && $user->getFactionName() !== null && $user->getRank() !== null) {
			switch ($user->getRank()) {
				case Ids::RECRUIT_ID:
					$rank = Utils::getText($player->getName(), "RECRUIT_RANK_NAME");
					break;
				case Ids::MEMBER_ID:
					$rank = Utils::getText($player->getName(), "MEMBER_RANK_NAME");
					break;
				case Ids::COOWNER_ID:
					$rank = Utils::getText($player->getName(), "COOWNER_RANK_NAME");
					break;
				case Ids::OWNER_ID:
					$rank = Utils::getText($player->getName(), "OWNER_RANK_NAME");
					break;
				default:
					$rank = "Unknow";
					break;
			}
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_PLAYER_RANK,
				$rank
			));
			$ev->call();
		} else {
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_PLAYER_RANK,
				Utils::getText($player->getName(), "NO_FACTION_TAG")
			));
			$ev->call();
		}
	}

	public function onPropertyTransfer(FactionPropertyTransferEvent $event): void {
		$originUser = MainAPI::getUser($event->getPlayer()->getName());
		if ($originUser->getRank() !== null) {
			switch ($originUser->getRank()) {
				case Ids::RECRUIT_ID:
					$rank = Utils::getText($event->getPlayer()->getName(), "RECRUIT_RANK_NAME");
					break;
				case Ids::MEMBER_ID:
					$rank = Utils::getText($event->getPlayer()->getName(), "MEMBER_RANK_NAME");
					break;
				case Ids::COOWNER_ID:
					$rank = Utils::getText($event->getPlayer()->getName(), "COOWNER_RANK_NAME");
					break;
				case Ids::OWNER_ID:
					$rank = Utils::getText($event->getPlayer()->getName(), "OWNER_RANK_NAME");
					break;
				default:
					$rank = "Unknow";
					break;
			}
			$ev = new PlayerTagUpdateEvent($event->getPlayer(), new ScoreTag(
				Ids::HUD_FACTIONMASTER_PLAYER_RANK,
				$rank
			));
			$ev->call();
		}
		$targetPlayer = $this->server->getPlayerExact($event->getTarget()->name);
		if (!$targetPlayer instanceof Player) {
			return;
		}
		if ($event->getTarget()->getRank() !== null) {
			switch ($event->getTarget()->getRank()) {
				case Ids::RECRUIT_ID:
					$rank = Utils::getText($targetPlayer->getName(), "RECRUIT_RANK_NAME");
					break;
				case Ids::MEMBER_ID:
					$rank = Utils::getText($targetPlayer->getName(), "MEMBER_RANK_NAME");
					break;
				case Ids::COOWNER_ID:
					$rank = Utils::getText($targetPlayer->getName(), "COOWNER_RANK_NAME");
					break;
				case Ids::OWNER_ID:
					$rank = Utils::getText($event->getPlayer()->getName(), "OWNER_RANK_NAME");
					break;
				default:
					$rank = "Unknow";
					break;
			}
			$ev = new PlayerTagUpdateEvent($targetPlayer, new ScoreTag(
				Ids::HUD_FACTIONMASTER_PLAYER_RANK,
				$rank
			));
			$ev->call();
		}
	}

	public function onFactionJoin(FactionJoinEvent $event): void {
		$player = $event->getTarget();
		if (!$player instanceof Player) {
			$player =  $this->server->getPlayerExact($player);
		}
		if (!$player instanceof Player) {
			return;
		}
		$faction = $event->getFaction();
		if ($faction instanceof FactionEntity) {
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_NAME,
				$faction->getName()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_POWER,
				$faction->getPower()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_LEVEL,
				$faction->getLevel()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_XP,
				$faction->getXP()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_MESSAGE,
				$faction->getMessage()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION,
				$faction->getDescription()
			));
			switch ($faction->getVisibilityId()) {
				case Ids::PUBLIC_VISIBILITY:
					$visibility = "§a" . Utils::getText($player->getName(), "PUBLIC_VISIBILITY_NAME");
					break;
				case Ids::PRIVATE_VISIBILITY:
					$visibility = "§4" . Utils::getText($player->getName(), "PRIVATE_VISIBILITY_NAME");
					break;
				case Ids::INVITATION_VISIBILITY:
					$visibility = "§6" . Utils::getText($player->getName(), "INVITATION_VISIBILITY_NAME");
					break;
				default:
					$visibility = "Unknow";
					break;
			}
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY,
				$visibility
			));
			$ev->call();
			foreach ($faction->getMembers() as $name => $rank) {
				$playerNew = $this->server->getPlayerExact($name);
				if ($playerNew instanceof Player) {
					$ev = new PlayerTagUpdateEvent($playerNew, new ScoreTag(
						Ids::HUD_FACTIONMASTER_FACTION_NUMBER_PLAYER,
						count($faction->getMembers())
					));
					$ev->call();
				}
			}
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_MAX_HOME,
				$faction->getMaxHome()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_MAX_ALLY,
				$faction->getMaxAlly()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_MAX_CLAIM,
				$faction->getMaxClaim()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_MAX_PLAYER,
				$faction->getMaxPlayer()
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_NUMBER_CLAIM,
				count(MainAPI::getClaimsFaction($faction->getName()))
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_NUMBER_ALLY,
				count($faction->getAlly())
			));
			$ev->call();
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_FACTION_NUMBER_HOME,
				count(MainAPI::getFactionHomes($faction->getName()))
			));
			$ev->call();
		}
		$user = MainAPI::getUser($player->getName());
		if ($user instanceof UserEntity && $user->getFactionName() !== null && $user->getRank() !== null) {
			switch ($user->getRank()) {
				case Ids::RECRUIT_ID:
					$rank = Utils::getText($player->getName(), "RECRUIT_RANK_NAME");
					break;
				case Ids::MEMBER_ID:
					$rank = Utils::getText($player->getName(), "MEMBER_RANK_NAME");
					break;
				case Ids::COOWNER_ID:
					$rank = Utils::getText($player->getName(), "COOWNER_RANK_NAME");
					break;
				case Ids::OWNER_ID:
					$rank = Utils::getText($player->getName(), "OWNER_RANK_NAME");
					break;
				default:
					$rank = "Unknow";
					break;
			}
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_PLAYER_RANK,
				$rank
			));
			$ev->call();
		} else {
			$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
				Ids::HUD_FACTIONMASTER_PLAYER_RANK,
				Utils::getText($player->getName(), "NO_FACTION_TAG")
			));
			$ev->call();
		}
	}

	public function onPower(FactionPowerEvent $event): void {
		$faction = $event->getFaction();
		foreach ($faction->getMembers() as $name => $rank) {
			$player = $this->server->getPlayerExact($name);
			if ($player instanceof Player) {
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_POWER,
					$faction->getPower()
				));
				$ev->call();
			}
		}
	}

	public function onLevelChange(FactionLevelChangeEvent $event): void {
		$faction = $event->getFaction();
		foreach ($faction->getMembers() as $name => $rank) {
			$player = $this->server->getPlayerExact($name);
			if ($player instanceof Player) {
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_LEVEL,
					$faction->getLevel()
				));
				$ev->call();
			}
		}
	}

	public function onXPChange(FactionXPChangeEvent $event): void {
		$faction = $event->getFaction();
		foreach ($faction->getMembers() as $name => $rank) {
			$player = $this->server->getPlayerExact($name);
			if ($player instanceof Player) {
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_XP,
					$faction->getXP()
				));
				$ev->call();
			}
		}
	}

	public function onMessageChange(MessageChangeEvent $event): void {
		$faction = $event->getFaction();
		foreach ($faction->getMembers() as $name => $rank) {
			$player = $this->server->getPlayerExact($name);
			if ($player instanceof Player) {
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_MESSAGE,
					$faction->getMessage()
				));
				$ev->call();
			}
		}
	}

	public function onDescriptionChange(DescriptionChangeEvent $event): void {
		$faction = $event->getFaction();
		foreach ($faction->getMembers() as $name => $rank) {
			$player = $this->server->getPlayerExact($name);
			if ($player instanceof Player) {
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION,
					$event->getFaction()->getDescription()
				));
				$ev->call();
			}
		}
	}

	public function onVisibilityChange(VisibilityChangeEvent $event): void {
		$faction = $event->getFaction();
		foreach ($faction->getMembers() as $name => $rank) {
			$player = $this->server->getPlayerExact($name);
			if ($player instanceof Player) {
				switch ($faction->getVisibilityId()) {
					case Ids::PUBLIC_VISIBILITY:
						$visibility = "§a" . Utils::getText($player->getName(), "PUBLIC_VISIBILITY_NAME");
						break;
					case Ids::PRIVATE_VISIBILITY:
						$visibility = "§4" . Utils::getText($player->getName(), "PRIVATE_VISIBILITY_NAME");
						break;
					case Ids::INVITATION_VISIBILITY:
						$visibility = "§6" . Utils::getText($player->getName(), "INVITATION_VISIBILITY_NAME");
						break;
					default:
						$visibility = "Unknow";
						break;
				}
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY,
					$visibility
				));
				$ev->call();
			}
		}
	}

	public function onRankChange(MemberChangeRankEvent $event): void {
		$user = $event->getTarget();
		$player = $this->server->getPlayerExact($user->getName());
		if ($player instanceof Player) {
			if ($user->getRank() !== null && $user->getFactionName() !== null) {
				switch ($user->getRank()) {
					case Ids::RECRUIT_ID:
						$rank = Utils::getText($player->getName(), "RECRUIT_RANK_NAME");
						break;
					case Ids::MEMBER_ID:
						$rank = Utils::getText($player->getName(), "MEMBER_RANK_NAME");
						break;
					case Ids::COOWNER_ID:
						$rank = Utils::getText($player->getName(), "COOWNER_RANK_NAME");
						break;
					case Ids::OWNER_ID:
						$rank = Utils::getText($player->getName(), "OWNER_RANK_NAME");
						break;
					default:
						$rank = "Unknow";
						break;
				}
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_PLAYER_RANK,
					$rank
				));
				$ev->call();
			} else {
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_PLAYER_RANK,
					Utils::getText($player->getName(), "NO_FACTION_TAG")
				));
				$ev->call();
			}
		}
	}

	public function onFactionLeave(FactionLeaveEvent $event): void {
		$player = $event->getTarget();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_NAME,
			Utils::getText($player->getName(), "NO_FACTION_TAG")
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_POWER,
			0
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_LEVEL,
			0
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_XP,
			0
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_MESSAGE,
			""
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION,
			""
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY,
			Utils::getText($player->getName(), "NO_FACTION_TAG")
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_PLAYER_RANK,
			Utils::getText($player->getName(), "NO_FACTION_TAG")
		));
		$ev->call();
		$faction = $event->getFaction();
		if (is_string($faction)) {
			$faction = MainAPI::getFaction($faction);
		}
		if ($faction instanceof FactionEntity) {
			foreach ($faction->getMembers() as $name => $rank) {
				$playerNew = $this->server->getPlayerExact($name);
				if ($playerNew instanceof Player) {
					$ev = new PlayerTagUpdateEvent($playerNew, new ScoreTag(
						Ids::HUD_FACTIONMASTER_FACTION_NUMBER_PLAYER,
						count($faction->getMembers())
					));
					$ev->call();
				}
			}
		}
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_MAX_PLAYER,
			0
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_MAX_HOME,
			0
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_MAX_ALLY,
			0
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_MAX_CLAIM,
			0
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_NUMBER_CLAIM,
			0
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_NUMBER_PLAYER,
			0
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_NUMBER_ALLY,
			0
		));
		$ev->call();
		$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
			Ids::HUD_FACTIONMASTER_FACTION_NUMBER_HOME,
			0
		));
		$ev->call();
	}

	public function onFactionDelete(FactionDeleteEvent $event): void {
		foreach ($event->getFaction()->getMembers() as $name => $rank) {
			$player = $this->server->getPlayerExact($name);
			if ($player instanceof Player) {
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_NAME,
					Utils::getText($player->getName(), "NO_FACTION_TAG")
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_POWER,
					0
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_LEVEL,
					0
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_XP,
					0
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_MESSAGE,
					""
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION,
					""
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY,
					Utils::getText($player->getName(), "NO_FACTION_TAG")
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_PLAYER_RANK,
					Utils::getText($player->getName(), "NO_FACTION_TAG")
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_MAX_PLAYER,
					0
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_MAX_HOME,
					0
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_MAX_ALLY,
					0
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_MAX_CLAIM,
					0
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_NUMBER_CLAIM,
					0
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_NUMBER_PLAYER,
					0
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_NUMBER_ALLY,
					0
				));
				$ev->call();
				$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
					Ids::HUD_FACTIONMASTER_FACTION_NUMBER_HOME,
					0
				));
				$ev->call();
			}
		}
	}

	public function onClaim(FactionClaimEvent $event) {
		$faction = $event->getFaction();
		if (is_string($faction)) {
			$faction = MainAPI::getFaction($faction);
		}
		if ($faction instanceof FactionEntity) {
			foreach ($faction->getMembers() as $name => $rank) {
				$player = $this->server->getPlayerExact($name);
				if ($player instanceof Player) {
					$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
						Ids::HUD_FACTIONMASTER_FACTION_NUMBER_CLAIM,
						count(MainAPI::getClaimsFaction($faction->getName()))
					));
					$ev->call();
				}
			}
		}
	}

	public function onUnclaim(FactionUnclaimEvent $event) {
		$faction = $event->getFaction();
		if (is_string($faction)) {
			$faction = MainAPI::getFaction($faction);
		}
		if ($faction instanceof FactionEntity) {
			foreach ($faction->getMembers() as $name => $rank) {
				$player = $this->server->getPlayerExact($name);
				if ($player instanceof Player) {
					$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
						Ids::HUD_FACTIONMASTER_FACTION_NUMBER_CLAIM,
						count(MainAPI::getClaimsFaction($faction->getName()))
					));
					$ev->call();
				}
			}
		}
	}

	public function onAllianceCreate(AllianceCreateEvent $event) {
		$faction = $event->getFaction();
		if (is_string($faction)) {
			$faction = MainAPI::getFaction($faction);
		}
		if ($faction instanceof FactionEntity) {
			foreach ($faction->getMembers() as $name => $rank) {
				$player = $this->server->getPlayerExact($name);
				if ($player instanceof Player) {
					$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
						Ids::HUD_FACTIONMASTER_FACTION_NUMBER_ALLY,
						count($faction->getAlly())
					));
					$ev->call();
				}
			}
		}
	}

	public function onAllianceBreak(AllianceBreakEvent $event) {
		$faction = $event->getFaction();
		if (is_string($faction)) {
			$faction = MainAPI::getFaction($faction);
		}
		if ($faction instanceof FactionEntity) {
			foreach ($faction->getMembers() as $name => $rank) {
				$player = $this->server->getPlayerExact($name);
				if ($player instanceof Player) {
					$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
						Ids::HUD_FACTIONMASTER_FACTION_NUMBER_ALLY,
						count($faction->getAlly())
					));
					$ev->call();
				}
			}
		}
	}

	public function onCreateHome(FactionHomeCreateEvent $event) {
		$faction = $event->getFaction();
		if (is_string($faction)) {
			$faction = MainAPI::getFaction($faction);
		}
		if ($faction instanceof FactionEntity) {
			foreach ($faction->getMembers() as $name => $rank) {
				$player = $this->server->getPlayerExact($name);
				if ($player instanceof Player) {
					$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
						Ids::HUD_FACTIONMASTER_FACTION_NUMBER_HOME,
						count(MainAPI::getFactionHomes($faction->getName()))
					));
					$ev->call();
				}
			}
		}
	}

	public function onDeleteHome(FactionHomeDeleteEvent $event) {
		$faction = $event->getFaction();
		if (is_string($faction)) {
			$faction = MainAPI::getFaction($faction);
		}
		if ($faction instanceof FactionEntity) {
			foreach ($faction->getMembers() as $name => $rank) {
				$player = $this->server->getPlayerExact($name);
				if ($player instanceof Player) {
					$ev = new PlayerTagUpdateEvent($player, new ScoreTag(
						Ids::HUD_FACTIONMASTER_FACTION_NUMBER_HOME,
						count(MainAPI::getFactionHomes($faction->getName()))
					));
					$ev->call();
				}
			}
		}
	}
}