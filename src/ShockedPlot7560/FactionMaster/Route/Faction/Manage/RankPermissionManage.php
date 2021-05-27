<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage;

use InvalidArgumentException;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class RankPermissionManage implements Route {

    const SLUG = "rankPermissionManage";

    public $PermissionNeed = [
        Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS
    ];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;
    /** @var array */
    private $check;
    /** @var array */
    private $permissionsData;
    /** @var array */
    private $permissionsUser;
    /** @var array */
    private $permissionsFaction;
    /** @var FactionEntity */
    private $Faction;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(ChangePermissionMain::SLUG);
    }

    /**
     * @param Player $player
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        if (!isset($params[0]) || !\is_int($params[0])) throw new InvalidArgumentException("Please give the rank id in the first item of the \$params");
        $this->rank = $params[0];
        $this->permissionsData = Utils::getPermissionData();
        $this->permissionsUser = $UserPermissions;
        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());
        $this->permissionsFaction = $this->Faction->permissions[$this->rank];

        $this->check = [];
        foreach ($this->permissionsData as $key => $permission) {
            if ($User->rank == Ids::OWNER_ID || (isset($this->permissionsUser[$permission['id']]) && $this->permissionsUser[$permission['id']] === true)) {
                $this->check[] = $permission['text'];
            }else{
                unset($this->permissionsData[$key]);
            }
        }
        $menu = $this->createPermissionMenu();
        $player->sendForm($menu);
    }

    public function call() : callable{
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($backMenu) {
            if ($data === null) return;
            $i =0;
            foreach ($this->permissionsData as $key => $permissionDa) {
                $this->Faction->permissions[$this->rank][$permissionDa['id']] = $data[$i];
                $i++;
            }
            if (MainAPI::updatePermissionFaction($this->Faction->name, $this->Faction->permissions)){
                Utils::processMenu($backMenu, $Player, ['§2Permission update successfuly !']);
            }else{
                Utils::processMenu($backMenu, $Player, [' §c>> §4An error has occured']);
            }
        };
    }

    private function createPermissionMenu(string $message = "") : CustomForm {
        $menu = new CustomForm($this->call());
        $menu->setTitle("Manage permission");
        foreach ($this->permissionsData as $value) {
            $menu->addToggle($value['text'], $this->permissionsFaction[$value["id"]] ?? false);
        }
        return $menu;
    }
}