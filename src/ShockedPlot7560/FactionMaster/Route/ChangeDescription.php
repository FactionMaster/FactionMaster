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

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\DescriptionChangeEvent;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ChangeDescription implements Route {

    const SLUG = "changeDescription";

    public $PermissionNeed = [
        PermissionIds::PERMISSION_CHANGE_FACTION_DESCRIPTION,
    ];
    public $backMenu;

    /** @var FactionEntity */
    private $Faction;
    /** @var UserEntity */
    private $UserEntity;

    public function getSlug(): string {
        return self::SLUG;
    }

    public function __construct() {
        $this->backMenu = RouterFactory::get(ManageFactionMain::SLUG);
    }

    /**
     * @param Player $player
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null) {
        $this->UserEntity = $User;
        $message = "";
        if (isset($params[0]) && \is_string($params[0])) {
            $message = $params[0];
        }

        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());
        $menu = $this->changeDescriptionMenu($message);
        $player->sendForm($menu);
    }

    public function call(): callable {
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($backMenu) {
            if ($data === null) {
                return;
            }

            if (isset($data[1]) && \is_string($data[1])) {
                $Faction = $this->Faction;
                $description = $data[1];
                MainAPI::changeDescription($Faction->name, $description);
                Utils::newMenuSendTask(new MenuSendTask(
                    function () use ($Faction, $description) {
                        return MainAPI::getFaction($Faction->name)->description === $description;
                    },
                    function () use ($Player, $Faction, $description, $backMenu) {
                        (new DescriptionChangeEvent($Player, $Faction, $description))->call();
                        Utils::processMenu($backMenu, $Player, [Utils::getText($Player->getName(), "SUCCESS_DESCRIPTION_UPDATE")]);
                    },
                    function () use ($Player) {
                        Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                    }
                ));
                return;
            }
            $menu = $this->changeDescriptionMenu(Utils::getText($Player->getName(), "ERROR"));
            $Player->sendForm($menu);
        };
    }

    private function changeDescriptionMenu(string $message = ""): CustomForm {
        $menu = new CustomForm($this->call());
        $menu->setTitle(Utils::getText($this->UserEntity->name, "CHANGE_DESCRIPTION_TITLE"));
        $menu->addLabel($message, $this->Faction->messageFaction);
        $menu->addInput(Utils::getText($this->UserEntity->name, "CHANGE_DESCRIPTION_INPUT_CONTENT"), "", $this->Faction->description);
        return $menu;
    }
}