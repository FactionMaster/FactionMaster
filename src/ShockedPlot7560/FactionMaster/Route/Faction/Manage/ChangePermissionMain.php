<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ChangePermissionMain implements Route {

    const SLUG = "changePermissionMain";

    public $PermissionNeed = [
        Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS
    ];
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
        $this->backMenu = RouterFactory::get(MainPanel::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];
        $this->buttons = [
            "Recruit",
            "Member",
            "Co-owner",
            "§4Back"
        ];
        $menu = $this->changePermissionMenu($message);
        $player->sendForm($menu);
    }

    public function call() : callable{
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($backMenu) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case "Recruit":
                    Utils::processMenu(RouterFactory::get(RankPermissionManage::SLUG), $Player, [Ids::RECRUIT_ID]);
                    break;
                case "Member":
                    Utils::processMenu(RouterFactory::get(RankPermissionManage::SLUG), $Player, [Ids::MEMBER_ID]);
                    break;
                case "Co-owner":
                    Utils::processMenu(RouterFactory::get(RankPermissionManage::SLUG), $Player, [Ids::COOWNER_ID]);
                    break;
                case "§4Back";
                    Utils::processMenu($backMenu, $Player);
                    break;
            }
        };
    }

    private function changePermissionMenu(string $message = "") : SimpleForm {
        $menu =new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Select a role to manage");
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }
}