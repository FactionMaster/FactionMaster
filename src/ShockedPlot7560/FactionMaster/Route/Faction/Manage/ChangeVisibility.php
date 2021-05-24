<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ChangeVisibility implements Route {

    const SLUG = "changeVisibility";

    /** @var \jojoe77777\FormAPI\FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;
    /** @var array */
    private $sliderData = [
        Ids::PUBLIC_VISIBILITY => "Public",
        Ids::PRIVATE_VISIBILITY => "Private",
        Ids::INVITATION_VISIBILITY => "Invitation"
    ];
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

    public function __invoke(Player $player, ?array $params = null)
    {
        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());
        
        $menu = $this->changeVisibility();
        $menu->sendToPlayer($player); 
    }

    public function call(): callable
    {
        return function (Player $player, $data)  {
            if ($data === null) return;
            if (MainAPI::changeVisibility($this->Faction->name, $data[0])) {
                Utils::processMenu(RouterFactory::get(ManageFactionMain::SLUG), $player, ['§2Successfully modified visibility ! ']);
            }else{
                Utils::processMenu(RouterFactory::get(ManageFactionMain::SLUG), $player, [' §c>> §4An error has occured']);
            }
        };
    }

    private function changeVisibility() : CustomForm {
        $menu = $this->FormUI->createCustomForm($this->call());
        $menu->addStepSlider("Choose the visibility", $this->sliderData, $this->Faction->visibility);
        $menu->setTitle("Change the visibility ");
        return $menu;
    }
}