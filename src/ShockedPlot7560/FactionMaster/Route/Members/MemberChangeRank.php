<?php 

namespace ShockedPlot7560\FactionMaster\Route\Members;

use InvalidArgumentException;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MemberChangeRank implements Route {
    
    const SLUG = "memberChangeRank";

    public $PermissionNeed = [Ids::PERMISSION_CHANGE_MEMBER_RANK];
    public $backMenu;

    /** @var FormAPI */
    private $FormUI;
    /** @var array */
    private $sliderData = [
        Ids::RECRUIT_ID => "Recruit",
        Ids::MEMBER_ID => "Member",
        Ids::COOWNER_ID => "Co-owner"
    ];
    /** @var UserEntity */
    private $victim;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(ManageMember::SLUG);
        $this->FormUI = Main::getInstance()->FormUI;
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        if (!isset($params[0])) throw new InvalidArgumentException("Need the target player instance");
        $this->victim = $params[0];

        $menu = $this->changeRankMenu($this->victim);
        $menu->sendToPlayer($player); 
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($backMenu) {
            if ($data === null) return;
            MainAPI::changeRank($this->victim->name, $data[0]);
            $this->victim->rank = $data[0];
            Utils::processMenu($backMenu, $player, [ $this->victim ]);
        };
    }

    private function changeRankMenu(UserEntity $Victim) : CustomForm {
        $menu = $this->FormUI->createCustomForm($this->call());
        $menu->addStepSlider("Choose the rank", $this->sliderData, $Victim->rank);
        $menu->setTitle("Change the role " . $Victim->name);
        return $menu;
    }
}