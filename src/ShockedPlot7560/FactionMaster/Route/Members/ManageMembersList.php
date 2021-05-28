<?php

namespace ShockedPlot7560\FactionMaster\Route\Members;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageMembersList implements Route {

    const SLUG = "manageMembersList";

    public $PermissionNeed = [Ids::PERMISSION_CHANGE_MEMBER_RANK, Ids::PERMISSION_KICK_MEMBER];
    public $backMenu;

    /** @var FormAPI */
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
        $this->backMenu = RouterFactory::get(ManageMainMembers::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $message = "";
        $Faction = MainAPI::getFactionOfPlayer($player->getName());
        $this->UserEntity = $User;

        $this->buttons = [];
        foreach ($Faction->members as $key => $Member) {
            if ($key === $player->getName()) continue;
            if ($Member < $this->UserEntity->rank) {
                $this->buttons[] = $key;
            }
        }
        $this->buttons[] = "§4Back";

        if (isset($params[0])) $message = $params[0];
        if ((count($Faction->members) - 1) == 0) $message .= Utils::getText($this->UserEntity->name, "NO_MEMBERS");
        
        $menu = $this->manageMembersListMenu($message);
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($backMenu) {
            if ($data === null) return;
            if ($data == count($this->buttons) - 1) {
                Utils::processMenu($backMenu, $player);
                return;
            }
            if (isset($this->buttons[$data])) {
                Utils::processMenu(RouterFactory::get(ManageMember::SLUG), $player, [MainAPI::getUser($this->buttons[$data])]);
            }
            return;
        };
    }

    private function manageMembersListMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MANAGE_MEMBERS_LIST_PANEL_TITLE"));
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

}