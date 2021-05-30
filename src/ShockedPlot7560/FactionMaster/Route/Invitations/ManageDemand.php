<?php 

namespace ShockedPlot7560\FactionMaster\Route\Invitations;

use InvalidArgumentException;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageDemand implements Route {
    
    const SLUG = "manageDemand";

    public $PermissionNeed = [];
    public $backMenu;

    /** @var array */
    private $buttons;
    /** @var InvitationEntity */
    private $invitation;


    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(MainPanel::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        if (!isset($params[0]) || !$params[0] instanceof InvitationEntity) throw new InvalidArgumentException("Need the invitation instance");
        $this->invitation = $params[0];

        $this->buttons = [];
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_ACCEPT_REQUEST");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_REFUSE_REQUEST");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");

        $menu = $this->manageInvitationMenu();
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $invitation = $this->invitation;
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($invitation, $backMenu) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case Utils::getText($this->UserEntity->name, "BUTTON_BACK"):
                    Utils::processMenu($backMenu, $player);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_REFUSE_REQUEST"):
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callDelete($invitation->sender, $invitation->receiver),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_TITLE_DELETE_REQUEST"),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_CONTENT_DELETE_REQUEST")
                    ]);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_ACCEPT_REQUEST"):
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callAccept($invitation->sender, $invitation->receiver),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_TITLE_ACCEPT_REQUEST"),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_CONTENT_ACCEPT_REQUEST")
                    ]);
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function manageInvitationMenu() : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        if (count($this->buttons) == 1) {
            $menu->setContent(Utils::getText($this->UserEntity->name, "NO_ACTION_POSSIBLE"));
        }
        $menu->setTitle(Utils::getText($this->UserEntity->name, "REQUEST_TITLE", ['name' => $this->invitation->sender]));
        return $menu;
    }

    private function callDelete(string $factionName, string $playerName) : callable {
        $invitation = $this->invitation;
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($factionName, $playerName, $invitation, $backMenu) {
            if ($data === null) return;
            if ($data) {
                $message = Utils::getText($this->UserEntity->name, "SUCCESS_DELETE_REQUEST", ['name' => $factionName]);
                if (!MainAPI::removeInvitation($factionName, $playerName, "member")) $message = Utils::getText($this->UserEntity->name, "ERROR"); 
                Utils::processMenu($backMenu, $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [$invitation]);
            }
        };
    }

    private function callAccept(string $factionName, string $playerName) : callable {
        $invitation = $this->invitation;
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($factionName, $playerName, $invitation, $backMenu) {
            if ($data === null) return;
            if ($data) {
                $Faction = MainAPI::getFaction($factionName);
                if (count($Faction->members) < $Faction->max_player) {
                    $message = Utils::getText($this->UserEntity->name, "SUCCESS_ACCEPT_REQUEST", ['name' => $factionName]);
                    if (!MainAPI::addMember($factionName, $playerName)) $message = Utils::getText($this->UserEntity->name, "ERROR"); 
                    if (!MainAPI::removeInvitation($factionName, $playerName, "member")) $message = Utils::getText($this->UserEntity->name, "ERROR"); 
                    Utils::processMenu($backMenu, $Player, [$message]);
                }else{
                    $message = Utils::getText($this->UserEntity->name, "MAX_PLAYER_REACH");
                    Utils::processMenu($backMenu, $Player, [$message]);
                }
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [$invitation]);
            }
        };
    }

}