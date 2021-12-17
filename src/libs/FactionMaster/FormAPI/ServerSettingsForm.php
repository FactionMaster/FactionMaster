<?php

declare(strict_types = 1);

namespace ShockedPlot7560\FactionMaster\libs\FactionMaster\FormAPI;

class ServerSettingsForm extends CustomForm{

    public function setIcon(int $imageType = -1, string $imagePath = ""){
        if($imageType !== -1) {
            $content["icon"]["type"] = $imageType === 0 ? "path" : "url";
            $content["icon"]["data"] = $imagePath;
        }
    }

    public function hasIcon(): bool{
        return is_array($this->data['icon']);
    }

}