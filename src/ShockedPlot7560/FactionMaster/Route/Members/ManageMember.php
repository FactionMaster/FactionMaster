<?php 

namespace ShockedPlot7560\FactionMaster\Route\Members;

use InvalidArgumentException;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageMember implements Route {
    
    const SLUG = "manageMember";

    public $PermissionNeed = [Ids::PERMISSION_CHANGE_MEMBER_RANK, Ids::PERMISSION_KICK_MEMBER];
    public $backMenu;

    /** @var FormAPI */
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
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(ManageMembersList::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        if (!isset($params[0]) || !$params[0] instanceof UserEntity) throw new InvalidArgumentException("Need the target player instance");
        $this->victim = $params[0];

        $this->buttons = [];
        if ((isset($UserPermissions[Ids::PERMISSION_CHANGE_MEMBER_RANK]) && $UserPermissions[Ids::PERMISSION_CHANGE_MEMBER_RANK]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = "Change the rank";
        if ((isset($UserPermissions[Ids::PERMISSION_KICK_MEMBER]) && $UserPermissions[Ids::PERMISSION_KICK_MEMBER]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = "§4Kick out";
        if ($User->rank == Ids::OWNER_ID) $this->buttons[] = "§cTransfer the property";
        $this->buttons[] = "§4Back";

        $menu = $this->manageMember();
        $menu->sendToPlayer($player);
    }

    public function call(): callable
    {
        $victim = $this->victim;
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($victim, $backMenu) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case "§4Back":
                    Utils::processMenu($backMenu, $player);
                    break;
                case "§cTransfer the property":
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callTransferProperty($player->getName(), $victim->name),
                        "Transfer property confirmation",
                        "§fAre you sure you want to transfer the own of this faction? This action is irreversible and will set you has a coowner"
                    ]);
                    break;
                case "§4Kick out":
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callKick($victim->name),
                        "Kick out confirmation",
                        "§fAre you sure you want to kick out of this faction? This action is irreversible"
                    ]);
                    break;
                case "Change the rank":
                    Utils::processMenu(RouterFactory::get(MemberChangeRank::SLUG), $player, [ $victim ]);
                    break;
                default:
                    Utils::processMenu($this->backMenu, $player);
                    break;
            }
        };
    }

    private function manageMember() : SimpleForm {
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Manage " . $this->victim->name);
        return $menu;
    }

    private function callTransferProperty(string $oldOwner, string $newOwner) : callable {
        return function (Player $Player, $data) use ($oldOwner, $newOwner) {
            if ($data === null) return;
            if ($data) {
                $message = '§2You have successfully give the property to ' . $newOwner;
                if (!MainAPI::changeRank($oldOwner, Ids::COOWNER_ID)) $message = "§4An error has occured"; 
                if (!MainAPI::changeRank($newOwner, Ids::OWNER_ID)) $message = "§4An error has occured";
                Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player);
            }
        };
    }

    private function callKick(string $targetName) : callable {
        return function (Player $Player, $data) use ($targetName) {
            $Faction = MainAPI::getFactionOfPlayer($Player->getName());
            if ($data === null) return;
            if ($data) {
                $message = '§2You have successfully kick ' . $targetName;
                if (!MainAPI::removeMember($Faction->name, $targetName)) $message = "§4An error has occured"; 
                Utils::processMenu($this->backMenu, $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player);
            }
        };
    }
}