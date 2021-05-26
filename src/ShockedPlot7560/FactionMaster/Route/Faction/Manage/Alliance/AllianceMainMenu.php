<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Faction\Manage\ManageFactionMain;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class AllianceMainMenu implements Route {

    const SLUG = "allianceMain";

    public $PermissionNeed = [
        Ids::PERMISSION_SEND_ALLIANCE_INVITATION,
        Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION,
        Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND,
        Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND
    ];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;
    /** @var FactionEntity */
    private $FactionEntity;
    /** @var UserEntity */
    private $UserEntity;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(ManageFactionMain::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->FactionEntity = MainAPI::getFactionOfPlayer($player->getName());
        $this->UserEntity = $User;
        $this->permissions = $UserPermissions;

        $message = '';
        $this->buttons = [];
        foreach ($this->FactionEntity->ally as $Alliance) {
            $this->buttons[] = MainAPI::getFaction($Alliance)->name;
        }
        if (count($this->FactionEntity->ally) == 0) {
           $message = "§4You don't have an ally yet";
        }
        if ((isset($UserPermissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) && $UserPermissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) || 
            $this->UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = "Send an invitation";
        if ((isset($UserPermissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) && $UserPermissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) || 
            $this->UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = "Invitation pending";
        if ((isset($UserPermissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) && $UserPermissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) || 
            (isset($permisUserPermissionssions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) && $UserPermissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) || 
            $this->UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = "Demand pending";
        $this->buttons[] = "§4Back";
        $menu = $this->allianceMainMenu($message);
        $menu->sendToPlayer($player);
    }

    public function call() : callable{
        $permissions = $this->permissions;
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($permissions, $backMenu){
            if ($data === null) return;
            if ($data == (\count($this->buttons) - 1)) {
                Utils::processMenu($backMenu, $Player);
                return;
            }
            $allyNumber = count($this->FactionEntity->ally);
            switch ($data) {
                case $allyNumber:
                    if ((isset($permissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) || 
                        $this->UserEntity->rank == Ids::OWNER_ID) {
                            Utils::processMenu(RouterFactory::get(NewAllianceInvitation::SLUG), $Player);
                    }else
                    if ((isset($permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) || 
                        $this->UserEntity->rank == Ids::OWNER_ID) {
                            Utils::processMenu(RouterFactory::get(AllianceInvitationList::SLUG), $Player);
                    }else
                    if ((isset($permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) && $permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) || 
                        (isset($permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) && $permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) || 
                        $this->UserEntity->rank == Ids::OWNER_ID) {
                            Utils::processMenu(RouterFactory::get(AllianceDemandList::SLUG), $Player);
                    }
                    break;
                case $allyNumber + 1:
                    if ((isset($permissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) || 
                        $this->UserEntity->rank == Ids::OWNER_ID) {
                            if ((isset($permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) || 
                                $this->UserEntity->rank == Ids::OWNER_ID) {
                                    Utils::processMenu(RouterFactory::get(AllianceInvitationList::SLUG), $Player);
                            }else
                            if ((isset($permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) && $permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) || 
                                (isset($permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) && $permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) || 
                                $this->UserEntity->rank == Ids::OWNER_ID) {
                                    Utils::processMenu(RouterFactory::get(AllianceDemandList::SLUG), $Player);
                            }
                    }else
                    if ((isset($permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) || 
                        $this->UserEntity->rank == Ids::OWNER_ID) {
                            Utils::processMenu(RouterFactory::get(AllianceInvitationList::SLUG), $Player);
                    }
                    break;
                case $allyNumber + 2:
                    Utils::processMenu(RouterFactory::get(AllianceDemandList::SLUG), $Player);
                    break;
                default:
                    Utils::processMenu(RouterFactory::get(ManageAlliance::SLUG), $Player, [MainAPI::getFaction($this->FactionEntity->ally[$data])]);
                    break;
            }
        };
    }

    private function allianceMainMenu(string $message = "") : SimpleForm {
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Alliance main menu");
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

}