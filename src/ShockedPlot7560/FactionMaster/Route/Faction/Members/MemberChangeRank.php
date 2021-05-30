<?php 

namespace ShockedPlot7560\FactionMaster\Route\Faction\Members;

use InvalidArgumentException;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MemberChangeRank implements Route {
    
    const SLUG = "memberChangeRank";

    public $PermissionNeed = [Ids::PERMISSION_CHANGE_MEMBER_RANK];
    public $backMenu;

    /** @var array */
    private $sliderData;
    /** @var UserEntity */
    private $victim;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(ManageMember::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        $this->sliderData = [
            Ids::RECRUIT_ID => Utils::getText($player->name, "RECRUIT_RANK_NAME"),
            Ids::MEMBER_ID => Utils::getText($player->name, "MEMBER_RANK_NAME"),
            Ids::COOWNER_ID => Utils::getText($player->name, "COOWNER_RANK_NAME")
        ];
        if (!isset($params[0])) throw new InvalidArgumentException("Need the target player instance");
        $this->victim = $params[0];

        $menu = $this->changeRankMenu($this->victim);
        $player->sendForm($menu);
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
        $menu = new CustomForm($this->call());
        $menu->addStepSlider(Utils::getText($this->UserEntity->name, "MEMBER_CHANGE_RANK_PANEL_STEP"), $this->sliderData, $Victim->rank);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MEMBER_CHANGE_RANK_PANEL_TITLE", ['playerName' => $Victim->name]));
        return $menu;
    }
}