<?php

declare(strict_types = 1);

namespace ShockedPlot7560\FactionMaster\libs\jojoe77777\FormAPI;

use pocketmine\plugin\PluginBase;

class FormAPI extends PluginBase {

	/**
	 * @deprecated
	 */
	public function createCustomForm(?callable $function = null) : CustomForm {
		return new CustomForm($function);
	}

	/**
	 * @deprecated
	 */
	public function createSimpleForm(?callable $function = null) : SimpleForm {
		return new SimpleForm($function);
	}

	/**
	 * @deprecated
	 */
	public function createModalForm(?callable $function = null) : ModalForm {
		return new ModalForm($function);
	}
}
