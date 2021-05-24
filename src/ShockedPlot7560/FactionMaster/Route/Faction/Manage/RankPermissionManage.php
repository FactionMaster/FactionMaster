<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage;

use InvalidArgumentException;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class RankPermissionManage implements Route {

    const SLUG = "rankPermissionManage";

    /** @var \jojoe77777\FormAPI\FormAPI */
    private $FormUI;
    /** @var array */
    private $check;
    /** @var array */
    private $permissionsData;
    /** @var array */
    private $permissionsUser;
    /** @var array */
    private $permissionsFaction;
    /** @var \ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity */
    private $Faction;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $Main = Main::getInstance();
        $this->FormUI = $Main->FormUI;
    }

    /**
     * @param Player $player
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, ?array $params = null)
    {
        if (!isset($params[0]) || !\is_int($params[0])) throw new InvalidArgumentException("Please give the rank id in the first item of the \$params");
        $this->rank = $params[0];
        $this->permissionsData = Utils::getPermissionData();
        $this->permissionsUser = MainAPI::getMemberPermission($player->getName());
        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());
        $this->permissionsFaction = $this->Faction->permissions[$this->rank];

        $UserEntity = MainAPI::getUser($player->getName());
        $this->check = [];
        foreach ($this->permissionsData as $key => $permission) {
            if ($UserEntity->rank == Ids::OWNER_ID || (isset($this->permissionsUser[$permission['id']]) && $this->permissionsUser[$permission['id']] === true)) {
                $this->check[] = $permission['text'];
            }else{
                unset($this->permissionsData[$key]);
            }
        }
        $menu = $this->createPermissionMenu();
        $menu->sendToPlayer($player);
    }

    public function call() : callable{
        return function (Player $Player, $data) {
            if ($data === null) return;
            foreach ($data as $key => $permissionsSet) {
                $this->Faction->permissions[$this->rank][$this->permissionsData[$key]['id']] = $permissionsSet;
            }
            if (MainAPI::updatePermissionFaction($this->Faction->name, $this->Faction->permissions)){
                Utils::processMenu(RouterFactory::get(ChangePermissionMain::SLUG), $Player, ['§2Permission update successfuly !']);
            }else{
                Utils::processMenu(RouterFactory::get(ChangePermissionMain::SLUG), $Player, [' §c>> §4An error has occured']);
            }
        };
    }

    private function createPermissionMenu(string $message = "") : CustomForm {
        $menu = $this->FormUI->createCustomForm($this->call());
        $menu->setTitle("Manage permission");
        foreach ($this->permissionsData as $key => $value) {
            $menu->addToggle($value['text'], $this->permissionsFaction[$value["id"]] ?? false);
        }
        return $menu;
    }
}