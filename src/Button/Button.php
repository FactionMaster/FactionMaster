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

namespace ShockedPlot7560\FactionMaster\Button;

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\SimpleForm;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function call_user_func;
use function count;
use function is_array;

abstract class Button implements ButtonSlug {

	/** @var string */
	private $slug;
	/** @var callable */
	private $content;
	/** @var int[] */
	private $permissions = [];
	/** @var callable */
	private $callable;
	/** @var string */
	private $imgPath = "";
	/** @var int */
	private $imgType = SimpleForm::IMAGE_TYPE_URL;

	public function setSlug(string $slug): self {
		$this->slug = $slug;
		return $this;
	}

	public function setContent(callable $content): self {
		$this->content = $content;
		return $this;
	}

	public function setCallable(callable $callable): self {
		$this->callable = $callable;
		return $this;
	}

	public function setPermissions(array $permissions): self {
		$this->permissions = $permissions;
		return $this;
	}

	public function setImgPath(string $path): self {
		$this->imgPath = $path;
		return $this;
	}

	public function setImgPack(string $path): self {
		return $this->setImgPath($path)->setImgType(SimpleForm::IMAGE_TYPE_PATH);
	}

	public function setImgType(int $imgType): self {
		$this->imgType = $imgType;
		return $this;
	}

	public function getSlug(): string {
		return $this->slug;
	}

	public function getContent(string $playerName): string {
		return call_user_func($this->content, $playerName);
	}

	public function getPermissions(): array {
		return $this->permissions;
	}

	public function hasAccess(string $playerName): bool {
		if (count($this->getPermissions()) == 0) {
			return true;
		}

		$user = MainAPI::getUser($playerName);
		if ($user->getRank() == Ids::OWNER_ID) {
			return true;
		}

		$permissionsPlayer = MainAPI::getMemberPermission($playerName);
		foreach ($this->getPermissions() as $permission) {
			if (!is_array($permission) && $permissionsPlayer !== null) {
				if (isset($permissionsPlayer[$permission]) && $permissionsPlayer[$permission]) {
					return true;
				}
			} elseif (is_array($permission) && $permission[0] === Utils::POCKETMINE_PERMISSIONS_CONSTANT) {
				return Main::getInstance()->getServer()->getPlayerExact($playerName)->hasPermission($permission[1]);
			}
		}
		return false;
	}

	public function getCallable(): callable {
		return $this->callable;
	}

	public function call(Player $player) {
		return call_user_func($this->getCallable(), $player);
	}

	public function getImgPath(): string {
		return $this->imgPath;
	}

	public function getImgType(): int {
		return $this->imgType;
	}
}