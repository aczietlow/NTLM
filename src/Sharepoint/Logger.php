<?php
namespace Piper\Sharepoint;

use Piper\Sharepoint\WatchdogLoggerTrait;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface {
  use WatchdogLoggerTrait;

  /**
   * The category to which this message belongs.
   * @var string
   */
  public $type;

  /**
   * Map of PSR3 log constants to RFC 5424 log constants.
   *
   * @var array
   */
  protected $levelTranslation = array(
    LogLevel::EMERGENCY => WATCHDOG_EMERGENCY,
    LogLevel::ALERT => WATCHDOG_ALERT,
    LogLevel::CRITICAL => WATCHDOG_CRITICAL,
    LogLevel::ERROR => WATCHDOG_ERROR,
    LogLevel::WARNING => WATCHDOG_WARNING,
    LogLevel::NOTICE => WATCHDOG_NOTICE,
    LogLevel::INFO => WATCHDOG_INFO,
    LogLevel::DEBUG => WATCHDOG_DEBUG,
  );

  /**
   * Logger constructor.
   *
   * @param string $type
   *   The category to which this message belongs. Can be any string, but the
   *   general practice is to use the name of the module calling watchdog().
   */
  public function __construct($type) {
    $this->type = $type;
  }


  /**
   * {@inheritdoc}
   *
   * Matching up to watchdog(
   *   $type,
   *   $message,
   *   $variables = [],
   *   $severity = WATCHDOG_NOTICE,
   *   $link = NULL
   * );
   */
  public function log($level, $message, array $context = []) {
    if (is_string($level)) {
      // Convert to integer equivalent for consistency with RFC 5424.
      $level = $this->levelTranslation[$level];
    }

    // Illustrate difference in vocabulary in PSR 3 and Drupal 7.
    $severity = $level;
    watchdog($this->type, '<pre>' . $message . '</pre>', [], $severity, []);

  }
}
