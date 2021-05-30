<?php

namespace ShockedPlot7560\FactionMaster\API;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;
use pocketmine\level\format\Chunk;
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
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MainAPI {

    /** @var FactionEntity[] */
    public static $factions = [];
    /** @var UserEntity[] */
    public static $users = [];
    /** @var \PDO */
    private static $PDO;
    /** @var array[] */
    public static $claim;
    /** @var array[] */
    public static $home;
    /** @var string[] */
    public static $languages;

    public static function init(PDO $PDO) {
        self::$PDO = $PDO;
        self::initClaim();
        self::initHome();
    }

    private static function initClaim() {
        self::$claim = [];
        foreach (MainAPI::getAllClaim() as $Claim) {
            if (!isset(self::$claim[$Claim->faction])) {
                self::$claim[$Claim->faction] = [$Claim->getToString()];
            }else{
                self::$claim[$Claim->faction][] = $Claim->getToString();
            }
        }
    }

    private static function initHome() {
        self::$home = [];
        foreach (MainAPI::getAllHome() as $Home) {
            if (!isset(self::$home[$Home->faction])) {
                self::$home[$Home->faction] = [$Home->name => $Home->getToArray()];
            }else{
                self::$home[$Home->faction][$Home->name] = $Home->getToArray();
            }
        }
    }

    /**
     * Get the Faction's instance from the $args given
     * @param string $factionName The name of the search faction
     * @return null|FactionEntity Return null if faction not found
     */
    public static function getFaction(string $factionName) : ?FactionEntity {
        try {
            $query = self::$PDO->prepare("SELECT * FROM " . FactionTable::TABLE_NAME . " WHERE name = :name");
            $query->execute([ 'name' => $factionName ]);
            $query->setFetchMode(PDO::FETCH_CLASS, FactionEntity::class);
            $result = $query->fetch();
        } catch (\PDOException $Exception) {
            return null;
        }
        return $result === false ? null : $result;
    }

    /**
     * Test if the faction's given are registered
     * @param string The name of the search faction
     * @return boolean
     */
    public static function isFactionRegistered(string $factionName) : bool {
        return self::getFaction($factionName) instanceof FactionEntity;
    }

    /**
     * Remove a registered faction
     * @param string $args The name of the target faction
     * @return boolean False on failure
     */
    public static function removeFaction(string $factionName) : bool {
        try {
            $Faction = self::getFaction($factionName);
            foreach ($Faction->ally as $Alliance) {
                if (!self::removeAlly($factionName, $Alliance)) return false;
            }
            $query = self::$PDO->prepare("DELETE FROM " . FactionTable::SLUG . " WHERE name = :name");
            if (!$query->execute([ 'name' => $factionName ])) return false;

            $query = self::$PDO->prepare("UPDATE " . UserTable::TABLE_NAME . " SET faction = NULL, rank = NULL WHERE faction = :faction");
            return $query->execute([ 'faction' => $factionName ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Can be used to add or update a faction
     * @param string $factionName The name of target faction
     * @param string $ownerName The name of the owner
     * @return false|FactionEntity False on failure
     */
    public static function addFaction(string $factionName, string $ownerName) {
        try {
            $query = self::$PDO->prepare("INSERT INTO " . FactionTable::SLUG . " (name, members, ally, permissions) VALUE (:name, :members, :ally, :permissions)");
            $query->execute([
                'name' => $factionName,
                'members' => \base64_encode(\serialize([
                    $ownerName => Ids::OWNER_ID
                ])),
                'ally' => \base64_encode(\serialize([])),
                'permissions' => \base64_encode(\serialize([[],[],[],[]]))
            ]);
            return self::addMember($factionName, $ownerName, Ids::OWNER_ID);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Return the members list of the faction
     * @param int|string The id / name or instance of the target faction
     * @return UserEntity[]
     * @throws \InvalidArgumentException
     */
    public static function getMembers(string $factionName) : array {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return [];
        return $Faction->members;
    }

    /**
     * Add a member to the target faction
     * **It will not check if the player are already in a faction !**
     * @param string $factionName The name of the faction
     * @param string $playerName The name of the player
     * @param int $rankId (Default to member) If a special rank is wanted
     * @return boolean False on failure
     */
    public static function addMember(string $factionName, string $playerName, int $rankId = Ids::RECRUIT_ID) : bool {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return false;
        $Faction->members[$playerName] = $rankId;
        try {
            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET members = :members WHERE name = :name");
            if (!$query->execute([
                'members' => \base64_encode(\serialize($Faction->members)),
                'name' => $factionName
            ])) return false;

            $query = self::$PDO->prepare("UPDATE " . UserTable::TABLE_NAME . " SET faction = :faction, rank = :rank WHERE name = :name");
            return $query->execute([
                'faction' => $factionName,
                'rank' => $rankId,
                'name' => $playerName
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Remove the specified member of the target faction
     * @param string $factionName The name of the faction
     * @param string $playerName The player's name to remove
     * @return boolean False on failure
     */
    public static function removeMember(string $factionName, string $playerName) : bool {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return false;
        unset($Faction->members[$playerName]);
        try {
            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET members = :members WHERE name = :name");
            if (!$query->execute([
                'members' => \base64_encode(\serialize($Faction->members)),
                'name' => $factionName
            ])) return false;

            $query = self::$PDO->prepare("UPDATE " . UserTable::TABLE_NAME . " SET faction = NULL, rank = NULL WHERE name = :name");
            return $query->execute([
                'name' => $playerName
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Add a quantity of XP to the faction, *If the total xp of the level are exceeded, it will be set to this limit*
     * @param string $factionName The name of the faction
     * @param int $xp The amount of xp to add
     * @return boolean False on failure
     */
    public static function addXP(string $factionName, int $xp) : bool {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return false;

        $level = $Faction->level;
        $XPneedLevel = 1000*pow(1.09, $level);
        $newXP = $Faction->xp + $xp;
        if ($newXP > $XPneedLevel) {
            $xp = $newXP - $XPneedLevel;
            $level++;
        }else{
            $xp = $newXP;
        }

        try {
            $query = self::$PDO->prepare("UPDATE " .FactionTable::TABLE_NAME . " SET xp = :xp, level = :level WHERE name = :name");
            return $query->execute([ 
                'xp' => $xp,
                'level' => $level,
                'name' => $factionName
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Change the faction level and reset xp to 0
     * @param string $factionName Name of the faction
     * @param int $level Number level to change
     * @return boolean
     */
    public static function changeLevel(string $factionName, int $level) : bool {
        try {
            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET level = level + :level, xp = 0 WHERE name = :name");
            return $query->execute([
                'level' => $level,
                'name' => $factionName
            ]);
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Change the power of a faction
     * @param string $factionName The name of the faction
     * @param int $power The power to change, it allow negative integer to substract
     * @return boolean False on failure
     */
    public static function changePower(string $factionName, int $power) : bool {
        $Faction = self::getFaction($factionName);
        if (!$Faction instanceof FactionEntity) return false;
        $actualPower = $Faction->power;
        if (($totalPower = $actualPower + $power) < 0) $totalPower = 0;

        try {
            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET power = :power WHERE name = :name");
            return $query->execute([
                'power' => $totalPower,
                'name' => $factionName
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Return the faction's instance of the target player
     * @param string $playerName The target player
     * @return FactionEntity|null Null if the player has no faction or player not found
     */
    public static function getFactionOfPlayer(string $playerName) : ?FactionEntity {
        try {
            $FactionTableName = FactionTable::TABLE_NAME;
            $UserTableName = UserTable::TABLE_NAME;
            $query = self::$PDO->prepare("SELECT $FactionTableName.*  FROM $FactionTableName LEFT JOIN $UserTableName ON $FactionTableName.name = $UserTableName.faction WHERE $UserTableName.name = :name");
            $query->execute([ 'name' => $playerName ]);
            $query->setFetchMode(PDO::FETCH_CLASS, FactionEntity::class);
            $result = $query->fetch();
            return $result === false ? null : $result;
        } catch (\PDOException $Exception) {
            return null;
        }
    }

    /**
     * Test if the player are in a faction
     * @param string $playerName Name of the player
     * @return boolean False if the player have no faction
     */
    public static function isInFaction(string $playerName) : bool {
        if (self::getFactionOfPlayer($playerName) instanceof FactionEntity) {
            return true;
        }
        return false;
    }

    /**
     * Test if the two players are in the same faction
     * @param string $playerName1 The name of the first player *The order does not matter*
     * @param string $playerName2 The name of the second player *The order does not matter*
     * @return boolean
     */
    public static function sameFaction(string $playerName1, string $playerName2) : bool {
        $player1 = self::getFactionOfPlayer($playerName1);
        $player2 = self::getFactionOfPlayer($playerName2);
        return ($player1 === $player2) && ($player1 !== null);
    }

    /**
     * Return the UserEntity
     * @param string $playerName The name of the player
     * @return UserEntity|null Null if the user are not found
     */
    public static function getUser(string $playerName) : ?UserEntity {
        $query = self::$PDO->prepare("SELECT * FROM " . UserTable::TABLE_NAME . " WHERE name = :name");
        $query->execute([ 'name' => $playerName ]);
        $query->setFetchMode(PDO::FETCH_CLASS, UserEntity::class);
        $result = $query->fetch();
        return $result === false ? null : $result;
    }

    /**
     * Check if the user are already register in the database
     * @param string $playerName Name of the player
     * @return boolean
     */
    public static function userIsRegister(string $playerName) : bool {
        if (self::getUser($playerName) instanceof UserEntity) {
            return true;
        }
        return false;
    }

    /**
     * Check if the two faction are ally
     * @param string $factionName1
     * @param string $factionName2
     * @return boolean
     */
    public static function isAlly(string $factionName1, string $factionName2) : bool {
        try {
            $query = self::$PDO->prepare("SELECT ally FROM " .FactionTable::TABLE_NAME . " WHERE name = :name");
            $query->execute([ 'name' => $factionName1 ]);
            $result = $query->fetch();
            if ($result === false) return false;
            $result = \unserialize(\base64_decode($result['ally']));
            if (\is_bool($result)) return false;
            return \in_array($factionName2, $result);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Set two faction ally
     * @param string $factionName1
     * @param string $factionName2
     * @return boolean
     */
    public static function setAlly(string $factionName1, string $factionName2) : bool {
        $Faction1 = self::getFaction($factionName1);
        $Faction2 = self::getFaction($factionName2);
        if (count($Faction1->ally) >= (int) $Faction1->max_ally || count($Faction2->ally) >= (int) $Faction2->max_ally) {
            return false;
        }
        $Faction1->ally[] = $factionName2;
        $Faction2->ally[] = $factionName1;

        try {
            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET ally = :ally WHERE name = :name");
            if (!$query->execute([
                'ally' => \base64_encode(\serialize($Faction1->ally)),
                'name' => $factionName1
            ])) return false;

            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET ally = :ally WHERE name = :name");
            return $query->execute([
                'ally' => \base64_encode(\serialize($Faction2->ally)),
                'name' => $factionName2
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Remove an alliance between two faction
     * @param string $faction1
     * @param string $faction2
     * @return boolean False on failure
     */
    public static function removeAlly(string $faction1, string $faction2) : bool {
        if (!self::isAlly($faction1, $faction2)) return false;

        $Faction1 = self::getFaction($faction1);
        $Faction2 = self::getFaction($faction2);
        if (!$Faction1 instanceof FactionEntity || !$Faction2 instanceof FactionEntity) return false;
        
        unset($Faction1->ally[$faction1]);
        unset($Faction2->ally[$faction2]);

        foreach ([$Faction1, $Faction2] as $key => $Faction) {
            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET ally = :ally WHERE name = :name");
            if (!$query->execute([
                'ally' => \base64_encode(\serialize($Faction->ally)),
                'name' => $Faction->name
            ])) return false;
        }
        return true;
    }

    /**
     * Change a user rank in his faction
     * @param string $playerName Name of the user
     * @param int $rank The id of the rank define in Ids interface
     * @return boolean
     */
    public static function changeRank(string $playerName, int $rank) : bool {
        $Faction = self::getFactionOfPlayer($playerName);
        if (!$Faction instanceof FactionEntity) return false;

        $Faction->members[$playerName] = $rank;

        try {
            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET members = :members WHERE name = :name");
            if (!$query->execute([
                'members' => \base64_encode(\serialize($Faction->members)),
                'name' => $Faction->name
            ])) return false;

            $query = self::$PDO->prepare("UPDATE " . UserTable::TABLE_NAME . " SET rank = :rank WHERE name = :name");
            return $query->execute([
                'rank' => $rank,
                'name' => $playerName
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Use to change the faction's visibility : Public / private / invitation
     * @param string $factionName Name of the faction
     * @param int $visibilityType The type define in Ids interface
     * @return boolean False on failure
     */
    public static function changeVisibility(string $factionName, int $visibilityType) : bool{
        try {
            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET visibility = :visibility WHERE name = :name");
            return $query->execute([
                'visibility' => $visibilityType,
                'name' => $factionName
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Use to change the faction's message
     * @param string $factionName Name of the faction
     * @param int $message The message to change
     * @return boolean False on failure
     */
    public static function changeMessage(string $factionName, string $message) : bool{
        try {
            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET messageFaction = :message WHERE name = :name");
            return $query->execute([
                'message' => $message,
                'name' => $factionName
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Use to change the faction's message
     * @param string $factionName Name of the faction
     * @param int $description The description to change
     * @return boolean False on failure
     */
    public static function changeDescription(string $factionName, string $description) : bool{
        try {
            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET description = :description WHERE name = :name");
            return $query->execute([
                'description' => $description,
                'name' => $factionName
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Return the permission's array of the target player
     * @param string $playerName
     * @return null|string[]
     */
    public static function getMemberPermission(string $playerName) : ?array {
        try {
            $FactionTableName = FactionTable::TABLE_NAME;
            $UserTableName = UserTable::TABLE_NAME;
            $query = self::$PDO->prepare("SELECT $FactionTableName.permissions, $UserTableName.rank FROM $FactionTableName LEFT JOIN $UserTableName ON $FactionTableName.name = $UserTableName.faction WHERE $UserTableName.name = :name");
            $query->execute([ 'name' => $playerName ]);
            $result = $query->fetch();
            if ($result === false) return null;
            $permissions = unserialize(\base64_decode($result['permissions']));
            return $permissions[(int) $result['rank']];
        } catch (\PDOException $Exception) {
            return null;
        }
    }

    /**
     * Create an invitation between two entity
     * @param string $sender Can be a user or faction name
     * @param string $receiver Can be a user or faction name
     * @param string $type Value : member or ally foreach -> invitation concern member or ally
     * @return bool
     */
    public static function makeInvitation(string $sender, string $receiver, string $type) : bool {
        try {
            $query = self::$PDO->prepare("INSERT INTO " .InvitationTable::TABLE_NAME . " (sender, receiver, type) VALUE (:sender, :receiver, :type)");
            return $query->execute([
                'sender' => $sender,
                'receiver' => $receiver,
                'type' => $type
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Test if the two entity are in invitation with the given type
     * @param string $sender
     * @param string $receiver
     * @param string $type
     * @return boolean
     */
    public static function areInInvitation(string $sender, string $receiver, string $type) : bool {
        try {
            $query = self::$PDO->prepare("SELECT * FROM " . InvitationTable::TABLE_NAME . " WHERE sender = :sender AND receiver = :receiver AND type = :type");
            $query->execute([
                'sender' => $sender,
                'receiver' => $receiver,
                'type' => $type
            ]);
            if (count($query->fetchAll()) > 0) return true;
            return false;
        } catch (\PDOException $Exception) {
            return true;
        }
    }

    /**
     * Remove the corresponding invitation
     * @param string $sender
     * @param string $receiver
     * @param string $type
     * @return boolean False on failure
     */
    public static function removeInvitation(string $sender, string $receiver, string $type) : bool {
        try {
            $query = self::$PDO->prepare("DELETE FROM " . InvitationTable::TABLE_NAME . " WHERE sender = :sender AND receiver = :receiver AND type = :type");
            return $query->execute([
                "sender" => $sender,
                "receiver" => $receiver,
                "type" => $type
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Return all the invitation send by an entity with the given type
     * @param string $sender
     * @param string $type
     * @return InvitationEntity[]
     */
    public static function getInvitationsBySender(string $sender, string $type) : array {
        try {
            $query = self::$PDO->prepare("SELECT * FROM " . InvitationTable::TABLE_NAME . " WHERE sender = :sender AND type= :type");
            $query->execute([
                'sender' => $sender,
                'type' => $type
            ]);
            $query->setFetchMode(PDO::FETCH_CLASS, InvitationEntity::class);
            return ($result = $query->fetchAll()) === false ? [] : $result;
        } catch (\PDOException $Exception) {
            return [];
        }
    }

    /**
     * Return all the invitation receive by an entity with the given type
     * @param string $receiver
     * @param string $type
     * @return InvitationEntity[]
     */
    public static function getInvitationsByReceiver(string $receiver, string $type) : array {
        try {
            $query = self::$PDO->prepare("SELECT * FROM " . InvitationTable::TABLE_NAME . " WHERE receiver = :receiver AND type= :type");
            $query->execute([
                'receiver' => $receiver,
                'type' => $type
            ]);
            $query->setFetchMode(PDO::FETCH_CLASS, InvitationEntity::class);
            return ($result = $query->fetchAll()) === false ? [] : $result;
        } catch (\PDOException $Exception) {
            return [];
        }
    }

    /**
     * Use to change the faction's permissions
     * @param string $factionName
     * @param array $permissions The permissions in this format : [[1 => true],[],[],[]]
     *                          where each index of sub array are a permission's ids define in Ids interface
     * @return boolean False on failure
     */
    public static function updatePermissionFaction(string $factionName, array $permissions) : bool {
        if (!isset($permissions[3])) throw new InvalidArgumentException("You must set the fourth item in the array");
        try {
            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET permissions = :permissions WHERE name = :name");
            return $query->execute([
                'permissions' => \base64_encode(\serialize($permissions)),
                'name' => $factionName
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    public static function addUser(string $playerName) : bool {
        try {
            $query = self::$PDO->prepare("INSERT INTO " . UserTable::TABLE_NAME . " (name) VALUE (:user)");
            return $query->execute([
                'user' => $playerName
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * Return the topFaction
     * @return FactionEntity[]
     */
    public static function getTopFaction() : array {
        try {
            $query = self::$PDO->prepare("SELECT * FROM " . FactionTable::TABLE_NAME . " ORDER BY level DESC, xp DESC, power DESC, money DESC LIMIT 10");
            $query->execute();
            $query->setFetchMode(PDO::FETCH_CLASS, FactionEntity::class);
            return $query->fetchAll();
        } catch (\PDOException $Exception) {
            return [];
        }
    }

    /**
     * Return all the claim register
     * @return ClaimEntity[]
     */
    public static function getAllClaim() : array {
        $claims = self::$claim;
        if ($claims === []) {
            try {
                $query = self::$PDO->prepare("SELECT * FROM " . ClaimTable::TABLE_NAME);
                $query->execute();
                $query->setFetchMode(PDO::FETCH_CLASS, ClaimEntity::class);
                return $query->fetchAll();
            } catch (\PDOException $Exception) {
                return [];
            }
        }else{
            return $claims;
        }
    }

    /**
     * @param Player $player 
     * @param string $factionName
     * @return bool False on failure
     */
    public static function addClaim(Player $player, string $factionName) : bool {
        $Chunk = $player->getLevel()->getChunkAtPosition($player);
        $X = $Chunk->getX();
        $Z = $Chunk->getZ();
        $World = $player->getLevel()->getName();
        try {
            $query = self::$PDO->prepare("INSERT INTO " . ClaimTable::TABLE_NAME . " (x, z, world, faction) VALUE (:x, :z, :world, :faction)");
            if (!$query->execute([
                "x" => $X,
                "z" => $Z,
                "world" => $World,
                "faction" => $factionName
            ])) return false;
            self::$claim[$factionName][] = Utils::claimToString($X, $Z, $World);
            return true;
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * @param string $World
     * @param int $X
     * @param int $Z
     * @return bool
     */
    public static function isClaim(string $World, int $X, int $Z) : bool{
        $stringClaim = Utils::claimToString($X, $Z, $World);
        foreach (self::$claim as $Faction => $FactionClaim) {
            foreach ($FactionClaim as $Claim) {
                if ($stringClaim === $Claim) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param string $World
     * @param int $X
     * @param int $Z
     * @return string|null Null if the chunk isn't claim
     */
    public static function getFactionClaim(string $World, int $X, int $Z) : ?string {
        $stringClaim = Utils::claimToString($X, $Z, $World);
        foreach (self::$claim as $Faction => $FactionClaim) {
            foreach ($FactionClaim as $Claim) {
                if ($stringClaim === $Claim) {
                    return $Faction;
                }
            }
        }
        return null;
    }

    /**
     * @param Player $player 
     * @param string $factionName
     * @return bool False on failure
     */
    public static function removeClaim(Player $player, string $factionName) : bool {
        $Chunk = $player->getLevel()->getChunkAtPosition($player);
        $X = $Chunk->getX();
        $Z = $Chunk->getZ();
        $World = $player->getLevel()->getName();
        try {
            $query = self::$PDO->prepare("DELETE FROM " . ClaimTable::TABLE_NAME . " WHERE x = :x AND z = :z AND world = :world AND faction = :faction");
            if (!$query->execute([
                "x" => $X,
                "z" => $Z,
                "world" => $World,
                "faction" => $factionName
            ])) return false;
            foreach (self::$claim[$factionName] as $key => $Claim) {
                if ($Claim === Utils::claimToString($X, $Z, $World)) {
                    unset(self::$claim[$factionName][$key]);
                }
            }
            return true;
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * @param string $factionName
     * @return string[]
     */
    public static function getClaimsFaction(string $factionName) : array {
        return self::$claim[$factionName] ?? [];
    }

    /**
     * Return all the home register
     * @return HomeEntity[]
     */
    public static function getAllHome() : array {
        $homes = self::$home;
        if ($homes === []) {
            try {
                $query = self::$PDO->prepare("SELECT * FROM " . HomeTable::TABLE_NAME);
                $query->execute();
                $query->setFetchMode(PDO::FETCH_CLASS, HomeEntity::class);
                return $query->fetchAll();
            } catch (\PDOException $Exception) {
                return [];
            }
        }else{
            return $homes;
        }
    }

    /**
     * @param string $factionName
     * @return array[] an array of HomeEntity convert with getToArray() function
     */
    public static function getFactionHomes(string $factionName) : array {
        return self::$home[$factionName] ?? [];
    }

    /**
     * @param string $factionName
     * @param string $name
     * @return (int|string)[]|null return null if the home don't exist
     */
    public static function getFactionHome(string $factionName, string $name) : ?array {
        return self::$home[$factionName][$name] ?? null;
    }

    /**
     * @param Player $player 
     * @param string $factionName
     * @return bool False on failure
     */
    public static function removeHome(string $factionName, string $name) : bool {
        try {
            $query = self::$PDO->prepare("DELETE FROM " . HomeTable::TABLE_NAME . " WHERE faction = :faction AND name = :name");
            if (!$query->execute([
                "faction" => $factionName,
                "name" => $name
            ])) return false;
            unset(self::$home[$factionName][$name]);
            return true;
        } catch (\Throwable $Exception) {
            return false;
        }
    }

    /**
     * @param Player $player 
     * @param string $factionName
     * @return bool False on failure
     */
    public static function addHome(Player $player, string $factionName, string $name) : bool {
        try {
            $query = self::$PDO->prepare("INSERT INTO " . HomeTable::TABLE_NAME . " (x, y, z, world, faction, name) VALUE (:x, :y, :z, :world, :faction, :name)");
            if (!$query->execute([
                "x" => floor($player->getX()),
                "z" => floor($player->getZ()),
                "y" => floor($player->getY()),
                "world" => $player->getLevel()->getName(),
                "faction" => $factionName,
                "name" => $name
            ])) return false;
            self::$home[$factionName][$name] = Utils::homeToArray($player->getX(), $player->getY(), $player->getZ(), $player->getLevel()->getName());
            return true;
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * @param string $playerName
     * @return string
     */
    public static function getPlayerLang(string $playerName) : string {
        return self::$languages[$playerName] ?? Utils::getConfigLang("default-language");
    }

    /**
     * @param string $playerName
     * @param string $slug
     * @return bool False on failure
     */
    public static function changeLanguage(string $playerName, string $slug) : bool {
        try {
            $query = self::$PDO->prepare("UPDATE " . UserTable::TABLE_NAME . " SET language = :lang WHERE name = :name");
            if (!$query->execute([
                'lang' => $slug,
                'name' => $playerName
            ])) return false;
            self::$languages[$playerName] = $slug;
            return true;
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * @param string $factionName
     * @param int $money Give negative integer to substract
     * @return bool False on failure
     */
    public static function updateMoney(string $factionName, int $money) : bool {
        try {
            $query = self::$PDO->prepare("UPDATE " .FactionTable::TABLE_NAME . " SET money = money + :money WHERE name = :name");
            return $query->execute([ 
                'money' => $money,
                'name' => $factionName
            ]);
        } catch (\PDOException $Exception) {
            return false;
        }
    }

    /**
     * @param int $level
     * @return null|RewardInterface Null if reward don't exist or if it's maximum level
     */
    public static function getLevelReward(int $level) : ?RewardInterface {
        $Data = self::getLevelRewardData($level);
        $Reward = RewardFactory::get($Data['type']);
        if ($Reward !== null) $Reward->setValue($Data['value']);
        return $Reward;
    }

    /**
     * @param int $level
     * @return array The data containt in level.yml
     */
    public static function getLevelRewardData(int $level) : array {
        return Main::getInstance()->levelConfig->__get($level-2);
    }

    /**
     * @param string $factionName
     * @param string $option The name of the column to update
     * @param int $value
     * @return bool False on failure
     */
    public static function updateFactionOption(string $factionName, string $option, int $value) : bool {
        try {
            $query = self::$PDO->prepare("UPDATE " . FactionTable::TABLE_NAME . " SET " . $option . " = " . $option . " + :option WHERE name = :name");
            return $query->execute([
                'option' => $value,
                'name' => $factionName
            ]);
        } catch (PDOException $Exception) {
            return false;
        }
    }
}