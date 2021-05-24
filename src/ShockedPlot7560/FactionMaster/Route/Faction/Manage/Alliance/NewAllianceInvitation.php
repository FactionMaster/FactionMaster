<?php

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class NewAllianceInvitation implements Route {

    const SLUG = "allianceInvitationCreate";
    const CREATE_INVITATION_PANEL_NAME = "Send a new invitation";

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

    public function __invoke(Player $player, ?array $params = null)
    {
        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());

        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];
        $menu = $this->createInvitationMenu($message);
        $menu->sendToPlayer($player);
    }

    public function call() : callable{
        return function (Player $Player, $data) {
            if ($data === null) return;
            $FactionRequest = MainAPI::getFaction($data[1]);

            if ($data[1] !== "") {
                if ($FactionRequest instanceof FactionEntity) {
                    if (!MainAPI::areInInvitation($this->Faction->name, $data[1], "alliance")) {
                        if (MainAPI::makeInvitation($this->Faction->name, $data[1], "alliance")) {
                            Utils::processMenu(RouterFactory::get(AllianceMainMenu::SLUG), $Player, ['§2Sent invitation to ' . $data[1] . " successfuly" ] );
                        }else{
                            $menu = $this->createInvitationMenu(" §c>> §4An error has occured");
                            $menu->sendToPlayer($Player);
                        }
                    }else{
                        $menu = $this->createInvitationMenu(" §c>> §4You have already pending an invitation to this player");
                        $menu->sendToPlayer($Player);
                    }
                }else{
                    $menu = $this->createInvitationMenu(" §c>> §4This user don't exist");
                    $menu->sendToPlayer($Player);
                } 
            }else{
                Utils::processMenu(RouterFactory::get(AllianceMainMenu::SLUG), $Player);
            }
        };
    }

    private function createInvitationMenu(string $message = "") : CustomForm {
        $menu = $this->FormUI->createCustomForm($this->call());
        $menu->setTitle(self::CREATE_INVITATION_PANEL_NAME);
        $menu->addLabel($message . " \nTo go back, submit nothing");
        $menu->addInput("Name of the faction : ");
        return $menu;
    }
}