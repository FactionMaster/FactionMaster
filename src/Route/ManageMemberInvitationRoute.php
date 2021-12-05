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
use ShockedPlot7560\FactionMaster\libs\jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionFactory;
use ShockedPlot7560\FactionMaster\Button\Collection\ManageMemberInvitationCollection;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageMemberInvitationRoute extends InvitationBase implements Route {

    const SLUG = "manageMemberInvitationRoute";

    public function getSlug(): string {
        return self::SLUG;
    }

    public function getPermissions(): array {
        return [
            PermissionIds::PERMISSION_DELETE_PENDING_MEMBER_INVITATION
        ];
    }

    public function getBackRoute(): ?Route {
        return RouterFactory::get(MembersInvitationSendRoute::SLUG);
    }

    public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
        $this->init($player, $userEntity, $userPermissions, $params);

        if (!isset($params[0]) || !$params[0] instanceof InvitationEntity) {
            throw new InvalidArgumentException("Need the invitation instance");
        }

        $this->setInvitation($params[0]);
        $this->setCollection(CollectionFactory::get(ManageMemberInvitationCollection::SLUG)->init($this->getPlayer(), $this->getUserEntity(), $this->getInvitation()));

        $player->sendForm($this->getForm());
    }

    public function call(): callable {
        return function (Player $player, $data) {
            if ($data === null) {
                return;
            }
            $this->getCollection()->process($data, $player);
        };
    }

    protected function getForm(): SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = $this->getCollection()->generateButtons($menu, $this->getUserEntity()->getName());
        $menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "MANAGE_MEMBER_INVITATION_TITLE", ['name' => $this->getInvitation()->getReceiverString()]));
        return $menu;
    }
}