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

namespace ShockedPlot7560\FactionMaster\Utils;

use Exception;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\MenuOpenEvent;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Manager\PermissionManager;
use ShockedPlot7560\FactionMaster\Manager\TranslationManager;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use function compact;
use function is_array;
use function is_string;
use function join;
use function pow;
use function str_replace;

class Utils {
	const POCKETMINE_PERMISSIONS_CONSTANT = "Pocketmine";

	public static function generateButton(SimpleForm $form, array $buttons): SimpleForm {
		foreach ($buttons as $button) {
			$form->addButton($button);
		}
		return $form;
	}

	public static function processMenu(Route $route, Player|CommandSender $player, ?array $params = null): void {
		if ($player instanceof CommandSender && !$player instanceof Player) {
			@throw new Exception("player given must be of type Player", 1);
		}
		$userEntity = MainAPI::getUser($player->getName());
		$userPermissions = MainAPI::getMemberPermission($player->getName());
		if ($userPermissions === null) {
			$userPermissions = [];
		}
		if ($userEntity instanceof UserEntity) {
			$good = $route->getPermissions() === [];
			if ($userEntity->getRank() === Ids::OWNER_ID) {
				$good = true;
			}
			foreach ($route->getPermissions() as $permission) {
				if (is_string($permission)) {
					if (self::haveAccess($userPermissions, $userEntity, $permission)) {
						$good = true;
					}
				} elseif (is_array($permission) && $permission[0] === self::POCKETMINE_PERMISSIONS_CONSTANT) {
					if ($player->hasPermission($permission[1])) {
						$good = true;
					}
				}
			}
			if ($good) {
				$ev = new MenuOpenEvent($player, $route);
				$ev->call();
				if ($ev->isCancelled()) {
					return;
				}

				$route($player, $userEntity, $userPermissions, $params);
				return;
			} else {
				if ($route->getBackRoute() instanceof Route) {
					$route = $route->getBackRoute();
					$route($player, $userEntity, $userPermissions, $params);
				}
			}
		}
	}

	public static function replaceParams(string $string, array $data): string {
		foreach ($data as $key => $value) {
			$string = str_replace("{{" . $key . "}}", $value, $string);
		}
		return $string;
	}

	/**
	 * @return string The formated string like x|z|world
	 */
	public static function claimToString($x, $z, $world): string {
		return join("|", [$x, $z, $world]);
	}

	/**
	 * @return string The formated string like x|y|z|world
	 */
	public static function homeToString($x, $y, $z, $world): string {
		return join("|", [$x, $y, $z, $world]);
	}

	public static function homeToArray($x, $y, $z, $world): array {
		return compact([$x, $y, $z, $world]);
	}

	/**
	 * @return bool|mixed
	 */
	public static function getConfig(string $key) {
		return self::getConfigFile()->get($key);
	}

	public static function getConfigFile(string $fileName = "config", string $folderPath = null): Config {
		if ($folderPath === null) {
			$folderPath = self::getDataFolder();
		}
		return new Config($folderPath . "$fileName.yml", Config::YAML);
	}

	/**
	 * @return bool|mixed
	 */
	public static function getConfigLang(string $key) {
		return self::getConfigFile("translation")->get($key);
	}

	public static function getText(string $playerName, string $slug, array $args = []): string {
		$playerLang = MainAPI::getPlayerLang($playerName);
		$textNoReplace = TranslationManager::getTranslation($slug, $playerLang);
		return self::replaceParams($textNoReplace, $args);
	}

	public static function getConfigLangFile(string $fileName): Config {
		return self::getConfigFile($fileName, self::getLangFile());
	}

	public static function getXpLevel(int $level): int {
		return 1000 * pow(1.09, $level);
	}

	public static function haveAccess(array $permission, UserEntity $userEntity, int $id): bool {
		if ($userEntity->getRank() == Ids::OWNER_ID) {
			return true;
		}

		if (!PermissionManager::isRegister($id)) {
			return false;
		}

		return (isset($permission[$id]) && $permission[$id]);
	}

	public static function newMenuSendTask(MenuSendTask $task): TaskHandler {
		return Main::getInstance()->getScheduler()->scheduleRepeatingTask($task, 1);
	}

	public static function getDataFolder(): string {
		return Main::getInstance()->getDataFolder();
	}

	public static function getLangFile(): string {
		return self::getDataFolder() . "lang/";
	}

	public static function getRawCoordonate(Position $position): string {
		return join("|", [
			$position->getX(),
			$position->getY(),
			$position->getZ(),
			$position->getWorld()->getDisplayName()
		]);
	}
}