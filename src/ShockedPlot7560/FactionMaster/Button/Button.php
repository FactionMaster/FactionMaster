<?php

namespace ShockedPlot7560\FactionMaster\Button;

use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class Button {

    private $slug;
    private $contentSlug;
    private $permissions;
    private $callable;

    public function __construct(string $slug, string $contentSlug, callable $callable, array $permissions = [])
    {
        $this->slug = $slug;
        $this->contentSlug = $contentSlug;
        $this->permissions = $permissions;
        $this->callable = $callable;
    }

    public function getSlug() : string {
        return $this->slug;
    }

    public function getContentSlug() : string {
        return $this->contentSlug;
    }

    public function getContent(string $playerName) : string {
        return Utils::getText($playerName, $this->getContentSlug());
    }

    /**
     * @return int[]
     */
    public function getPermissions() : array {
        return $this->permissions;
    }

    public function canAccess(string $playerName) : bool {
        if (count($this->permissions) == 0) return true;
        $User = MainAPI::getUser($playerName);
        if ($User->rank == Ids::OWNER_ID) return true;
        $PermissionsPlayer = MainAPI::getMemberPermission($playerName);
        if ($PermissionsPlayer === null) return false;
        foreach ($this->getPermissions() as $Permission) {
            if (isset($PermissionsPlayer[$Permission]) && $PermissionsPlayer[$Permission]) return true;
        }
        return false;
    }

    public function getCallable() : callable {
        return $this->callable;
    }

    public function call(Player $Player) {
        return call_user_func($this->getCallable(), $Player);
    }
}