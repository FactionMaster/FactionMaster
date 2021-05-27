<?php

namespace ShockedPlot7560\FactionMaster\Route;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Faction\Manage\ManageFactionMain;
use ShockedPlot7560\FactionMaster\Route\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MainPanel implements Route {

    const SLUG = "main";

    const NO_FACTION_TYPE = 0;
    const FACTION_TYPE = 1;

    public $PermissionNeed = [];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;
    /** @var UserEntity */
    private $UserEntity;
    /** @var array */
    private $UserPermissions;
    /** @var FactionEntity */
    private $Faction;
    /** @var int */
    private $menuType;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
    }

    /**
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $Player, UserEntity $User, array $UserPermissions, ?array $params = null){
        $this->UserEntity = $User;
        $this->UserPermissions = $UserPermissions;
        $message = '';

        if ($this->UserEntity->faction === null) {
            $this->buttons = [
                "Join a faction",
                "Create a faction",
                "Top factions",
                "§4Quit"
            ];
            $this->menuType = self::NO_FACTION_TYPE;
            if (isset($params[0])) $message = $params[0];
            $menu = $this->noFactionMenu($message);
        }else{
            $this->generateButton($Player);
            $this->menuType = self::FACTION_TYPE;
            if (isset($params[0])) $message = $params[0];
            $this->Faction = MainAPI::getFaction($this->UserEntity->faction);
            $menu = $this->factionMenu($message);
        }
        $Player->sendForm($menu);
    }

    public function call() : callable{
        return function (Player $Player, $data) {
            if ($data === null) return;
            switch ($this->menuType) {
                case self::NO_FACTION_TYPE:
                    switch ($data) {
                        case 0:
                            Utils::processMenu(RouterFactory::get(ManageInvitationMain::SLUG), $Player);
                            break;
                        case 1:
                            Utils::processMenu(RouterFactory::get(CreateFactionPanel::SLUG), $Player);
                            break;
                        case 2:
                            //TODO: Topfactions panel
                            break;
                        default:
                            return;
                            break;
                    }
                    break;
                case self::FACTION_TYPE:
                    $countData = \count($this->buttonsData);
                    if ($data == $countData - 1) {
                        return;
                        break;
                    }
                    switch ($this->buttonsData[$data]["slug"]) {
                        case "bankView":
                            break;
                        case "factionMembers":
                            break;
                        case "factionHome":
                            break;
                        case "manageMembers":
                            Utils::processMenu(RouterFactory::get(ManageMainMembers::SLUG), $Player);
                            break;
                        case "manageFaction":
                            Utils::processMenu(RouterFactory::get(ManageFactionMain::SLUG), $Player);
                            break;
                        case "factionsTop":
                            break;
                        case "leavingButton":
                            if ($this->UserEntity->rank == Ids::OWNER_ID) {
                                $data = [
                                    $this->callConfirmDelete(),
                                    "Delete " . $this->Faction->name . " confirmation",
                                    "§fAre you sure you want to delete this faction? This action is irreversible"
                                ];
                            }else{
                                $data = [
                                    $this->callConfirmLeave(),
                                    "Leave " . $this->Faction->name . " confirmation",
                                    "§fAre you sure you want to leave this faction? This action is irreversible"
                                ];
                            }
                            Utils::processMenu(
                                RouterFactory::get(ConfirmationMenu::SLUG), 
                                $Player, 
                                $data
                            );
                            break;
                        default:
                            return;
                            break;
                    }
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function noFactionMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Main menu");
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

    private function factionMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::replaceParams("Main menu - {{factionName}}", ["factionName" => $this->Faction->name]));
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

    private function callConfirmLeave() : callable {
        $Faction = $this->Faction;
        return function (Player $Player, $data) use ($Faction) {
            if ($data === null) return;
            if ($data) {
                $message = '§2You have successfully left the faction';
                if (!MainAPI::removeMember($Faction->name, $Player->getName())) $message = "§4An error has occured";
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player);
            }
        };
    }

    private function callConfirmDelete() : callable {
        $Faction = $this->Faction;
        return function (Player $Player, $data) use ($Faction) {
            if ($data === null) return;
            if ($data) {
                $message = '§2You have successfully delete the faction';
                if (!MainAPI::removeFaction($Faction->name)) $message = "§4An error has occured";
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player);
            }
        };
    }

    private function generateButton(Player $player) {
        $permissions = $this->UserPermissions;
        $manageFaction = false;
        $manageMembers = false;
        $leavingButton = "§cLeave the faction";
        if (
                (isset($permissions[Ids::PERMISSION_ACCEPT_MEMBER_DEMAND]) && $permissions[Ids::PERMISSION_ACCEPT_MEMBER_DEMAND]) ||
                (isset($permissions[Ids::PERMISSION_CHANGE_MEMBER_RANK]) && $permissions[Ids::PERMISSION_CHANGE_MEMBER_RANK]) ||
                (isset($permissions[Ids::PERMISSION_DELETE_PENDING_MEMBER_INVITATION]) && $permissions[Ids::PERMISSION_DELETE_PENDING_MEMBER_INVITATION]) ||
                (isset($permissions[Ids::PERMISSION_KICK_MEMBER]) && $permissions[Ids::PERMISSION_KICK_MEMBER]) ||
                (isset($permissions[Ids::PERMISSION_REFUSE_MEMBER_DEMAND]) && $permissions[Ids::PERMISSION_REFUSE_MEMBER_DEMAND]) ||
                (isset($permissions[Ids::PERMISSION_SEND_MEMBER_INVITATION]) && $permissions[Ids::PERMISSION_SEND_MEMBER_INVITATION])
        ) $manageMembers = true;

        if (
                (isset($permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) && $permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) ||
                (isset($permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) && $permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) ||
                (isset($permissions[Ids::PERMISSION_BREAK_ALLIANCE]) && $permissions[Ids::PERMISSION_BREAK_ALLIANCE]) ||
                (isset($permissions[Ids::PERMISSION_CHANGE_FACTION_DESCRIPTION]) && $permissions[Ids::PERMISSION_CHANGE_FACTION_DESCRIPTION]) ||
                (isset($permissions[Ids::PERMISSION_CHANGE_FACTION_MESSAGE]) && $permissions[Ids::PERMISSION_CHANGE_FACTION_MESSAGE]) ||
                (isset($permissions[Ids::PERMISSION_CHANGE_FACTION_VISIBILITY]) && $permissions[Ids::PERMISSION_CHANGE_FACTION_VISIBILITY]) ||
                (isset($permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) ||
                (isset($permissions[Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS]) && $permissions[Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS]) ||
                (isset($permissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION]) && $permissions[Ids::PERMISSION_SEND_ALLIANCE_INVITATION])
        ) $manageFaction = true;

        if ($this->UserEntity->rank == Ids::OWNER_ID) {
            $manageMembers = true;
            $manageFaction = true;
            $leavingButton = "§cDelete the faction";
        }

            //TODO: Manage faction permissions
        $this->buttonsData = [
                [
                    'slug' => "bankView",
                    'text' => "View bank",
                    'access' => true
                ],[
                    'slug' => "factionMembers",
                    'text' => "View faction members",
                    'access' => true
                ],[
                    'slug' => "factionHome",
                    'text' => "View faction home",
                    'access' => true
                ],[
                    'slug' => "manageMembers",
                    'text' => "Manage members",
                    'access' => $manageMembers
                ],[
                    'slug' => "manageFaction",
                    'text' => "Manage faction",
                    'access' => $manageFaction
                ],[
                    'slug' => "factionsTop",
                    'text' => "Top factions",
                    'access' => true
                ],[
                    'slug' => "leavingButton",
                    'text' => $leavingButton,
                    'access' => true
                ],[
                    'slug' => "quit",
                    'text' => "§4Quit",
                    'access' => true
                ]
        ];
        
        $this->buttons = [];
        foreach ($this->buttonsData as $key => $permission) {
            if ($permission['access'] !== false) {
                $this->buttons[] = $permission["text"];
            }else{
                unset($this->buttonsData[$key]);
            }
        }
        $this->buttonsData = \array_values($this->buttonsData);
    }
}