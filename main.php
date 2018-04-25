<?php

require 'domino.php';

$domino = new Domino();
$domino->prepareGame();
$domino->playGame();

exit;