<?php 

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance;

use InvalidArgumentException;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageAllianceDemand implements Route {
    
    const SLUG = "manageAllianceDemand";

    /** @var \jojoe77777\FormAPI\FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;
    /** @var UserEntity */
    private $victim;

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
        $permissions = MainAPI::getMemberPermission($player->getName());
        $UserEntity = MainAPI::getUser($player->getName());
        
        if (!isset($params[0]) || !$params[0] instanceof InvitationEntity) throw new InvalidArgumentException("Need the target player instance");
        $this->invitation = $params[0];

        $this->buttons = [];
        if ((array_key_exists(Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND, $permissions) && $permissions[Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND]) || $UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = "Accept the demand";
        if ((array_key_exists(Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND, $permissions) && $permissions[Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND]) || $UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = "Refuse the demand";
        $this->buttons[] = "§4Back";

        $menu = $this->manageAlliance($this->invitation);
        $menu->sendToPlayer($player);
    }

    public function call(): callable
    {
        $invitation = $this->invitation;
        return function (Player $player, $data) use ($invitation) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case "§4Back":
                    Utils::processMenu(RouterFactory::get(AllianceMainMenu::SLUG), $player);
                    break;
                case "Refuse the demand":
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callDelete($invitation->receiver, $invitation->sender),
                        "Delete demand confirmation",
                        "§fAre you sure you want to ignore this demand ?"
                    ]);
                    break;
                case "Accept the demand":
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callAccept($invitation->receiver, $invitation->sender),
                        "Accept demand confirmation",
                        "§fAre you sure you want to accept this demand ? You will become an ally"
                    ]);
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function manageAlliance(InvitationEntity $invitation) : SimpleForm {
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        if (count($this->buttons) == 1) {
            $menu->setContent(" §c>> §4You can't do anything");
        }
        $menu->setTitle("Manage " . $invitation->sender . " demand");
        return $menu;
    }

    private function callDelete(string $factionName, string $allianceName) : callable {
        $invitation = $this->invitation;
        return function (Player $Player, $data) use ($factionName, $allianceName, $invitation) {
            if ($data === null) return;
            if ($data) {
                $message = '§2You have successfully delete the demand of ' . $allianceName;
                if (!MainAPI::removeInvitation($allianceName, $factionName, "alliance")) $message = "§4An error has occured"; 
                Utils::processMenu(RouterFactory::get(AllianceMainMenu::SLUG), $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [$invitation]);
            }
        };
    }

    private function callAccept(string $factionName, string $allianceName) : callable {
        $invitation = $this->invitation;
        return function (Player $Player, $data) use ($factionName, $allianceName, $invitation) {
            if ($data === null) return;
            if ($data) {
                $message = '§2You have successfully accept the demand of ' . $allianceName;
                if (!MainAPI::setAlly($factionName, $allianceName)) $message = "§4An error has occured"; 
                if (!MainAPI::removeInvitation($allianceName, $factionName, "alliance")) $message = "§4An error has occured"; 
                Utils::processMenu(RouterFactory::get(AllianceMainMenu::SLUG), $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [$invitation]);
            }
        };
    }

}