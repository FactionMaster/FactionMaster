<?php 

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance;

use InvalidArgumentException;
use jojoe77777\FormAPI\SimpleForm;
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

class ManageAlliance implements Route {
    
    const SLUG = "manageAlliance";

    /** @var \jojoe77777\FormAPI\FormAPI */
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
        $Main = Main::getInstance();
        $this->FormUI = $Main->FormUI;
    }

    public function __invoke(Player $player, ?array $params = null)
    {
        $permissions = MainAPI::getMemberPermission($player->getName());
        $UserEntity = MainAPI::getUser($player->getName());
        
        if (!isset($params[0])) throw new InvalidArgumentException("Need the target player instance");
        $this->alliance = $params[0];

        $this->buttons = [];
        if ((array_key_exists(Ids::PERMISSION_BREAK_ALLIANCE, $permissions) && $permissions[Ids::PERMISSION_BREAK_ALLIANCE]) || $UserEntity->rank == Ids::OWNER_ID) $this->buttons[] = "§4Break alliance";
        $this->buttons[] = "§4Back";

        $menu = $this->manageAlliance($this->alliance);
        $menu->sendToPlayer($player);
    }

    public function call(): callable
    {
        $alliance = $this->alliance;
        return function (Player $player, $data) use ($alliance) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case "§4Back":
                    Utils::processMenu(RouterFactory::get(AllianceMainMenu::SLUG), $player);
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

    private function manageAlliance(UserEntity $user) : SimpleForm {
        $menu = $this->FormUI->createSimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle("Manage " . $user->name);
        return $menu;
    }

    private function callKick(string $targetName) : callable {
        return function (Player $Player, $data) use ($targetName) {
            $Faction = MainAPI::getFactionOfPlayer($Player->getName());
            if ($data === null) return;
            if ($data) {
                $message = '§2You have successfully break with ' . $targetName;
                if (!MainAPI::removeAlly($Faction->name, $targetName)) $message = "§4An error has occured"; 
                Utils::processMenu(RouterFactory::get(AllianceMainMenu::SLUG), $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player);
            }
        };
    }
}