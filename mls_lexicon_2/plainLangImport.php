<?php

session_start();

//print_r($_SESSION["giannakis"]);

$petros = $_SESSION["giannakis"];
    
echo $petros["wep"]["All"];
