<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance\AllianceMainMenu;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageFactionMain implements Route {

    const SLUG = "manageMainFaction";

    /** @var \jojoe77777\FormAPI\FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
    }

    public function __invoke(Player $player, ?array $params = null)
    {
        $permissions = MainAPI::getMemberPermission($player->getName());
        $UserEntity = MainAPI::getUser($player->getName());
        
        $this->buttons = [];
        if ((array_key_exists(Ids::PERMISSION_CHANGE_FACTION_DESCRIPTION, $permissions) && $permissions[Ids::PERMISSION_CHANGE_FACTION_DESCRIPTION]) || $UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = "Change description";
        if ((array_key_exists(Ids::PERMISSION_CHANGE_FACTION_MESSAGE, $permissions) && $permissions[Ids::PERMISSION_CHANGE_FACTION_MESSAGE]) || $UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = "Change message";
        if ((array_key_exists(Ids::PERMISSION_CHANGE_FACTION_VISIBILITY, $permissions) && $permissions[Ids::PERMISSION_CHANGE_FACTION_VISIBILITY]) || $UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = "Change visibility";
        if ((array_key_exists(Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS, $permissions) && $permissions[Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS]) || $UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = "Change permissions";
        if ((isset($permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) && $permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) || 
            (isset($permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) && $permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) || 
            (isset($permissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) || 
            (isset($permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) || 
            $UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = "Manage alliance";
        $this->buttons[] = "§4Back";

        $message = "";
        if (isset($params[0])) $message = $params[0];
        $menu = $this->manageMainMembersMenu($message);
        $menu->sendToPlayer($player);
    }

    public function call(): callable
    {
        return function (Player $player, $data) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case $this->buttons[\count($this->buttons) - 1]:
                    Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $player);
                    break;
                case "Manage alliance":
                    Utils::processMenu(RouterFactory::get(AllianceMainMenu::SLUG), $player);
                    break;
                case "Change description":
                    Utils::processMenu(RouterFactory::get(ChangeDescription::SLUG), $player);
                    break;
                case "Change message":
                    Utils::processMenu(RouterFactory::get(ChangeMessage::SLUG), $player);
                    break;
                case "Change visibility":
                    Utils::processMenu(RouterFactory::get(ChangeVisibility::SLUG), $player);
                    break;
                case "Change permissions":
                    Utils::processMenu(RouterFactory::get(ChangePermissionMain::SLUG), $player);
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function manageMainMembersMenu(string $message = "") : SimpleForm {
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Manage faction");
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

}