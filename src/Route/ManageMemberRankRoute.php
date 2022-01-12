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
use ShockedPlot7560\FactionMaster\Event\MemberChangeRankEvent;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\CustomForm;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageMemberRankRoute extends VictimBase implements Route {
	/** @deprecated */
	const SLUG = "manageMemberRankRoute";

	/** @var array */
	private $sliderData;

	public function getSlug(): string {
		return self::MANAGE_MEMBER_RANK_ROUTE;
	}

	public function getPermissions(): array {
		return [
			PermissionIds::PERMISSION_CHANGE_MEMBER_RANK
		];
	}

	public function getBackRoute(): ?Route {
		return RouterFactory::get(self::MANAGE_MEMBER_ROUTE);
	}

	private function getSliderData(): array {
		return $this->sliderData;
	}

	public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
		$this->init($player, $userEntity, $userPermissions, $params);
		if (!isset($params[0])) {
			throw new InvalidArgumentException("Need the target player instance");
		}

		$this->setVictim($params[0]);
		$this->sliderData = [
			Ids::RECRUIT_ID => Utils::getText($this->getVictim()->getName(), "RECRUIT_RANK_NAME"),
			Ids::MEMBER_ID => Utils::getText($this->getVictim()->getName(), "MEMBER_RANK_NAME"),
			Ids::COOWNER_ID => Utils::getText($this->getVictim()->getName(), "COOWNER_RANK_NAME"),
		];
		$player->sendForm($this->getForm());
	}

	public function call(): callable {
		return function (Player $player, $data) {
			if ($data === null) {
				return;
			}

			MainAPI::changeRank($this->getVictim()->getName(), $data[0]);
			$oldRank = $this->getVictim()->getRank();
			$this->getVictim()->setRank($data[0]);
			(new MemberChangeRankEvent($this->getFaction(), $this->getVictim(), $oldRank))->call();
			Utils::processMenu($this->getBackRoute(), $player, [$this->getVictim()]);
		};
	}

	protected function getForm(): CustomForm {
		$menu = new CustomForm($this->call());
		$menu->addStepSlider(Utils::getText($this->getUserEntity()->getName(), "MEMBER_CHANGE_RANK_PANEL_STEP"), $this->getSliderData(), $this->getVictim()->getRank());
		$menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "MEMBER_CHANGE_RANK_PANEL_TITLE", ['playerName' => $this->getVictim()->getName()]));
		return $menu;
	}
}