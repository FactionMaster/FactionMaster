<?php

namespace ShockedPlot7560\FactionMaster\Route\Members\Invitations;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class NewMemberInvitation implements Route {

    const SLUG = "memberInvitationCreate";

    public $PermissionNeed = [
        Ids::PERMISSION_SEND_MEMBER_INVITATION
    ];
    public $backMenu;

    /** @var \jojoe77777\FormAPI\FormAPI */
    private $FormUI;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(ManageMainMembers::SLUG);
    }

    /**
     * @param Player $player
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());

        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];
        $menu = $this->createInvitationMenu($message);
        $player->sendForm($menu);
    }

    public function call() : callable{
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($backMenu) {
            if ($data === null) return;
            $UserRequest = MainAPI::getUser($data[1]);

            if ($data[1] !== "") {
                if ($UserRequest instanceof UserEntity) {
                    if (!MainAPI::getFactionOfPlayer($data[1]) instanceof FactionEntity) {
                        if (!MainAPI::areInInvitation($this->Faction->name, $data[1], "member")) {
                            if (MainAPI::makeInvitation($this->Faction->name, $data[1], "member")) {
                                Utils::processMenu($backMenu, $Player, ['§2Sent invitation to ' . $data[1] . " successfuly" ] );
                            }else{
                                $menu = $this->createInvitationMenu(" §c>> §4An error has occured");
                                $Player->sendForm($menu);;
                            }
                        }else{
                            $menu = $this->createInvitationMenu(" §c>> §4You have already pending an invitation to this player");
                            $Player->sendForm($menu);;
                        }
                    }else{
                        $menu = $this->createInvitationMenu(" §c>> §4This player have already a faction");
                        $Player->sendForm($menu);;
                    }
                }else{
                    $menu = $this->createInvitationMenu(" §c>> §4This user don't exist");
                    $Player->sendForm($menu);;
                } 
            }else{
                Utils::processMenu($backMenu, $Player);
            }
        };
    }

    private function createInvitationMenu(string $message = "") : CustomForm {
        $menu = new CustomForm($this->call());
        $menu->setTitle("Send a new invitation");
        $menu->addLabel($message . " \nTo go back, submit nothing");
        $menu->addInput("Name of the player : ");
        return $menu;
    }
}