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

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\VisibilityChangeEvent;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\CustomForm;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class VisibilityChangeRoute extends RouteBase {
	/** @deprecated */
	const SLUG = "visibilityChangeRoute";

	/** @var array */
	private $sliderData;

	public function getSlug(): string {
		return self::VISIBILITY_CHANGE_ROUTE;
	}

	public function getPermissions(): array {
		return [
			PermissionIds::PERMISSION_CHANGE_FACTION_VISIBILITY,
		];
	}

	public function getBackRoute(): ?Route {
		return RouterFactory::get(self::FACTION_OPTION_ROUTE);
	}

	public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
		$this->init($player, $userEntity, $userPermissions, $params);

		$this->sliderData = [
			Ids::PUBLIC_VISIBILITY => Utils::getText($this->getUserEntity()->getName(), "PUBLIC_VISIBILITY_NAME"),
			Ids::PRIVATE_VISIBILITY => Utils::getText($this->getUserEntity()->getName(), "PRIVATE_VISIBILITY_NAME"),
			Ids::INVITATION_VISIBILITY => Utils::getText($this->getUserEntity()->getName(), "INVITATION_VISIBILITY_NAME"),
		];

		$player->sendForm($this->getForm());
	}

	public function call(): callable {
		return function (Player $player, $data) {
			if ($data === null) {
				return;
			}

			$faction = $this->getFaction();
			$visibility = $data[0];
			MainAPI::changeVisibility($faction->getName(), $visibility);
			Utils::newMenuSendTask(new MenuSendTask(
				function () use ($faction, $visibility) {
					return MainAPI::getFaction($faction->getName())->getVisibilityId() == $visibility;
				},
				function () use ($player, $faction, $visibility) {
					$oldVisibility = $faction->getVisibilityId();
					$faction->setVisibility($visibility);
					(new VisibilityChangeEvent($player, $faction, $oldVisibility))->call();
					Utils::processMenu($this->getBackRoute(), $player, [Utils::getText($player->getName(), "SUCCESS_VISIBILITY_UPDATE")]);
				},
				function () use ($player) {
					Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ERROR")]);
				}
			));
		};
	}

	protected function getForm(): CustomForm {
		$menu = new CustomForm($this->call());
		$menu->addStepSlider(Utils::getText($this->getUserEntity()->getName(), "CHANGE_VISIBILITY_STEP"), $this->sliderData, $this->getFaction()->getVisibilityId());
		$menu->addLabel(Utils::getText($this->getUserEntity()->getName(), "CHANGE_VISIBILITY_EXPLICATION"));
		$menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "CHANGE_VISIBILITY_TITLE"));
		return $menu;
	}
}