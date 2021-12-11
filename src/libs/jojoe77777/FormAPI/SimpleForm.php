<?php

declare(strict_types = 1);

namespace ShockedPlot7560\FactionMaster\libs\jojoe77777\FormAPI;

use function count;

class SimpleForm extends Form {
	const IMAGE_TYPE_PATH = 0;
	const IMAGE_TYPE_URL = 1;

	/** @var string */
	private $content = "";

	private $labelMap = [];

	public function __construct(?callable $callable) {
		parent::__construct($callable);
		$this->data["type"] = "form";
		$this->data["title"] = "";
		$this->data["content"] = $this->content;
		$this->data["buttons"] = [];
	}

	public function processData(&$data) : void {
		$data = $this->labelMap[$data] ?? null;
	}

	public function setTitle(string $title) : void {
		$this->data["title"] = $title;
	}

	public function getTitle() : string {
		return $this->data["title"];
	}

	public function getContent() : string {
		return $this->data["content"];
	}

	public function setContent(string $content) : void {
		$this->data["content"] = $content;
	}

	/**
	 * @param string $label
	 */
	public function addButton(string $text, int $imageType = -1, string $imagePath = "", ?string $label = null) : void {
		$content = ["text" => $text];
		if ($imageType !== -1) {
			$content["image"]["type"] = $imageType === 0 ? "path" : "url";
			$content["image"]["data"] = $imagePath;
		}
		$this->data["buttons"][] = $content;
		$this->labelMap[] = $label ?? count($this->labelMap);
	}
}
