<?php 

namespace ShockedPlot7560\FactionMaster\Route\Invitations;

use InvalidArgumentException;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\ManageInvitationMain;
use ShockedPlot7560\FactionMaster\Route\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageDemand implements Route {
    
    const SLUG = "manageDemand";

    public $PermissionNeed = [];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;
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
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(MainPanel::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        if (!isset($params[0]) || !$params[0] instanceof InvitationEntity) throw new InvalidArgumentException("Need the invitation instance");
        $this->invitation = $params[0];

        $this->buttons = [];
        $this->buttons[] = "Accept the demand";
        $this->buttons[] = "Refuse the demand";
        $this->buttons[] = "§4Back";

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
                case "§4Back":
                    Utils::processMenu($backMenu, $player);
                    break;
                case "Refuse the demand":
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callDelete($invitation->sender, $invitation->receiver),
                        "Delete demand confirmation",
                        "§fAre you sure you want to ignore this demand ?"
                    ]);
                    break;
                case "Accept the demand":
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callAccept($invitation->sender, $invitation->receiver),
                        "Accept demand confirmation",
                        "§fAre you sure you want to accept this demand ?"
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
            $menu->setContent(" §c>> §4You can't do anything");
        }
        $menu->setTitle("Manage " . $this->invitation->sender . " demand");
        return $menu;
    }

    private function callDelete(string $factionName, string $playerName) : callable {
        $invitation = $this->invitation;
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($factionName, $playerName, $invitation, $backMenu) {
            if ($data === null) return;
            if ($data) {
                $message = '§2You have successfully delete the demand of ' . $playerName;
                if (!MainAPI::removeInvitation($factionName, $playerName, "member")) $message = "§4An error has occured"; 
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
                $message = '§2You have successfully accept the demand of ' . $playerName;
                \var_dump($message);
                if (!MainAPI::addMember($factionName, $playerName)) $message = "§4An error has occured"; 
                \var_dump($message);
                if (!MainAPI::removeInvitation($factionName, $playerName, "member")) $message = "§4An error has occured"; 
                \var_dump($message);
                Utils::processMenu($backMenu, $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [$invitation]);
            }
        };
    }

}