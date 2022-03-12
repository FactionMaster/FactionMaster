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
use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\PermissionChangeEvent;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\CustomForm;
use ShockedPlot7560\FactionMaster\Manager\PermissionManager;
use ShockedPlot7560\FactionMaster\Permission\Permission;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function is_int;

class PermissionChangeRoute extends RouteBase {
	/** @deprecated */
	const SLUG = "permissionChangeRoute";

	/** @var Permission[] */
	private $permissionsData;
	/** @var int */
	private $rank;

	public function getSlug(): string {
		return self::PERMISSION_CHANGE_ROUTE;
	}

	public function getPermissions(): array {
		return [
			PermissionIds::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS
		];
	}

	public function getBackRoute(): ?Route {
		return RouterFactory::get(self::MANAGE_PERMISSION_ROUTE);
	}

	protected function getRank(): int {
		return $this->rank;
	}

	/** @return Permission[] */
	protected function getAllPermissions(): array {
		return $this->permissionsData;
	}

	public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
		$this->init($player, $userEntity, $userPermissions, $params);

		if (!isset($params[0]) || !is_int($params[0])) {
			throw new InvalidArgumentException("Please give the rank id in the first item of the \$params");
		}
		$this->rank = $params[0];
		$this->permissionsData = PermissionManager::getAll();

		$check = [];
		foreach ($this->getAllPermissions() as $key => $permission) {
			if ($this->getUserEntity()->getRank() == Ids::OWNER_ID ||
					(isset($this->getUserPermissions()[$permission->getId()]) && $this->getUserPermissions()[$permission->getId()] === true)) {
				$check[] = $permission->getName($this->getPlayer()->getName());
			} else {
				unset($this->permissionsData[$key]);
			}
		}
		$player->sendForm($this->getForm());
	}

	public function call() : callable {
		return function (Player $player, $data) {
			if ($data === null) {
				return;
			}
			$i = 0;
			$faction = $this->getFaction();
			$oldPermission = $faction->getPermissions();
			foreach ($this->getAllPermissions() as $permission) {
				$permissions = $faction->getPermissions();
				$permissions[$this->getRank()][$permission->getId()] = $data[$i];
				$faction->setPermissions($permissions);
				$i++;
			}
			if ($faction->getPermissions() === $oldPermission) {
				Utils::processMenu($this->getBackRoute(), $player);
				return;
			}
			MainAPI::updatePermissionFaction($faction->getName(), $faction->getPermissions());
			Utils::newMenuSendTask(new MenuSendTask(
				function () use ($faction, $oldPermission) {
					return MainAPI::getFaction($faction->getName())->getPermissions() !== $oldPermission;
				},
				function () use ($player, $faction, $oldPermission) {
					(new PermissionChangeEvent($player, $faction, $oldPermission))->call();
					Utils::processMenu($this->getBackRoute(), $player, [Utils::getText($player->getName(), "SUCCESS_PERMISSION_UPDATE")]);
				},
				function () use ($player) {
					Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ERROR")]);
				}
			));
		};
	}

	protected function getForm() : CustomForm {
		$menu = new CustomForm($this->call());
		$menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "MANAGE_PERMISSION_MAIN_TITLE"));
		$rankFactionPermission = $this->getFaction()->getPermissions()[$this->getRank()];
		foreach ($this->getAllPermissions() as $permission) {
			$menu->addToggle($permission->getName($this->getUserEntity()->getName()), $rankFactionPermission[$permission->getId()] ?? false);
		}
		return $menu;
	}
}
