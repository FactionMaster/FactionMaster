<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ChangePermissionMain implements Route {

    const SLUG = "changePermissionMain";
    const CHOOSE_ROLE_PERMISSION_PANEL_NAME = "Choose the rank";

    /** @var \jojoe77777\FormAPI\FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;
    /** @var \ShockedPlot7560\FactionMaster\Main */
    private $Main;

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
        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];
        $this->buttons = [
            "Recruit",
            "Member",
            "Co-owner",
            "§4Back"
        ];
        $menu = $this->changePermissionMenu($message);
        $menu->sendToPlayer($player);
    }

    public function call() : callable{
        return function (Player $Player, $data) {
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
                default:
                    Utils::processMenu(RouterFactory::get(ManageFactionMain::SLUG), $Player);
                    break;
            }
        };
    }

    private function changePermissionMenu(string $message = "") : SimpleForm {
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(self::CHOOSE_ROLE_PERMISSION_PANEL_NAME);
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }
}