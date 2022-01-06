<?php

declare(strict_types = 1);

namespace ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI;

use pocketmine\plugin\PluginBase;

class FormsUI extends PluginBase{

    /**
     * @deprecated
     *
     * @param callable|null $function
     * @return CustomForm
     */
    public function createCustomForm(?callable $function = null) : CustomForm {
        return new CustomForm($function);
    }

    /**
     * @deprecated
     *
     * @param callable|null $function
     * @return SimpleForm
     */
    public function createSimpleForm(?callable $function = null) : SimpleForm {
        return new SimpleForm($function);
    }

    /**
     * @deprecated
     *
     * @param callable|null $function
     * @return ModalForm
     */
    public function createModalForm(?callable $function = null) : ModalForm {
        return new ModalForm($function);
    }
}
