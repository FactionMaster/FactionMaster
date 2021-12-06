<?php

declare(strict_types=1);

/*
 *  _   _           _       _       _   _       _   _  __ _
 * | | | |         | |     | |     | \ | |     | | (_)/ _(_)
 * | | | |_ __   __| | __ _| |_ ___|  \| | ___ | |_ _| |_ _  ___ _ __
 * | | | | '_ \ / _` |/ _` | __/ _ \ . ` |/ _ \| __| |  _| |/ _ \ '__|
 * | |_| | |_) | (_| | (_| | ||  __/ |\  | (_) | |_| | | | |  __/ |
 *  \___/| .__/ \__,_|\__,_|\__\___\_| \_/\___/ \__|_|_| |_|\___|_|
 *       | |
 *       |_|
 *
 * UpdateNotifier, a updater virion for PocketMine-MP
 * Copyright (c) 2018 JackMD  < https://github.com/JackMD >
 *
 * Discord: JackMD#3717
 * Twitter: JackMTaylor_
 *
 * This software is distributed under "GNU General Public License v3.0".
 *
 * UpdateNotifier is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License v3.0 for more details.
 *
 * You should have received a copy of the GNU General Public License v3.0
 * along with this program. If not, see
 * <https://opensource.org/licenses/GPL-3.0>.
 * ------------------------------------------------------------------------
 */

namespace ShockedPlot7560\FactionMaster\libs\JackMD\UpdateNotifier;

use pocketmine\Server;
use ShockedPlot7560\FactionMaster\libs\JackMD\UpdateNotifier\task\UpdateNotifyTask;

class UpdateNotifier {

	/**
	 * Submits an async task which then checks if a new version for the plugin is available.
	 * If an update is available then it would print a message on the console.
	 */
	public static function checkUpdate(string $pluginName, string $pluginVersion) : void {
		Server::getInstance()->getAsyncPool()->submitTask(new UpdateNotifyTask($pluginName, $pluginVersion));
	}
}