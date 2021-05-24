<?php

namespace ShockedPlot7560\FactionMaster\Database\Table;

use PDO;

interface TableInterface {

    public function init();

    public function __construct(PDO $PDO);
}