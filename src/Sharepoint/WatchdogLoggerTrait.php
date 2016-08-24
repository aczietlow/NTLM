<?php
namespace Piper\Sharepoint;

trait WatchdogLoggerTrait {

  /**
   * System is unusable.
   *
   * @param string $message
   * @param array $context
   *
   * @return null
   */
  public function emergency($message, array $context = array()) {
    $this->log(WATCHDOG_EMERGENCY, $message, $context);
  }

  /**
   * Action must be taken immediately.
   *
   * Example: Entire website down, database unavailable, etc. This should
   * trigger the SMS alerts and wake you up.
   *
   * @param string $message
   * @param array $context
   *
   * @return null
   */
  public function alert($message, array $context = array()) {
    $this->log(WATCHDOG_ALERT, $message, $context);
  }

  /**
   * Critical conditions.
   *
   * Example: Application component unavailable, unexpected exception.
   *
   * @param string $message
   * @param array $context
   *
   * @return null
   */
  public function critical($message, array $context = array()) {
    $this->log(WATCHDOG_CRITICAL, $message, $context);
  }

  /**
   * Runtime errors that do not require immediate action but should typically
   * be logged and monitored.
   *
   * @param string $message
   * @param array $context
   *
   * @return null
   */
  public function error($message, array $context = array()) {
    $this->log(WATCHDOG_ERROR, $message, $context);
  }

  /**
   * Exceptional occurrences that are not errors.
   *
   * Example: Use of deprecated APIs, poor use of an API, undesirable things
   * that are not necessarily wrong.
   *
   * @param string $message
   * @param array $context
   *
   * @return null
   */
  public function warning($message, array $context = array()) {
    $this->log(WATCHDOG_WARNING, $message, $context);
  }

  /**
   * Normal but significant events.
   *
   * @param string $message
   * @param array $context
   *
   * @return null
   */
  public function notice($message, array $context = array()) {
    $this->log(WATCHDOG_NOTICE, $message, $context);
  }

  /**
   * Interesting events.
   *
   * Example: User logs in, SQL logs.
   *
   * @param string $message
   * @param array $context
   *
   * @return null
   */
  public function info($message, array $context = array()) {
    $this->log(WATCHDOG_INFO, $message, $context);
  }

  /**
   * Detailed debug information.
   *
   * @param string $message
   * @param array $context
   *
   * @return null
   */
  public function debug($message, array $context = array()) {
    $this->log(WATCHDOG_DEBUG, $message, $context);
  }
}