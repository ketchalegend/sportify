<?php

define('VIEW_DIR', $_SERVER['DOCUMENT_ROOT'] . '/../views/');

define('POINTS_OUTCOME', 1);
define('POINTS_EXACT', 3);

// set the server timezone to EET (GMT +2)
date_default_timezone_set('EET');

require '../vendor/autoload.php';
require '../config/base.php';
require '../application/Database.class.php';
require '../application/Router.class.php';
require '../application/AbstractController.class.php';
require '../application/RegisterController.class.php';
require '../application/LoginController.class.php';
require '../application/LogoutController.class.php';
require '../application/StandingsController.class.php';
require '../application/MatchesController.class.php';
require '../application/TournamentsController.class.php';
require '../application/HistoryController.class.php';
require '../application/View.class.php';
require '../application/User.class.php';
require '../application/Token.class.php';
require '../application/UserAuth.class.php';

/**
 * Initialize Dotenv for dynamically getting environment variables
 */
$dotenv = new Dotenv\Dotenv(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config');
$dotenv->load();
