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

namespace ShockedPlot7560\FactionMaster\API;

use PDO;
use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\ClaimEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\HomeEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Database\Table\ClaimTable;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\HomeTable;
use ShockedPlot7560\FactionMaster\Database\Table\InvitationTable;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\Event\FactionLevelChangeEvent;
use ShockedPlot7560\FactionMaster\Event\FactionOptionUpdateEvent;
use ShockedPlot7560\FactionMaster\Event\FactionPowerEvent;
use ShockedPlot7560\FactionMaster\Event\FactionXPChangeEvent;
use ShockedPlot7560\FactionMaster\Event\MemberChangeRankEvent;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Reward\RewardInterface;
use ShockedPlot7560\FactionMaster\Task\DatabaseTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function array_merge;
use function explode;
use function floor;
use function json_encode;
use function pow;

class MainAPI {

	/** @var FactionEntity[] */
	public static $factions = [];
	/** @var UserEntity[] */
	public static $users = [];
	/** @var PDO */
	public static $PDO;
	/** @var ClaimEntity[][] */
	public static $claim = [];
	/** @var HomeEntity[][] */
	public static $home = [];
	/** @var InvitationEntity[] */
	public static $invitation = [];
	/** @var string[] */
	public static $languages = [];

	/** @var Main */
	private static $main;

	public static function init(PDO $PDO, Main $main): void {
		self::$main = $main;
		self::$PDO = $PDO;
		self::initClaim();
		self::initFaction();
		self::initHome();
		self::initInvitation();
		self::initUser();
	}

	private static function initInvitation(): void {
		try {
			$query = self::$PDO->prepare("SELECT * FROM " . InvitationTable::TABLE_NAME);
			$query->execute();
			$query->setFetchMode(PDO::FETCH_CLASS, InvitationEntity::class);
			/** @var InvitationEntity[] */
			$result = $query->fetchAll();
			foreach ($result as $invitation) {
				self::$invitation[$invitation->getSenderString() . "|" . $invitation->getReceiverString() . "|" . $invitation->getType()] = $invitation;
			}
		} catch (\PDOException $exception) {
			self::$main->getLogger()->alert("An occured in the server synchronisation, please open an issue on GitHub with this error : " . $exception->getMessage());
		}
	}

	private static function initHome(): void {
		try {
			$query = self::$PDO->prepare("SELECT * FROM " . HomeTable::TABLE_NAME);
			$query->execute();
			$query->setFetchMode(PDO::FETCH_CLASS, HomeEntity::class);
			/** @var HomeEntity[] */
			$result = $query->fetchAll();
			foreach ($result as $home) {
				if (!isset(self::$home[$home->getFactionName()])) {
					self::$home[$home->getFactionName()] = [$home];
				} else {
					self::$home[$home->getFactionName()][] = $home;
				}
			}
		} catch (\PDOException $exception) {
			self::$main->getLogger()->alert("An occured in the server synchronisation, please open an issue on GitHub with this error : " . $exception->getMessage());
		}
	}

	private static function initClaim(): void {
		try {
			$query = self::$PDO->prepare("SELECT * FROM " . ClaimTable::TABLE_NAME);
			$query->execute();
			$query->setFetchMode(PDO::FETCH_CLASS, ClaimEntity::class);
			/** @var ClaimEntity[] */
			$result = $query->fetchAll();
			foreach ($result as $claim) {
				if (!$claim->isActive()) {
					continue;
				}
				if (!isset(self::$claim[$claim->getFactionName()])) {
					self::$claim[$claim->getFactionName()] = [$claim];
				} else {
					self::$claim[$claim->getFactionName()][] = $claim;
				}
			}
		} catch (\PDOException $exception) {
			self::$main->getLogger()->alert("An occured in the server synchronisation, please open an issue on GitHub with this error : " . $exception->getMessage());
		}
	}

	private static function initFaction(): void {
		try {
			$query = self::$PDO->prepare("SELECT * FROM " . FactionTable::TABLE_NAME);
			$query->execute();
			$query->setFetchMode(PDO::FETCH_CLASS, FactionEntity::class);
			/** @var FactionEntity[] */
			$result = $query->fetchAll();
			foreach ($result as $faction) {
				self::$factions[$faction->getName()] = $faction;
			}
		} catch (\PDOException $exception) {
			self::$main->getLogger()->alert("An occured in the server synchronisation, please open an issue on GitHub with this error : " . $exception->getMessage());
		}
	}

	private static function initUser(): void {
		try {
			$query = self::$PDO->prepare("SELECT * FROM " . UserTable::TABLE_NAME);
			$query->execute();
			$query->setFetchMode(PDO::FETCH_CLASS, UserEntity::class);
			/** @var UserEntity[] */
			$result = $query->fetchAll();
			foreach ($result as $user) {
				self::$users[$user->getName()] = $user;
			}
		} catch (\PDOException $exception) {
			self::$main->getLogger()->alert("An occured in the server synchronisation, please open an issue on GitHub with this error : " . $exception->getMessage());
		}
	}

	/**
	 * @return HomeEntity[][]
	 */
	public static function getAllHome(): array {
		return self::$home;
	}

	public static function getFaction(string $factionName): ?FactionEntity {
		return self::$factions[$factionName] ?? null;
	}

	public static function isFactionRegistered(string $factionName): bool {
		return self::getFaction($factionName) instanceof FactionEntity;
	}

	public static function removeFaction(string $factionName): void {
		$faction = MainAPI::$factions[$factionName];
		self::submitDatabaseTask(
			new DatabaseTask(
				"DELETE FROM " . FactionTable::TABLE_NAME . " WHERE name = :name",
				["name" => $factionName],
				function () use ($factionName) {
					unset(MainAPI::$factions[$factionName]);
				}
			)
		);
		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . UserTable::TABLE_NAME . " SET faction = NULL, rank = NULL WHERE faction = :faction",
				['faction' => $factionName],
				function () use ($faction) {
					foreach ($faction->getMembers() as $name => $rank) {
						$user = MainAPI::getUser($name);
						$oldRank = $user->getRank();
						$user->setFactionName(null);
						$user->setRank(null);
						MainAPI::$users[$name] = $user;
						(new MemberChangeRankEvent($faction, $user, $oldRank))->call();
					}
				}
			)
		);
		self::submitDatabaseTask(
			new DatabaseTask(
				"DELETE FROM " . ClaimTable::TABLE_NAME . " WHERE faction = :faction",
				['faction' => $factionName],
				function () use ($factionName) {
					unset(MainAPI::$claim[$factionName]);
				}
			)
		);
		$invitations = array_merge(
			self::getInvitationsBySender($factionName, InvitationEntity::MEMBER_INVITATION),
			self::getInvitationsBySender($factionName, InvitationEntity::ALLIANCE_INVITATION),
			self::getInvitationsByReceiver($factionName, InvitationEntity::ALLIANCE_INVITATION),
			self::getInvitationsByReceiver($factionName, InvitationEntity::MEMBER_INVITATION)
		);
		self::submitDatabaseTask(
			new DatabaseTask(
				"DELETE FROM " . InvitationTable::TABLE_NAME . " WHERE sender = :faction OR receiver = :faction",
				['faction' => $factionName],
				function () use ($invitations) {
					foreach ($invitations as $key => $invitation) {
						unset(MainAPI::$invitation[$invitation->getSenderString() . "|" . $invitation->getReceiverString() . "|" . $invitation->getType()]);
					}
				}
			)
		);
	}

	public static function addFaction(string $factionName, string $ownerName): void {
		self::submitDatabaseTask(
			new DatabaseTask(
				"INSERT INTO " . FactionTable::TABLE_NAME . " (name, level, description, message, members, ally, permissions) VALUES (:name, :level, :description, :message, :members, :ally, :permissions)",
				[
					'name' => $factionName,
					"level" => ConfigManager::getConfig()->get("default-faction-level"),
					"description" => ConfigManager::getConfig()->get("default-faction-description"),
					"message" => ConfigManager::getConfig()->get("default-faction-message"),
					'members' => json_encode([
						$ownerName => Ids::OWNER_ID,
					]),
					'ally' => json_encode([]),
					'permissions' => json_encode([[], [], [], []]),
				],
				function () use ($factionName, $ownerName) {
					self::submitDatabaseTask(
						new DatabaseTask(
							"SELECT * FROM " . FactionTable::TABLE_NAME . " WHERE name = :name",
							["name" => $factionName],
							function ($result) use ($factionName, $ownerName) {
								MainAPI::$factions[$factionName] = $result[0];
								MainAPI::addMember($factionName, $ownerName, Ids::OWNER_ID);
							},
							FactionEntity::class
						)
					);
				}
			)
		);
	}

	/**
	 * @return array Format : [name => rankId]
	 */
	public static function getMembers(string $factionName): array {
		$faction = self::getFaction($factionName);
		if (!$faction instanceof FactionEntity) {
			return [];
		}
		return $faction->getMembers();
	}

	public static function addMember(string $factionName, string $playerName, int $rankId = Ids::RECRUIT_ID) {
		$faction = self::getFaction($factionName);
		if (!$faction instanceof FactionEntity) {
			return false;
		}

		$faction->addMember($playerName, $rankId);
		$user = self::getUser($playerName);
		$user->setFactionName($factionName);
		$user->setRank($rankId);
		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET members = :members WHERE name = :name",
				[
					'members' => json_encode($faction->getMembers()),
					'name' => $factionName,
				],
				function () use ($factionName, $faction) {
					MainAPI::$factions[$factionName] = $faction;
				}
			)
		);
		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . UserTable::TABLE_NAME . " SET `faction` = :faction, `rank` = :rank WHERE `name` = :name",
				[
					'faction' => $factionName,
					'rank' => (int) $rankId,
					'name' => $playerName,
				],
				function () use ($playerName, $user, $faction) {
					MainAPI::$users[$playerName] = $user;
					(new MemberChangeRankEvent($faction, $user, null))->call();
				}
			)
		);
	}

	public static function removeMember(string $factionName, string $playerName) {
		$faction = self::getFaction($factionName);
		if (!$faction instanceof FactionEntity) {
			return false;
		}

		$faction->removeMember($playerName);
		$user = self::getUser($playerName);
		$oldRank = $user->getRank();
		$user->setFactionName(null);
		$user->setRank(null);
		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET members = :members WHERE name = :name",
				[
					'members' => json_encode($faction->getMembers()),
					'name' => $factionName,
				],
				function () use ($factionName, $faction) {
					MainAPI::$factions[$factionName] = $faction;
				}
			)
		);
		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . UserTable::TABLE_NAME . " SET faction = NULL, rank = NULL WHERE name = :name",
				[
					'name' => $playerName,
				],
				function () use ($playerName, $user, $faction, $oldRank) {
					MainAPI::$users[$playerName] = $user;
					(new MemberChangeRankEvent($faction, $user, $oldRank))->call();
				}
			)
		);
	}

	/**
	 * Add a quantity of XP to the faction, *If the total xp of the level are exceeded, it will be set to this limit*
	 */
	public static function addXP(string $factionName, int $xp): void {
		$setXP = $xp;
		$faction = self::getFaction($factionName);
		if (!$faction instanceof FactionEntity) {
			return;
		}

		$level = $faction->getLevel();
		$XPneedLevel = 1000 * pow(1.09, $level);
		$newXP = $faction->getXP() + $xp;
		if ($newXP > $XPneedLevel) {
			$xp = $newXP - $XPneedLevel;
			$level++;
		} else {
			$xp = $newXP;
		}

		$faction->setLevel($level);
		$faction->setXp($xp);
		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET xp = :xp, level = :level WHERE name = :name",
				[
					'xp' => $xp,
					'level' => $level,
					'name' => $factionName,
				],
				function () use ($factionName, $faction, $setXP) {
					MainAPI::$factions[$factionName] = $faction;
					(new FactionXPChangeEvent($faction, $setXP))->call();
				}
			)
		);
	}

	/**
	 * Change the faction level and reset xp to 0
	 */
	public static function changeLevel(string $factionName, int $level): void {
		$faction = self::getFaction($factionName);
		if (!$faction instanceof FactionEntity) {
			return;
		}

		$faction->setLevel($level);
		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET level = level + :level, xp = 0 WHERE name = :name",
				[
					'level' => $level,
					'name' => $factionName,
				],
				function () use ($factionName, $faction) {
					MainAPI::$factions[$factionName] = $faction;
					(new FactionLevelChangeEvent($faction))->call();
				}
			)
		);
	}

	/**
	 * @param int $power The power to change, it allow negative integer to substract
	 */
	public static function changePower(string $factionName, int $power) {
		$faction = self::getFaction($factionName);
		if (!$faction instanceof FactionEntity) {
			return false;
		}

		$actualPower = $faction->getPower();
		if (($totalPower = $actualPower + $power) < 0) {
			$totalPower = 0;
		}

		$faction->setPower($totalPower);
		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET power = :power WHERE name = :name",
				[
					'power' => $totalPower,
					'name' => $factionName,
				],
				function () use ($factionName, $faction, $power) {
					MainAPI::$factions[$factionName] = $faction;
					(new FactionPowerEvent($faction, $power))->call();
				}
			)
		);
	}

	public static function getFactionOfPlayer(string $playerName): ?FactionEntity {
		$user = self::getUser($playerName);
		if (!$user instanceof UserEntity) {
			return null;
		}
		return $user->getFactionEntity();
	}

	public static function isInFaction(string $playerName): bool {
		if (self::getFactionOfPlayer($playerName) instanceof FactionEntity) {
			return true;
		}
		return false;
	}

	public static function sameFaction(string $playerName1, string $playerName2): bool {
		$player1 = self::getFactionOfPlayer($playerName1);
		$player2 = self::getFactionOfPlayer($playerName2);
		return ($player1 === $player2) && ($player1 !== null);
	}

	public static function getUser(string $playerName): ?UserEntity {
		return self::$users[$playerName] ?? null;
	}

	public static function userIsRegister(string $playerName): bool {
		if (self::getUser($playerName) instanceof UserEntity) {
			return true;
		}
		return false;
	}

	public static function isAlly(string $factionName1, string $factionName2): bool {
		$f2 = self::getFaction($factionName2);
		if (!$f2 instanceof FactionEntity) {
			return false;
		}
		return $f2->isAlly($factionName1);
	}

	public static function setAlly(string $factionName1, string $factionName2): void {
		$faction1 = self::getFaction($factionName1);
		$faction2 = self::getFaction($factionName2);
		if ($faction1->haveMaxAlly() || $faction2->haveMaxAlly()) {
			return;
		}

		$faction1->addAlly($factionName2);
		$faction2->addAlly($factionName1);

		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET ally = :ally WHERE name = :name",
				[
					'ally' => json_encode($faction1->getAlly()),
					'name' => $factionName1,
				],
				function () use ($factionName1, $faction1) {
					MainAPI::$factions[$factionName1] = $faction1;
				}
			)
		);
		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET ally = :ally WHERE name = :name",
				[
					'ally' => json_encode($faction2->getAlly()),
					'name' => $factionName2,
				],
				function () use ($factionName2, $faction2) {
					MainAPI::$factions[$factionName2] = $faction2;
				}
			)
		);
	}

	public static function removeAlly(string $faction1, string $faction2): void {
		if (!self::isAlly($faction1, $faction2)) {
			return;
		}

		$faction1 = self::getFaction($faction1);
		$faction2 = self::getFaction($faction2);
		if (!$faction1 instanceof FactionEntity || !$faction2 instanceof FactionEntity) {
			return;
		}
		$array = [$faction1, $faction2];
		foreach ($array as $key => $faction) {
			switch ($key) {
				case 0:
					$faction->removeAlly($array[1]->getName());
					break;
				case 1:
					$faction->removeAlly($array[0]->getName());
					break;
			}
			self::submitDatabaseTask(
				new DatabaseTask(
					"UPDATE " . FactionTable::TABLE_NAME . " SET ally = :ally WHERE name = :name",
					[
						'ally' => json_encode($faction->getAlly()),
						'name' => $faction->getName(),
					],
					function () use ($faction) {
						MainAPI::$factions[$faction->getName()] = $faction;
					}
				)
			);
		}
	}

	public static function changeRank(string $playerName, int $rank) {
		$faction = self::getFactionOfPlayer($playerName);
		if (!$faction instanceof FactionEntity) {
			return false;
		}

		$faction->setMemberRank($playerName, $rank);

		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET members = :members WHERE name = :name",
				[
					'members' => json_encode($faction->getMembers()),
					'name' => $faction->getName(),
				],
				function () use ($faction) {
					MainAPI::$factions[$faction->getName()] = $faction;
				}
			)
		);
		$user = self::getUser($playerName);
		$oldRank = $user->getRank();
		$user->setFactionName(null);
		$user->setRank(null);
		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . UserTable::TABLE_NAME . " SET `rank` = :rank WHERE `name` = :name",
				[
					'rank' => $rank,
					'name' => $playerName,
				],
				function () use ($user, $faction, $oldRank) {
					MainAPI::$users[$user->getName()] = $user;
					(new MemberChangeRankEvent($faction, $user, $oldRank))->call();
				}
			)
		);
	}

	public static function changeVisibility(string $factionName, int $visibilityType) {
		$faction = self::getFaction($factionName);
		if (!$faction instanceof FactionEntity) {
			return false;
		}

		$faction->setVisibility($visibilityType);

		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET visibility = :visibility WHERE name = :name",
				[
					'visibility' => $visibilityType,
					'name' => $factionName,
				],
				function () use ($faction) {
					MainAPI::$factions[$faction->getName()] = $faction;
				}
			)
		);
	}

	public static function changeMessage(string $factionName, string $message) {
		$faction = self::getFaction($factionName);
		if (!$faction instanceof FactionEntity) {
			return false;
		}

		$faction->setMessage($message);

		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET message = :message WHERE name = :name",
				[
					'message' => $message,
					'name' => $factionName,
				],
				function () use ($faction) {
					MainAPI::$factions[$faction->getName()] = $faction;
				}
			)
		);
	}

	public static function changeDescription(string $factionName, string $description) {
		$faction = self::getFaction($factionName);
		if (!$faction instanceof FactionEntity) {
			return false;
		}

		$faction->setDescription($description);

		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET description = :description WHERE name = :name",
				[
					'description' => $description,
					'name' => $factionName,
				],
				function () use ($faction) {
					MainAPI::$factions[$faction->getName()] = $faction;
				}
			)
		);
	}

	public static function getMemberPermission(string $playerName): ?array {
		$faction = self::getFactionOfPlayer($playerName);
		if (!$faction instanceof FactionEntity) {
			return [];
		}

		$user = self::getUser($playerName);
		return $faction->getPermissions()[$user->getRank()];
	}

	public static function makeInvitation(string $sender, string $receiver, string $type): void {
		self::submitDatabaseTask(
			new DatabaseTask(
				"INSERT INTO " . InvitationTable::TABLE_NAME . " (sender, receiver, type) VALUES (:sender, :receiver, :type)",
				[
					'sender' => $sender,
					'receiver' => $receiver,
					'type' => $type,
				],
				function () use ($sender, $receiver, $type) {
					self::submitDatabaseTask(
						new DatabaseTask(
							"SELECT * FROM " . InvitationTable::TABLE_NAME . " WHERE sender = :sender AND receiver = :receiver AND type = :type",
							[
								'sender' => $sender,
								'receiver' => $receiver,
								'type' => $type,
							],
							function ($result) use ($sender, $receiver, $type) {
								MainAPI::$invitation[$sender . "|" . $receiver . "|" . $type] = $result[0];
							},
							InvitationEntity::class
						));
				}
			)
		);
	}

	public static function areInInvitation(string $sender, string $receiver, string $type): bool {
		$invitation = self::$invitation[$sender . "|" . $receiver . "|" . $type] ?? null;
		if (!$invitation instanceof InvitationEntity) {
			return false;
		}
		return $invitation->getType() === $type;
	}

	public static function removeInvitation(string $sender, string $receiver, string $type): void {
		self::submitDatabaseTask(
			new DatabaseTask(
				"DELETE FROM " . InvitationTable::TABLE_NAME . " WHERE sender = :sender AND receiver = :receiver AND type = :type",
				[
					"sender" => $sender,
					"receiver" => $receiver,
					"type" => $type,
				],
				function () use ($sender, $receiver, $type) {
					unset(self::$invitation[$sender . "|" . $receiver . "|" . $type]);
				}
			)
		);
	}

	/**
	 * @return InvitationEntity[]
	 */
	public static function getInvitationsBySender(string $sender, string $type): array {
		$inv = [];
		foreach (self::$invitation as $key => $invitation) {
			$array = explode("|", $key);
			if ($array[0] === $sender && $invitation->getType() === $type && $array[2] === $type) {
				$inv[] = $invitation;
			}
		}
		return $inv;
	}

	/**
	 * @return InvitationEntity[]
	 */
	public static function getInvitationsByReceiver(string $receiver, string $type): array {
		$inv = [];
		foreach (self::$invitation as $key => $invitation) {
			$array = explode("|", $key);
			if ($array[1] === $receiver && $invitation->getType() === $type && $array[2] === $type) {
				$inv[] = $invitation;
			}
		}
		return $inv;
	}

	/**
	 * @param array $permissions The permissions in this format : [[1 => true],[],[],[]]
	 *                          where each index of sub array are a permission's ids
	 */
	public static function updatePermissionFaction(string $factionName, array $permissions) {
		$faction = self::getFaction($factionName);
		if (!$faction instanceof FactionEntity) {
			return false;
		}

		$faction->setPermissions($permissions);

		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET permissions = :permissions WHERE name = :name",
				[
					'permissions' => json_encode($permissions),
					'name' => $factionName,
				],
				function () use ($faction) {
					MainAPI::$factions[$faction->getName()] = $faction;
				}
			)
		);
	}

	public static function addUser(string $playerName): void {
		self::submitDatabaseTask(
			new DatabaseTask(
				"INSERT INTO " . UserTable::TABLE_NAME . " (`name`) VALUES (:user)",
				[
					'user' => $playerName,
				],
				function () use ($playerName) {
					self::submitDatabaseTask(
						new DatabaseTask(
							"SELECT * FROM " . UserTable::TABLE_NAME . " WHERE name = :name",
							[
								'name' => $playerName,
							],
							function ($result) use ($playerName) {
								MainAPI::$users[$playerName] = $result[0];
							},
							UserEntity::class
						)
					);
				}
			)
		);
	}

	/**
	 * @return ClaimEntity[][]
	 */
	public static function getAllClaim(): array {
		return self::$claim;
	}

	public static function addClaim(Player $player, string $factionName, ?int $flag = null): void {
		$z = $player->getPosition()->getFloorZ() >> 4;
		$x = $player->getPosition()->getFloorX() >> 4;
		$chunk = $player->getWorld()->getChunk($x, $z);
		$world = $player->getWorld()->getDisplayName();
		self::submitDatabaseTask(
			new DatabaseTask(
				"INSERT INTO " . ClaimTable::TABLE_NAME . " (x, z, world, faction, server, flags) VALUES (:x, :z, :world, :faction, :server, :flag)",
				[
					"x" => $x,
					"z" => $z,
					"world" => $world,
					"faction" => $factionName,
					"server" => self::$main->getServer()->getIp(),
					"flag" => $flag
				],
				function () use ($factionName, $world, $x, $z) {
					self::submitDatabaseTask(
						new DatabaseTask(
							"SELECT * FROM " . ClaimTable::TABLE_NAME . " WHERE x = :x AND z = :z AND world = :world AND faction = :faction",
							[
								'x' => $x,
								'z' => $z,
								'faction' => $factionName,
								'world' => $world,
							],
							function ($result) use ($factionName) {
								MainAPI::$claim[$factionName][] = $result[0];
							},
							ClaimEntity::class
						)
					);
				}
			)
		);
	}

	public static function isClaim(string $world, int $x, int $z): bool {
		return self::getFactionClaim($world, $x, $z) instanceof ClaimEntity;
	}

	public static function getFactionClaim(string $world, int $x, int $z): ?ClaimEntity {
		foreach (self::$claim as $factionClaim) {
			foreach ($factionClaim as $claim) {
				if ($claim->getX() == $x && $claim->getZ() == $z && $claim->getLevelName() == $world) {
					return $claim;
				}
			}
		}
		return null;
	}

	public static function removeClaim(Player $player, string $factionName): void {
		$z = floor($player->getPosition()->getFloorX()/16);
		$x = floor($player->getPosition()->getFloorZ()/16);
		$chunk = $player->getWorld()->getChunk($x, $z);
		$world = $player->getWorld()->getDisplayName();
		$claim = self::getFactionClaim($world, $x, $z);
		if (!$claim instanceof ClaimEntity) {
			return;
		}

		self::submitDatabaseTask(
			new DatabaseTask(
				"DELETE FROM " . ClaimTable::TABLE_NAME . " WHERE x = :x AND z = :z AND world = :world AND faction = :faction",
				[
					"x" => $x,
					"z" => $z,
					"world" => $world,
					"faction" => $factionName,
				],
				function () use ($factionName, $x, $z, $world) {
					foreach (MainAPI::$claim[$factionName] as $key => $claim) {
						if ($claim->getX() == $x && $claim->getZ() == $z && $claim->getLevelName() == $world) {
							unset(MainAPI::$claim[$factionName][$key]);
						}
					}
				}
			)
		);
	}

	/**
	 * @return ClaimEntity[]
	 */
	public static function getClaimsFaction(string $factionName): array {
		return self::$claim[$factionName] ?? [];
	}

	/**
	 * @return HomeEntity[]
	 */
	public static function getFactionHomes(string $factionName): array {
		return self::$home[$factionName] ?? [];
	}

	public static function getFactionHome(string $factionName, string $name): ?HomeEntity {
		return self::$home[$factionName][$name] ?? null;
	}

	public static function removeHome(string $factionName, string $name) {
		if (!isset(self::$home[$factionName][$name])) {
			return false;
		}

		self::submitDatabaseTask(
			new DatabaseTask(
				"DELETE FROM " . HomeTable::TABLE_NAME . " WHERE faction = :faction AND name = :name",
				[
					"faction" => $factionName,
					"name" => $name,
				],
				function () use ($factionName, $name) {
					unset(MainAPI::$home[$factionName][$name]);
				}
			)
		);
	}

	public static function addHome(Player $player, string $factionName, string $name): void {
		self::submitDatabaseTask(
			new DatabaseTask(
				"INSERT INTO " . HomeTable::TABLE_NAME . " (x, y, z, world, yaw, pitch, faction, name, server) VALUES (:x, :y, :z, :world, :yaw, :pitch, :faction, :name, :server)",
				[
					"x" => $player->getPosition()->getX(),
					"z" => $player->getPosition()->getZ(),
					"y" => $player->getPosition()->getY(),
					"yaw" => $player->getLocation()->getYaw(),
					"pitch" => $player->getLocation()->getPitch(),
					"world" => $player->getWorld()->getDisplayName(),
					"faction" => $factionName,
					"name" => $name,
					"server" => self::$main->getServer()->getIp()
				],
				function () use ($player, $factionName, $name) {
					self::submitDatabaseTask(
						new DatabaseTask(
							"SELECT * FROM " . HomeTable::TABLE_NAME . " WHERE x = :x AND z = :z AND y = :y AND world = :world AND yaw = :yaw AND pitch = :pitch AND faction = :faction AND name = :name",
							[
								"x" => $player->getPosition()->getX(),
								"z" => $player->getPosition()->getZ(),
								"y" => $player->getPosition()->getY(),
								"yaw" => $player->getLocation()->getYaw(),
								"pitch" => $player->getLocation()->getPitch(),
								"world" => $player->getWorld()->getDisplayName(),
								"faction" => $factionName,
								"name" => $name,
							],
							function ($result) use ($factionName, $name) {
								MainAPI::$home[$factionName][$name] = $result[0];
							},
							HomeEntity::class
						)
					);
				}
			)
		);
	}

	public static function getPlayerLang(string $playerName): string {
		return self::$languages[$playerName] ?? Utils::getConfigLang("default-language");
	}

	public static function changeLanguage(string $playerName, string $slug): void {
		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . UserTable::TABLE_NAME . " SET language = :lang WHERE name = :name",
				[
					'lang' => $slug,
					'name' => $playerName,
				],
				function () use ($playerName, $slug) {
					MainAPI::$languages[$playerName] = $slug;
				}
			)
		);
	}

	public static function getLevelReward(int $level): ?RewardInterface {
		$data = self::getLevelRewardData($level);
		$reward = RewardFactory::get($data['type']);
		if ($reward !== null) {
			$reward->setValue($data['value']);
		}

		return $reward;
	}

	public static function getLevelRewardData(int $level): array {
		return ConfigManager::getLevelConfig()->__get("levels")[$level - 2];
	}

	public static function updateFactionOption(string $factionName, string $option, int $value) {
		$faction = self::getFaction($factionName);
		$oldFac = clone $faction;
		if (!$faction instanceof FactionEntity) {
			return false;
		}

		$faction->$value = $value;
		self::submitDatabaseTask(
			new DatabaseTask(
				"UPDATE " . FactionTable::TABLE_NAME . " SET " . $option . " = " . $option . " + :option WHERE name = :name",
				[
					'option' => $value,
					'name' => $factionName,
				],
				function () use ($faction, $oldFac, $option, $value) {
					MainAPI::$factions[$faction->getName()] = $faction;
					(new FactionOptionUpdateEvent($oldFac, $value, $option))->call();
				}
			)
		);
	}

	private static function submitDatabaseTask(DatabaseTask $task): void {
		self::$main->getServer()->getAsyncPool()->submitTask($task);
	}
}