<?php 

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance;

use InvalidArgumentException;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageAlliance implements Route {
    
    const SLUG = "manageAlliance";

    public $PermissionNeed = [
        Ids::PERMISSION_BREAK_ALLIANCE
    ];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;
    /** @var array */
    private $buttons;
    /** @var FactionEntity */
    private $alliance;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(AllianceMainMenu::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        if (!isset($params[0]) || !$params[0] instanceof FactionEntity) throw new InvalidArgumentException("Need the target faction instance");
        $this->alliance = $params[0];

        $this->buttons = [];
        if ((isset($UserPermissions[Ids::PERMISSION_BREAK_ALLIANCE]) && $UserPermissions[Ids::PERMISSION_BREAK_ALLIANCE]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = "§4Break alliance";
        $this->buttons[] = "§4Back";

        $menu = $this->manageAlliance();
        $menu->sendToPlayer($player);
    }

    public function call(): callable
    {
        $alliance = $this->alliance;
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($alliance, $backMenu) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case "§4Back":
                    Utils::processMenu($backMenu, $player);
                    break;
                case "§4Break alliance":
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callKick($alliance->name),
                        "Break alliance confirmation",
                        "§fAre you sure you want to break the alliance with this faction ? This action is irreversible"
                    ]);
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function manageAlliance() : SimpleForm {
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Manage " . $this->alliance->name);
        return $menu;
    }

    private function callKick(string $targetName) : callable {
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($targetName, $backMenu) {
            $Faction = MainAPI::getFactionOfPlayer($Player->getName());
            if ($data === null) return;
            if ($data) {
                $message = '§2You have successfully break with ' . $targetName;
                if (!MainAPI::removeAlly($Faction->name, $targetName)) $message = "§4An error has occured"; 
                Utils::processMenu($backMenu, $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player);
            }
        };
    }
}