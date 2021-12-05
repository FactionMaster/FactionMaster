<?php

declare(strict_types=1);

namespace ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\libs\muqsit\simplepackethandler;

use InvalidArgumentException;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\libs\muqsit\simplepackethandler\interceptor\IPacketInterceptor;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\libs\muqsit\simplepackethandler\interceptor\PacketInterceptor;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\libs\muqsit\simplepackethandler\monitor\IPacketMonitor;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\libs\muqsit\simplepackethandler\monitor\PacketMonitor;
use pocketmine\event\EventPriority;
use pocketmine\plugin\Plugin;

final class SimplePacketHandler{

	public static function createInterceptor(Plugin $registerer, int $priority = EventPriority::NORMAL, bool $handleCancelled = false) : IPacketInterceptor{
		if($priority === EventPriority::MONITOR){
			throw new InvalidArgumentException("Cannot intercept packets at MONITOR priority");
		}
		return new PacketInterceptor($registerer, $priority, $handleCancelled);
	}

	public static function createMonitor(Plugin $registerer, bool $handleCancelled = false) : IPacketMonitor{
		return new PacketMonitor($registerer, $handleCancelled);
	}
}