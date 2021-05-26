<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ChangeDescription implements Route {

    const SLUG = "changeDescription";

    public $PermissionNeed = [
        Ids::PERMISSION_CHANGE_FACTION_DESCRIPTION
    ];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;
    /** @var FactionEntity */
    private $Faction;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(ManageFactionMain::SLUG);
    }

    /**
     * @param Player $player
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];

        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());
        $menu = $this->changeDescriptionMenu($message);
        $menu->sendToPlayer($player);
    }

    public function call() : callable{
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($backMenu) {
            if ($data === null) return;
            if (isset($data[1]) && \is_string($data[1])) {
                if (MainAPI::changeDescription($this->Faction->name, $data[1])) {
                    Utils::processMenu($backMenu, $Player, ['§2Description successfully edited !']);
                    return;
                }
            }
            $menu = $this->changeDescriptionMenu(" §c>> §4An error has occured");
            $menu->sendToPlayer($Player);
        };
    }

    private function changeDescriptionMenu(string $message = "") : CustomForm {
        $menu = $this->FormUI->createCustomForm($this->call());
        $menu->setTitle("Change the faction description");
        $menu->addLabel($message, $this->Faction->messageFaction);
        $menu->addInput("Enter your description below : ");
        return $menu;
    }
}