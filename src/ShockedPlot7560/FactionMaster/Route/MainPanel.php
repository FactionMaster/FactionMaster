<?php

namespace ShockedPlot7560\FactionMaster\Route;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Faction\Manage\ChangePermissionMain;
use ShockedPlot7560\FactionMaster\Route\Faction\Manage\ManageFactionMain;
use ShockedPlot7560\FactionMaster\Route\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MainPanel implements Route {

    const SLUG = "main";
    const NO_FACTION_PANEL_NAME = "Main menu";
    const WITH_FACTION_PANEL_NAME = "Main menu - {{factionName}}";

    const NO_FACTION_TYPE = 0;
    const FACTION_TYPE = 1;

    /** @var \jojoe77777\FormAPI\FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;
    
    private $UserEntity;
    private $menuType;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $Main = Main::getInstance();
        $this->FormUI = $Main->FormUI;
    }

    public function __invoke(Player $player, ?array $params = null)
    {
        $this->UserEntity = MainAPI::getUser($player->getName());
        $message = '';
        if (!$this->UserEntity instanceof UserEntity || $this->UserEntity->faction === null) {
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
            $this->generateButton($player);
            $this->menuType = self::FACTION_TYPE;
            if (isset($params[0])) $message = $params[0];
            $menu = $this->factionMenu(MainAPI::getFaction($this->UserEntity->faction), $message);
        }
        $menu->sendToPlayer($player);
    }

    public function call() : callable{
        return function (Player $Player, $data) {
            if ($data === null) return;
            switch ($this->menuType) {
                case self::NO_FACTION_TYPE:
                    switch ($data) {
                        case 0:
                            //TODO: Invitation panel
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
                            $Faction = MainAPI::getFactionOfPlayer($Player->getName());
                            if ($this->UserEntity->rank == Ids::OWNER_ID) {
                                $data = [
                                    $this->callConfirmDelete($Faction),
                                    "Delete " . $Faction->name . " confirmation",
                                    "§fAre you sure you want to delete this faction? This action is irreversible"
                                ];
                            }else{
                                $data = [
                                    $this->callConfirmLeave($Faction),
                                    "Leave " . $Faction->name . " confirmation",
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
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(self::NO_FACTION_PANEL_NAME);
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

    private function factionMenu(FactionEntity $Faction, string $message = "") : SimpleForm {
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::replaceParams(self::WITH_FACTION_PANEL_NAME, ["factionName" => $Faction->name]));
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

    private function callConfirmLeave(FactionEntity $Faction) : callable {
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

    private function callConfirmDelete(FactionEntity $Faction) : callable {
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
        $permissions = MainAPI::getMemberPermission($player->getName());
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