<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ChangeDescription implements Route {

    const SLUG = "changeDescription";

    /** @var \jojoe77777\FormAPI\FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;
    /** @var \ShockedPlot7560\FactionMaster\Main */
    private $Main;
    /** @var \ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity */
    private $Faction;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
    }

    /**
     * @param Player $player
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, ?array $params = null)
    {
        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];

        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());
        $menu = $this->changeDescriptionMenu($message);
        $menu->sendToPlayer($player);
    }

    public function call() : callable{
        return function (Player $Player, $data) {
            if ($data === null) return;
            if (isset($data[1]) && \is_string($data[1])) {
                if (MainAPI::changeDescription($this->Faction->name, $data[1])) {
                    Utils::processMenu(RouterFactory::get(ManageFactionMain::SLUG), $Player, ['§2Description successfully edited !']);
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