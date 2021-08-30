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

use InvalidArgumentException;
use PDO;
use PDOException;
use pocketmine\Player;
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
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Reward\RewardInterface;
use ShockedPlot7560\FactionMaster\Task\DatabaseTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MainAPI {

    /** @var FactionEntity[] */
    public static $factions = [];
    /** @var UserEntity[] */
    public static $users = [];
    /** @var \PDO */
    public static $PDO;
    /** @var ClaimEntity[][] */
    public static $claim = [];
    /** @var HomeEntity[][] */
    public static $home = [];
    /** @var InvitationEntity[] */
    public static $invitation = [];
    /** @var string[] */
    public static $languages = [];

    public static function init(PDO $PDO) {
        self::$PDO = $PDO;
        self::initClaim();
        self::initFaction();
        self::initHome();
        self::initInvitation();
        self::initUser();
    }

    private static function initInvitation(): bool {
        try {
            $query = self::$PDO->prepare("SELECT * FROM " . InvitationTable::TABLE_NAME);
            $query->execute();
            $query->setFetchMode(PDO::FETCH_CLASS, InvitationEntity::class);
            /** @var InvitationEntity[] */
            $result = $query->fetchAll();
            foreach ($result as $invitation) {
                self::$invitation[$invitation->sender . "|" .$invitation->receiver] = $invitation;
            }
            return true;
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    private static function initHome(): bool {
        try {
            $query = self::$PDO->prepare("SELECT * FROM " . HomeTable::TABLE_NAME);
            $query->execute();
            $query->setFetchMode(PDO::FETCH_CLASS, HomeEntity::class);
            $result = $query->fetchAll();
            foreach ($result as $home) {
                if (!isset(self::$home[$home->faction])) {
                    self::$home[$home->faction] = [$home];
                }else{
                    self::$home[$home->faction][] = $home;
                }
            }
            return true;
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    private static function initClaim(): bool {
        try {
            $query = self::$PDO->prepare("SELECT * FROM " . ClaimTable::TABLE_NAME);
            $query->execute();
            $query->setFetchMode(PDO::FETCH_CLASS, ClaimEntity::class);
            /** @var ClaimEntity[] */
            $result = $query->fetchAll();
            foreach ($result as $claim) {
                if (!isset(self::$claim[$claim->faction])) {
                    self::$claim[$claim->faction] = [$claim];
                }else{
                    self::$claim[$claim->faction][] = $claim;
                }
            }
            return true;
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    private static function initFaction(): bool {
        try {
            $query = self::$PDO->prepare("SELECT * FROM " . FactionTable::TABLE_NAME);
            $query->execute();
            $query->setFetchMode(PDO::FETCH_CLASS, FactionEntity::class);
            foreach ($query->fetchAll() as $faction) {
                self::$factions[$faction->name] = $faction;
            }
            return true;
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    private static function initUser(): bool {
        try {
            $query = self::$PDO->prepare("SELECT * FROM " . UserTable::TABLE_NAME);
            $query->execute();
            $query->setFetchMode(PDO::FETCH_CLASS, UserEntity::class);
            foreach ($query->fetchAll() as $user) {
                self::$users[$user->name] = $user;
            }
            return true;
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Return all the home register
     * @return HomeEntity[][]
     */
    public static function getAllHome() : array {
        return self::$home;
    }

    public static function getFaction(string $factionName) : ?FactionEntity {
        return self::$factions[$factionName] ?? null;
    }

    public static function isFactionRegistered(string $factionName) : bool {
        return self::getFaction($factionName) instanceof FactionEntity;
    }

    public static function removeFaction(string $factionName) {
        $faction = MainAPI::$factions[$factionName];
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "DELETE FROM " . FactionTable::SLUG . " WHERE name = :name", 
                [ "name" => $factionName ],
                function () use ($factionName) {
                    unset(MainAPI::$factions[$factionName]);
                }
        ));
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . UserTable::TABLE_NAME . " SET faction = NULL, rank = NULL WHERE faction = :faction", 
                [ 'faction' => $factionName ],
                function () use ($faction) {
                    foreach ($faction->members as $name => $rank) {
                        $user = MainAPI::getUser($name);
                        $user->faction = null;
                        $user->rank = null;
                        MainAPI::$users[$name] = $user;
                    }
                }
        ));
    }

    public static function addFaction(string $factionName, string $ownerName) {
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "INSERT INTO " . FactionTable::SLUG . " (name, members, ally, permissions) VALUES (:name, :members, :ally, :permissions)", 
                [
                    'name' => $factionName,
                    'members' => \base64_encode(\serialize([
                        $ownerName => Ids::OWNER_ID
                    ])),
                    'ally' => \base64_encode(\serialize([])),
                    'permissions' => \base64_encode(\serialize([[],[],[],[]]))
                ],
                function () use ($factionName, $ownerName) {
                    Main::getInstance()->getServer()->getAsyncPool()->submitTask(
                        new DatabaseTask(
                            "SELECT * FROM " . FactionTable::TABLE_NAME . " WHERE name = :name", 
                            [ "name" => $factionName ],
                            function ($result) use ($factionName, $ownerName) {
                                MainAPI::$factions[$factionName] = $result[0];
                                MainAPI::addMember($factionName, $ownerName, Ids::OWNER_ID);
                            },
                            FactionEntity::class
                    ));
                }
        ));
    }

    /**
     * @return array Format : [name => rankId]
     */
    public static function getMembers(string $factionName) : array {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return [];
        return $Faction->members;
    }

    public static function addMember(string $factionName, string $playerName, int $rankId = Ids::RECRUIT_ID) {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return false;
        $Faction->members[$playerName] = $rankId;
        $user = self::getUser($playerName);
        $user->faction = $factionName;
        $user->rank = $rankId;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . FactionTable::TABLE_NAME . " SET members = :members WHERE name = :name", 
                [
                    'members' => \base64_encode(\serialize($Faction->members)),
                    'name' => $factionName
                ],
                function () use ($factionName, $Faction) {
                    MainAPI::$factions[$factionName] = $Faction;
                }
        ));
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . UserTable::TABLE_NAME . " SET faction = :faction, rank = :rank WHERE name = :name", 
                [
                    'faction' => $factionName,
                    'rank' => $rankId,
                    'name' => $playerName
                ],
                function () use ($playerName, $user) {
                    MainAPI::$users[$playerName] = $user;
                }
        ));
    }

    public static function removeMember(string $factionName, string $playerName) {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return false;
        unset($Faction->members[$playerName]);
        $user = self::getUser($playerName);
        $user->faction = null;
        $user->rank = null;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . FactionTable::TABLE_NAME . " SET members = :members WHERE name = :name", 
                [
                    'members' => \base64_encode(\serialize($Faction->members)),
                    'name' => $factionName
                ],
                function () use ($factionName, $Faction) {
                    MainAPI::$factions[$factionName] = $Faction;
                }
        ));
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . UserTable::TABLE_NAME . " SET faction = NULL, rank = NULL WHERE name = :name", 
                [
                    'name' => $playerName
                ],
                function () use ($playerName, $user) {
                    MainAPI::$users[$playerName] = $user;
                }
        ));
    }

    /**
     * Add a quantity of XP to the faction, *If the total xp of the level are exceeded, it will be set to this limit*
     */
    public static function addXP(string $factionName, int $xp) {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return;

        $level = $Faction->level;
        $XPneedLevel = 1000*pow(1.09, $level);
        $newXP = $Faction->xp + $xp;
        if ($newXP > $XPneedLevel) {
            $xp = $newXP - $XPneedLevel;
            $level++;
        }else{
            $xp = $newXP;
        }

        $Faction->level = $level;
        $Faction->xp = $xp;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " .FactionTable::TABLE_NAME . " SET xp = :xp, level = :level WHERE name = :name", 
                [ 
                    'xp' => $xp,
                    'level' => $level,
                    'name' => $factionName
                ],
                function () use ($factionName, $Faction) {
                    MainAPI::$factions[$factionName] = $Faction;
                }
        ));
    }

    /**
     * Change the faction level and reset xp to 0
     */
    public static function changeLevel(string $factionName, int $level) {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return;
        $Faction->level = $level;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . FactionTable::TABLE_NAME . " SET level = level + :level, xp = 0 WHERE name = :name", 
                [
                    'level' => $level,
                    'name' => $factionName
                ],
                function () use ($factionName, $Faction) {
                    MainAPI::$factions[$factionName] = $Faction;
                }
        ));
    }

    /**
     * @param int $power The power to change, it allow negative integer to substract
     */
    public static function changePower(string $factionName, int $power) {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return false;
        $actualPower = $Faction->power;
        if (($totalPower = $actualPower + $power) < 0) $totalPower = 0;

        $Faction->power = $totalPower;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . FactionTable::TABLE_NAME . " SET power = :power WHERE name = :name", 
                [
                    'power' => $totalPower,
                    'name' => $factionName
                ],
                function () use ($factionName, $Faction) {
                    MainAPI::$factions[$factionName] = $Faction;
                }
        ));
    }

    /**
     * @return FactionEntity|null
     */
    public static function getFactionOfPlayer(string $playerName) : ?FactionEntity {
        $user = self::getUser($playerName);
        if (!$user instanceof UserEntity || $user->faction === null) return null;
        return self::getFaction($user->faction);
    }

    public static function isInFaction(string $playerName) : bool {
        if (self::getFactionOfPlayer($playerName) instanceof FactionEntity) {
            return true;
        }
        return false;
    }

    public static function sameFaction(string $playerName1, string $playerName2) : bool {
        $player1 = self::getFactionOfPlayer($playerName1);
        $player2 = self::getFactionOfPlayer($playerName2);
        return ($player1 === $player2) && ($player1 !== null);
    }

    public static function getUser(string $playerName) : ?UserEntity {
        return self::$users[$playerName] ?? null;        
    }

    public static function userIsRegister(string $playerName) : bool {
        if (self::getUser($playerName) instanceof UserEntity) {
            return true;
        }
        return false;
    }

    public static function isAlly(string $factionName1, string $factionName2) : bool {
        $f2 = self::getFaction($factionName2);
        if (!$f2 instanceof FactionEntity) return false;
        return in_array($factionName1, $f2->ally);
    }

    public static function setAlly(string $factionName1, string $factionName2) {
        $Faction1 = self::getFaction($factionName1);
        $Faction2 = self::getFaction($factionName2);
        if (count($Faction1->ally) >= (int) $Faction1->max_ally || count($Faction2->ally) >= (int) $Faction2->max_ally) {
            return;
        }
        $Faction1->ally[] = $factionName2;
        $Faction2->ally[] = $factionName1;

        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . FactionTable::TABLE_NAME . " SET ally = :ally WHERE name = :name", 
                [
                    'ally' => \base64_encode(\serialize($Faction1->ally)),
                    'name' => $factionName1
                ],
                function () use ($factionName1, $Faction1) {
                    MainAPI::$factions[$factionName1] = $Faction1;
                }
        ));
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . FactionTable::TABLE_NAME . " SET ally = :ally WHERE name = :name", 
                [
                    'ally' => \base64_encode(\serialize($Faction2->ally)),
                    'name' => $factionName2
                ],
                function () use ($factionName2, $Faction2) {
                    MainAPI::$factions[$factionName2] = $Faction2;
                }
        ));
    }

    public static function removeAlly(string $faction1, string $faction2) {
        if (!self::isAlly($faction1, $faction2)) return;

        $Faction1 = self::getFaction($faction1);
        $Faction2 = self::getFaction($faction2);
        if (!$Faction1 instanceof FactionEntity || !$Faction2 instanceof FactionEntity) return;
        
        foreach ([$Faction1, $Faction2] as $key => $Faction) {
            foreach ($Faction->ally as $key => $alliance) {
                if (in_array($alliance, [$faction1, $faction2])) {
                    unset($Faction->ally[$key]);
                }
            }
            Main::getInstance()->getServer()->getAsyncPool()->submitTask(
                new DatabaseTask(
                    "UPDATE " . FactionTable::TABLE_NAME . " SET ally = :ally WHERE name = :name", 
                    [
                        'ally' => \base64_encode(\serialize($Faction->ally)),
                        'name' => $Faction->name
                    ],
                    function () use ($Faction) {
                        MainAPI::$factions[$Faction->name] = $Faction;
                    }
            ));
        }
    }

    public static function changeRank(string $playerName, int $rank) {
        $Faction = self::getFactionOfPlayer($playerName);
        if (!$Faction instanceof FactionEntity) return false;

        $Faction->members[$playerName] = $rank;

        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . FactionTable::TABLE_NAME . " SET members = :members WHERE name = :name", 
                [
                    'members' => \base64_encode(\serialize($Faction->members)),
                    'name' => $Faction->name
                ],
                function () use ($Faction) {
                    MainAPI::$factions[$Faction->name] = $Faction;
                }
        ));
        $user = self::getUser($playerName);
        $user->faction = null;
        $user->rank = null;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . UserTable::TABLE_NAME . " SET rank = :rank WHERE name = :name", 
                [
                    'rank' => $rank,
                    'name' => $playerName
                ],
                function () use ($user) {
                    MainAPI::$users[$user->name] = $user;
                }
        ));
    }

    public static function changeVisibility(string $factionName, int $visibilityType) {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return false;
        $Faction->visibility = $visibilityType;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . FactionTable::TABLE_NAME . " SET visibility = :visibility WHERE name = :name", 
                [
                    'visibility' => $visibilityType,
                    'name' => $factionName
                ],
                function () use ($Faction) {
                    MainAPI::$factions[$Faction->name] = $Faction;
                }
        ));
    }

    public static function changeMessage(string $factionName, string $message) {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return false;
        $Faction->messageFaction = $message;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . FactionTable::TABLE_NAME . " SET messageFaction = :message WHERE name = :name", 
                [
                    'message' => $message,
                    'name' => $factionName
                ],
                function () use ($Faction) {
                    MainAPI::$factions[$Faction->name] = $Faction;
                }
        ));
    }

    public static function changeDescription(string $factionName, string $description) {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return false;
        $Faction->description = $description;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . FactionTable::TABLE_NAME . " SET description = :description WHERE name = :name", 
                [
                    'description' => $description,
                    'name' => $factionName
                ],
                function () use ($Faction) {
                    MainAPI::$factions[$Faction->name] = $Faction;
                }
        ));
    }

    /**
     * Return the permission's array of the target player
     * @return null|string[]
     */
    public static function getMemberPermission(string $playerName) : ?array {
        $Faction = self::getFactionOfPlayer($playerName);
        if (!$Faction instanceof FactionEntity) return [];
        $user = self::getUser($playerName);
        return $Faction->permissions[$user->rank];
    }

    /**
     * Create an invitation between two entity
     */
    public static function makeInvitation(string $sender, string $receiver, string $type) {
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "INSERT INTO " .InvitationTable::TABLE_NAME . " (sender, receiver, type) VALUES (:sender, :receiver, :type)", 
                [
                    'sender' => $sender,
                    'receiver' => $receiver,
                    'type' => $type
                ],
                function () use ($sender, $receiver, $type) {
                    Main::getInstance()->getServer()->getAsyncPool()->submitTask(
                        new DatabaseTask(
                            "SELECT * FROM " . InvitationTable::TABLE_NAME . " WHERE sender = :sender AND receiver = :receiver AND type = :type", 
                            [
                                'sender' => $sender,
                                'receiver' => $receiver,
                                'type' => $type
                            ],
                            function ($result) use ($sender, $receiver) {
                                MainAPI::$invitation[$sender . "|" . $receiver] = $result[0];
                            },
                            InvitationEntity::class
                    ));
                }
        ));
    }

    public static function areInInvitation(string $sender, string $receiver, string $type) : bool {
        $invitation = self::$invitation[$sender . "|" . $receiver] ?? null;
        if (!$invitation instanceof InvitationEntity) return false;
        return $invitation->type === $type;
    }

    public static function removeInvitation(string $sender, string $receiver, string $type) {
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "DELETE FROM " . InvitationTable::TABLE_NAME . " WHERE sender = :sender AND receiver = :receiver AND type = :type", 
                [
                    "sender" => $sender,
                    "receiver" => $receiver,
                    "type" => $type
                ],
                function () use ($sender, $receiver) {
                    unset(self::$invitation[$sender . "|" . $receiver]);
                }
        ));
    }

    /**
     * @return InvitationEntity[]
     */
    public static function getInvitationsBySender(string $sender, string $type) : array {
        $inv = [];
        foreach (self::$invitation as $key => $invitation) {
            $array = explode("|", $key);
            if ($array[0] === $sender && $invitation->type === $type) $inv[] = $invitation; 
        }
        return $inv;
    }

    /**
     * @return InvitationEntity[]
     */
    public static function getInvitationsByReceiver(string $receiver, string $type) : array {
        $inv = [];
        foreach (self::$invitation as $key => $invitation) {
            $array = explode("|", $key);
            if ($array[1] === $receiver && $invitation->type === $type) $inv[] = $invitation; 
        }
        return $inv;
    }

    /**
     * @param array $permissions The permissions in this format : [[1 => true],[],[],[]]
     *                          where each index of sub array are a permission's ids define in Ids interface
     */
    public static function updatePermissionFaction(string $factionName, array $permissions) {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return false;
        $Faction->permissions = $permissions;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . FactionTable::TABLE_NAME . " SET permissions = :permissions WHERE name = :name", 
                [
                    'permissions' => \base64_encode(\serialize($permissions)),
                    'name' => $factionName
                ],
                function () use ($Faction) {
                    MainAPI::$factions[$Faction->name] = $Faction;
                }
        ));
    }

    public static function addUser(string $playerName) {
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "INSERT INTO " . UserTable::TABLE_NAME . " (`name`) VALUES (:user)", 
                [
                    'user' => $playerName
                ],
                function () use ($playerName) {
                    Main::getInstance()->getServer()->getAsyncPool()->submitTask(
                        new DatabaseTask(
                            "SELECT * FROM " . UserTable::TABLE_NAME . " WHERE name = :name", 
                            [
                                'name' => $playerName
                            ],
                            function ($result) use ($playerName) {
                                MainAPI::$users[$playerName] = $result[0];
                            },
                            UserEntity::class
                    ));
                }
        ));
    }

    /**
     * @return FactionEntity[]
     */
    public static function getTopFaction() : array {
        try {
            $query = self::$PDO->prepare("SELECT * FROM " . FactionTable::TABLE_NAME . " ORDER BY level DESC, xp DESC, power DESC LIMIT 10");
            $query->execute();
            $query->setFetchMode(PDO::FETCH_CLASS, FactionEntity::class);
            return $query->fetchAll();
        } catch (\PDOException $Exception) {
            return [];
        }
    }

    /**
     * @return ClaimEntity[][]
     */
    public static function getAllClaim() : array {
        return self::$claim;
    }

    public static function addClaim(Player $player, string $factionName) {
        $Chunk = $player->getLevel()->getChunkAtPosition($player);
        $X = $Chunk->getX();
        $Z = $Chunk->getZ();
        $World = $player->getLevel()->getName();
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "INSERT INTO " . ClaimTable::TABLE_NAME . " (x, z, world, faction) VALUES (:x, :z, :world, :faction)", 
                [
                    "x" => $X,
                    "z" => $Z,
                    "world" => $World,
                    "faction" => $factionName
                ],
                function () use ($factionName, $World, $X, $Z) {
                    Main::getInstance()->getServer()->getAsyncPool()->submitTask(
                        new DatabaseTask(
                            "SELECT * FROM " . ClaimTable::TABLE_NAME . " WHERE x = :x AND z = :z AND world = :world AND faction = :faction", 
                            [
                                'x' => $X,
                                'z' => $Z,
                                'faction' => $factionName,
                                'world' => $World
                            ],
                            function ($result) use ($factionName) {
                                MainAPI::$claim[$factionName][] = $result[0];
                            },
                            ClaimEntity::class
                    ));
                }
        ));
    }

    public static function isClaim(string $World, int $X, int $Z) : bool{
        return self::getFactionClaim($World, $X, $Z) instanceof ClaimEntity;
    }

    public static function getFactionClaim(string $World, int $X, int $Z) : ?ClaimEntity {
        foreach (self::$claim as $factionName => $factionClaim) {
            foreach ($factionClaim as $claim) {
                if ($claim->x == $X && $claim->z == $Z && $claim->world == $World) return $claim;
            }
        }
        return null;
    }

    public static function removeClaim(Player $player, string $factionName) {
        $Chunk = $player->getLevel()->getChunkAtPosition($player);
        $X = $Chunk->getX();
        $Z = $Chunk->getZ();
        $World = $player->getLevel()->getName();
        $claim = self::getFactionClaim($World, $X, $Z);
        if (!$claim instanceof ClaimEntity) return;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "DELETE FROM " . ClaimTable::TABLE_NAME . " WHERE x = :x AND z = :z AND world = :world AND faction = :faction", 
                [
                    "x" => $X,
                    "z" => $Z,
                    "world" => $World,
                    "faction" => $factionName
                ],
                function () use ($factionName, $X, $Z, $World) {
                    foreach (MainAPI::$claim[$factionName] as $key => $claim) {
                        if ($claim->x == $X && $claim->z == $Z && $claim->world == $World) {
                            unset(MainAPI::$claim[$factionName][$key]);
                        }
                    }
                }
        ));
    }

    /**
     * @return ClaimEntity[]
     */
    public static function getClaimsFaction(string $factionName) : array {
        return self::$claim[$factionName] ?? [];
    }

    public static function getFactionHomes(string $factionName) : array {
        return self::$home[$factionName] ?? [];
    }

    public static function getFactionHome(string $factionName, string $name) : ?HomeEntity {
        return self::$home[$factionName][$name] ?? null;
    }

    public static function removeHome(string $factionName, string $name) {
        if (!isset(self::$home[$factionName][$name])) return false;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "DELETE FROM " . HomeTable::TABLE_NAME . " WHERE faction = :faction AND name = :name", 
                [
                    "faction" => $factionName,
                    "name" => $name
                ],
                function () use ($factionName, $name) {
                    unset(MainAPI::$home[$factionName][$name]);
                }
        ));
    }

    public static function addHome(Player $player, string $factionName, string $name) {
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "INSERT INTO " . HomeTable::TABLE_NAME . " (x, y, z, world, faction, name) VALUES (:x, :y, :z, :world, :faction, :name)", 
                [
                    "x" => floor($player->getX()),
                    "z" => floor($player->getZ()),
                    "y" => floor($player->getY()),
                    "world" => $player->getLevel()->getName(),
                    "faction" => $factionName,
                    "name" => $name
                ],
                function () use ($player, $factionName, $name) {
                    Main::getInstance()->getServer()->getAsyncPool()->submitTask(
                        new DatabaseTask(
                            "SELECT * FROM " . HomeTable::TABLE_NAME . " WHERE x = :x AND z = :z AND world = :world AND faction = :faction AND name = :name", 
                            [
                                "x" => floor($player->getX()),
                                "z" => floor($player->getZ()),
                                "y" => floor($player->getY()),
                                "world" => $player->getLevel()->getName(),
                                "faction" => $factionName,
                                "name" => $name
                            ],
                            function ($result) use ($factionName, $name) {
                                MainAPI::$home[$factionName][$name] = $result[0];
                            },
                            HomeEntity::class
                    ));
                }
        ));
    }

    public static function getPlayerLang(string $playerName) : string {
        return self::$languages[$playerName] ?? Utils::getConfigLang("default-language");
    }

    public static function changeLanguage(string $playerName, string $slug) {
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . UserTable::TABLE_NAME . " SET language = :lang WHERE name = :name", 
                [
                    'lang' => $slug,
                    'name' => $playerName
                ],
                function () use ($playerName, $slug) {
                    MainAPI::$languages[$playerName] = $slug;
                }
        ));
    }

    public static function getLevelReward(int $level) : ?RewardInterface {
        $Data = self::getLevelRewardData($level);
        $Reward = RewardFactory::get($Data['type']);
        if ($Reward !== null) $Reward->setValue($Data['value']);
        return $Reward;
    }

    public static function getLevelRewardData(int $level) : array {
        return Main::getInstance()->levelConfig->__get($level-2);
    }

    public static function updateFactionOption(string $factionName, string $option, int $value) {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return false;
        $Faction->$value = $value;
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(
            new DatabaseTask(
                "UPDATE " . FactionTable::TABLE_NAME . " SET " . $option . " = " . $option . " + :option WHERE name = :name", 
                [
                    'option' => $value,
                    'name' => $factionName
                ],
                function () use ($Faction) {
                    MainAPI::$factions[$Faction->name] = $Faction;
                }
        ));
    }

}