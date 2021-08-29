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

namespace ShockedPlot7560\FactionMaster\Task;

use pocketmine\plugin\PluginLogger;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Config;

class InitTranslationFile extends AsyncTask {

    private $extensions;
    private $mainFolder;
    private $Logger;

    public function __construct(array $extensions, string $mainFolder, PluginLogger $Logger) {
        $this->extensions = $extensions;
        $this->mainFolder = $mainFolder;
        $this->Logger = $Logger;
    }

    public function onRun(): void {
        foreach ($this->extensions as $extensionName => $langConfig) {
            foreach ($langConfig as $LangSLug => $LangConfig) {
                if (!$LangConfig instanceof Config) {
                    $this->Logger->warning("Loading the translate files of : ".$extensionName.", check the return value of the function getLangConfig() and verify its key and value. If you are not the author of this extension, please inform him");
                }else{
                    $LangMainFile = new Config($this->mainFolder . "Translation/$LangSLug.yml", Config::YAML);
                    foreach ($LangConfig->getAll() as $key => $value) {
                        $LangMainFile->__set($key, $value);
                    }
                    $LangMainFile->save();
                }
            }
        }
        $this->setResult("");
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server): void {
        
    }
}