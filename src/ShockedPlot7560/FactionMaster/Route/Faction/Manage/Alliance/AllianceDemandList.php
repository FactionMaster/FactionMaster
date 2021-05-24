<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance\AllianceMainMenu;
use ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance\ManageAllianceDemand;
use ShockedPlot7560\FactionMaster\Route\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class AllianceDemandList implements Route {

    const SLUG = "allianceDemandList";

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
        $this->Invitations = MainAPI::getInvitationsByReceiver($Faction->name, "alliance");
        $this->buttons = [];
        foreach ($this->Invitations as $key => $Invitation) {
            $this->buttons[] = $Invitation->sender;
        }
        $this->buttons[] = "§4Back";
        if (isset($params[0])) $message = $params[0];
        if (count($this->Invitations) == 0) $message .= "\n \n§4No pending demand";
        $menu = $this->allianceDemandList($message);
        $menu->sendToPlayer($player);
    }

    public function call(): callable
    {
        return function (Player $player, $data) {
            if ($data === null) return;
            if ($data == count($this->buttons) - 1) {
                Utils::processMenu(RouterFactory::get(AllianceMainMenu::SLUG), $player);
                return;
            }
            if (isset($this->buttons[$data])) {
                Utils::processMenu(RouterFactory::get(ManageAllianceDemand::SLUG), $player, [$this->Invitations[$data]]);
            }
            return;
        };
    }

    private function allianceDemandList(string $message = "") : SimpleForm {
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Demand list");
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }


}