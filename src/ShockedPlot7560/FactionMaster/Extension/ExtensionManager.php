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

namespace ShockedPlot7560\FactionMaster\Extension;

use pocketmine\utils\Config;
use ShockedPlot7560\FactionMaster\Main;

class ExtensionManager {

    /** @var Extension[] */
    public $extensions = [];

    public function registerExtension(Extension $extension) {
        $this->extensions[$extension->getExtensionName()] = $extension;
    }

    public function disableExtension(string $name) {
        if (isset($this->extensions[$name])) unset($this->extensions[$name]);
    }

    public function load() {
        $Logger = Main::$logger;
        if (count($this->extensions)> 0) $Logger->info("§7Loading FactionMaster extension started");
        foreach ($this->extensions as $extension) {
            $Logger->info("§fLoading §7" . $extension->getExtensionName());
            $extension->execute();
            foreach ($extension->getLangConfig() as $LangSLug => $LangConfig) {
                if (!$LangConfig instanceof Config) {
                    $Logger->warning("Loading the translate files of : ".$extension->getExtensionName().", check the return value of the function getLangConfig() and verify its key and value");
                }else{
                    $LangMainFile = new Config(Main::getInstance()->getDataFolder() . "Translation/$LangSLug.yml", Config::YAML);
                    foreach ($LangConfig->getAll() as $key => $value) {
                        $LangMainFile->__set($key, $value);
                    }
                    $LangMainFile->save();
                }
            }
            $Logger->info("§7" . $extension->getExtensionName() . " §floading finish");
        }
        if (count($this->extensions)> 0) $Logger->info("§7Loading FactionMaster extension finish");
    }
}