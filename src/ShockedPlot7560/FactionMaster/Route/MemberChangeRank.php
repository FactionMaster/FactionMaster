<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author ShockedPlot7560
 * @link https://github.com/ShockedPlot7560
 *
 *
 */

namespace ShockedPlot7560\FactionMaster\Route;

use InvalidArgumentException;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\MemberChangeRankEvent;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MemberChangeRank implements Route {

    const SLUG = "memberChangeRank";

    public $PermissionNeed = [PermissionIds::PERMISSION_CHANGE_MEMBER_RANK];
    public $backMenu;

    /** @var array */
    private $sliderData;
    /** @var UserEntity */
    private $victim;

    /** @var UserEntity */
    private $UserEntity;

    public function getSlug(): string {
        return self::SLUG;
    }

    public function __construct() {
        $this->backMenu = RouterFactory::get(ManageMember::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null) {
        $this->UserEntity = $User;
        if (!isset($params[0])) {
            throw new InvalidArgumentException("Need the target player instance");
        }

        $this->victim = $params[0];
        $this->sliderData = [
            Ids::RECRUIT_ID => Utils::getText($this->victim->name, "RECRUIT_RANK_NAME"),
            Ids::MEMBER_ID => Utils::getText($this->victim->name, "MEMBER_RANK_NAME"),
            Ids::COOWNER_ID => Utils::getText($this->victim->name, "COOWNER_RANK_NAME"),
        ];

        $menu = $this->changeRankMenu($this->victim);
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($backMenu) {
            if ($data === null) {
                return;
            }

            MainAPI::changeRank($this->victim->name, $data[0]);
            $this->victim->rank = $data[0];
            (new MemberChangeRankEvent($player, $this->victim, $this->victim->rank))->call();
            Utils::processMenu($backMenu, $player, [$this->victim]);
        };
    }

    private function changeRankMenu(UserEntity $Victim): CustomForm {
        $menu = new CustomForm($this->call());
        $menu->addStepSlider(Utils::getText($this->UserEntity->name, "MEMBER_CHANGE_RANK_PANEL_STEP"), $this->sliderData, $Victim->rank);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MEMBER_CHANGE_RANK_PANEL_TITLE", ['playerName' => $Victim->name]));
        return $menu;
    }
}