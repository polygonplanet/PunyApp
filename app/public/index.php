<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 */

require_once dirname(dirname(dirname(__FILE__))) . '/lib/bootstrap.php';

PunyApp_Dispatcher::dispatch();
