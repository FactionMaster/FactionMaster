<?php

namespace ShockedPlot7560\FactionMaster\Route;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class CreateFactionPanel implements Route {

    const SLUG = "createFaction";
    const CREATE_FACTION_PANEL_NAME = "Main menu";

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
        $menu = $this->createFactionMenu($message);
        $menu->sendToPlayer($player);
    }

    public function call() : callable{
        return function (Player $Player, $data) {
            if ($data === null) return;
            $FactionRequest = MainAPI::getFaction($data[1]);

            if (!$FactionRequest instanceof FactionEntity) {
                if (MainAPI::addFaction($data[1], $Player->getName())) {
                    Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player, ['§2Successfully created faction !']);
                }else{
                    $menu = $this->createFactionMenu(" §c>> §4An error has occured");
                    $menu->sendToPlayer($Player);
                }
            }else{
                $menu = $this->createFactionMenu(" §c>> §4This name is already used");
                $menu->sendToPlayer($Player);
            } 
            
        };
    }

    private function createFactionMenu(string $message = "") : CustomForm {
        $menu = $this->FormUI->createCustomForm($this->call());
        $menu->setTitle(self::CREATE_FACTION_PANEL_NAME);
        $menu->addLabel($message);
        $menu->addInput("Name of the faction : ");
        return $menu;
    }
}