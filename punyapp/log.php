<?php
/**
 * PunyApp:
 *   The puny developer framework for rapid compiling.
 *
 * PHP version 5
 *
 * @package    PunyApp
 * @subpackage -
 * @category   Log
 * @author     polygon planet <polygon.planet.aqua@gmail.com>
 * @link       https://github.com/polygonplanet/PunyApp
 * @license    MIT
 * @copyright  Copyright (c) 2014 polygon planet
 */

/**
 * @name PunyApp_Log
 */
class PunyApp_Log {

  /**
   * @const file extension
   */
  const EXT = '.log';

  /**
   * @var int max item counts
   */
  public $maxCount = null;

  /**
   * @var bool multiline
   */
  public $multiline = false;

  /**
   * @var PunyApp_File
   */
  private $_file = null;

  /**
   * Constructor
   *
   * @param string $name log name
   * @param int $max_count max item counts
   * @param bool $multiline multiline
   */
  public function __construct($name, $max_count = null, $multiline = false) {
    $filename = PUNYAPP_LOGS_DIR . DIRECTORY_SEPARATOR . $name . self::EXT;
    $this->_file = new PunyApp_File($filename, true);
    $this->maxCount = $max_count;
    $this->multiline = $multiline;
  }

  /**
   * Write log
   *
   * @param mixed $message
   * @param bool $pretty_print
   * @return bool
   */
  public function write($message, $pretty_print = false) {
    if (!is_array($message)) {
      $message = array(array(
        'message' => $message,
        'time' => PunyApp::now()
      ));
    }

    $messages = array();
    foreach ($message as $val) {
      if (!isset($val['message'])) {
        continue;
      }

      if (!isset($val['time'])) {
        $val['time'] = PunyApp::now();
      }

      $msg = $val['message'];
      $time = $val['time'];

      if (is_object($msg) && ($msg instanceof Exception)) {
        $msg = sprintf('%s in %s on line %d',
          $msg->getMessage(),
          $msg->getFile(),
          $msg->getLine()
        );
      } else {
        if ($pretty_print) {
          $msg = print_r($msg, true);
        }
      }
      $messages[] = $this->_format($msg, $time);
    }

    if ($this->maxCount === null) {
      $result = $this->_file->append($msg);
      $this->_file->close();
      return $result;
    }

    $data = $this->_file->read();
    if (!$data) {
      $data = '';
    }
    $data .= implode('', $messages);

    $pattern = '(?:\r\n|\n|\r)';
    if ($this->multiline) {
      $pattern = '----- \[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3}\] -----' .
        $pattern;
    }

    $data = preg_split('/(' . $pattern . ')/',
      $data,
      -1,
      PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
    );

    $max = $this->maxCount * 2;
    while (count($data) > $max) {
      array_shift($data);
    }

    $result = $this->_file->write(implode('', $data));
    $this->_file->close();
    return $result;
  }


  /**
   * Format message
   *
   * @param string $message
   * @param float $time
   * @return string
   */
  private function _format($message, $time = null) {
    if ($time === null) {
      $time = PunyApp::now();
    }

    $date = sprintf('[%s.%s]',
      date('Y-m-d H:i:s', floor($time / 1000)), substr($time, -3)
    );

    if ($this->multiline) {
      $date = '----- ' . $date . ' -----' . PHP_EOL;
    } else {
      $date .= ' ';
      $message = PunyApp_Util::removeLineBreaks($message);
    }

    $result = $date . $message . PHP_EOL;
    return PunyApp_Util::removeLineBreaks($result, PHP_EOL);
  }
}
