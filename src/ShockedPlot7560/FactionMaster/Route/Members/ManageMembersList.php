<?php

namespace ShockedPlot7560\FactionMaster\Route\Members;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageMembersList implements Route {

    const SLUG = "manageMembersList";

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
        $Main = Main::getInstance();
        $this->FormUI = $Main->FormUI;
    }

    public function __invoke(Player $player, ?array $params = null)
    {
        $message = "";
        $Faction = MainAPI::getFactionOfPlayer($player->getName());
        $UserEntity = MainAPI::getUser($player->getName());
        $this->buttons = [];
        foreach ($Faction->members as $key => $Member) {
            if ($key === $player->getName()) continue;
            if ($Member < $UserEntity->rank) {
                $this->buttons[] = $key;
            }
        }
        $this->buttons[] = "§4Back";
        if (isset($params[0])) $message = $params[0];
        if ((count($Faction->members) - 1) == 0) $message .= "\n \n§4No editable members to display";
        $menu = $this->manageMainMembersMenu($message);
        $menu->sendToPlayer($player);
    }

    public function call(): callable
    {
        return function (Player $player, $data) {
            if ($data === null) return;
            if ($data == count($this->buttons) - 1) {
                Utils::processMenu(RouterFactory::get(ManageMainMembers::SLUG), $player);
                return;
            }
            if (isset($this->buttons[$data])) {
                Utils::processMenu(RouterFactory::get(ManageMember::SLUG), $player, [MainAPI::getUser($this->buttons[$data])]);
            }
            return;
        };
    }

    private function manageMainMembersMenu(string $message = "") : SimpleForm {
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Manage members - list");
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

}