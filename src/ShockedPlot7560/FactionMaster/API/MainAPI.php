<?php

namespace ShockedPlot7560\FactionMaster\API;

use Exception;
use InvalidArgumentException;
use PDO;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\InvitationTable;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MainAPI {

    /** @var FactionEntity[] */
    public static $factions = [];
    /** @var UserEntity[] */
    public static $users = [];
    /** @var \PDO */
    private static $PDO;

    public static function init(PDO $PDO) {
        self::$PDO = $PDO;
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
            $xp = $XPneedLevel;
        }else{
            $xp = $newXP;
        }

        try {
            $query = self::$PDO->prepare("UPDATE " .UserTable::TABLE_NAME . " SET xp = :xp WHERE name = :name");
            return $query->execute([ 
                'xp' => $xp,
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
            \var_dump($Exception->getMessage());
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
            \var_dump($Exception->getMessage());
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
}