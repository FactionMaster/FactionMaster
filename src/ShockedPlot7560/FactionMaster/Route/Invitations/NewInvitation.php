<?php

namespace ShockedPlot7560\FactionMaster\Route\Invitations;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\ManageInvitationMain;
use ShockedPlot7560\FactionMaster\Route\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class NewInvitation implements Route {

    const SLUG = "invitationCreate";

    public $PermissionNeed = [];
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
        $this->backMenu = RouterFactory::get(ManageInvitationMain::SLUG);
    }

    /**
     * @param Player $player
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];
        $menu = $this->createInvitationMenu($message);
        $player->sendForm($menu);
    }

    public function call() : callable{
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($backMenu) {
            if ($data === null) return;
            $FactionRequest = MainAPI::getFaction($data[1]);

            if ($data[1] !== "") {
                if ($FactionRequest instanceof FactionEntity) {
                    if (!MainAPI::getFactionOfPlayer($Player->getName()) instanceof FactionEntity) {
                        if (!MainAPI::areInInvitation($Player->getName(), $data[1], "member")) {
                            if (MainAPI::makeInvitation($Player->getName(), $data[1], "member")) {
                                Utils::processMenu($backMenu, $Player, [Utils::getText($this->UserEntity->name, "SUCCESS_SEND_INVITATION", ['name' => $data[1]])] );
                            }else{
                                $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "ERROR"));
                                $Player->sendForm($menu);;
                            }
                        }else{
                            $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "ALREADY_PENDING_INVITATION"));
                            $Player->sendForm($menu);;
                        }
                    }else{
                        $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "ALREADY_IN_THIS_FACTION"));
                        $Player->sendForm($menu);;
                    }
                }else{
                    $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "FACTION_DONT_EXIST"));
                    $Player->sendForm($menu);;
                } 
            }else{
                Utils::processMenu($backMenu, $Player);
            }
        };
    }

    private function createInvitationMenu(string $message = "") : CustomForm {
        $menu = new CustomForm($this->call());
        $menu->setTitle(Utils::getText($this->UserEntity->name, "SEND_INVITATION_PANEL_TITLE"));
        $menu->addLabel(Utils::getText($this->UserEntity->name, "SEND_INVITATION_PANEL_CONTENT") . "\n" . $message);
        $menu->addInput(Utils::getText($this->UserEntity->name, "SEND_INVITATION_PANEL_INPUT_CONTENT_FACTION"));
        return $menu;
    }
}