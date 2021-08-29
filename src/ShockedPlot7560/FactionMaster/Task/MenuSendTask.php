<?php

namespace ShockedPlot7560\FactionMaster\Task;

use pocketmine\scheduler\Task;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MenuSendTask extends Task {

    private $condition;
    private $onSuccess;
    private $onTimeOut;
    private $timeOut;
    private $tick = 0;

    public function __construct(callable $condition, callable $onSuccess, callable $onTimeOut) {
        $this->condition = $condition;
        $this->onSuccess = $onSuccess;
        $this->onTimeOut = $onTimeOut;
        $this->timeOut = (int) Utils::getConfig("timeout-task");
    }

    public function onRun(int $currentTick) {
        $result = call_user_func($this->condition);
        if ($result === true && $this->tick < $this->timeOut) {
            call_user_func($this->onSuccess);
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }elseif ($this->tick >= $this->timeOut) {
            call_user_func($this->onTimeOut);
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
        $this->tick++;
    }
}