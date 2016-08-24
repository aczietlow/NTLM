<?php

namespace Piper\Sharepoint;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Psr7\Response;
use Piper\Sharepoint\Logger;
use Piper\Sharepoint\ResponseParser;
use GuzzleHttp\Middleware;
use PhpSpec\Exception\Exception;
use Piper\Sharepoint\Session;
use GuzzleHttp\Psr7\Request;

class API {
  /**
   * The Sharepoint API.
   * @var string
   *
   * @TODO Make configurable.
   */
  protected $sharepointUri = 'https://readact.obvious/_api/';

  /**
   * Drupal sharepoint list.
   */
  const ITEMTYPE = 'SP.Data.PiperDrupalListItem';

  /**
   * Guzzle Client.
   * @var \GuzzleHttp\Client
   */
  public $guzzleClient;

  /**
   * Custom session for Sharepoint API.
   * @var Session
   */
  protected $session;

  /**
   * Drupal logger for watchdog.
   * @var Logger
   */
  protected $logger;

  /**
   * Allow for debug mode for api requests.
   * @var bool
   */
  protected $debug = FALSE;

  /**
   * Windows AD username.
   * @var string
   */
  private $user;

  /**
   * Windows AD password.
   * @var string
   */
  private $password;

  /**
   * API constructor.
   *
   * @param string $user
   *   NTLM account username.
   * @param string $password
   *   NTLM account password.
   * @param \GuzzleHttp\Client|NULL $guzzle
   *   Reusable guzzle client.
   */
  public function __construct($user, $password, Client $guzzle = NULL) {
    $this->user = $user;
    $this->password = $password;

    if (is_null($guzzle)) {
      $this->guzzleClient = new Client(
        [
          'base_uri' => $this->sharepointUri,
          'handler' => $this->createLoggingHandlerStack([
            '{method} {uri} HTTP/{version} {req_body}',
            'RESPONSE: {code} - {res_body}',
          ]),
          'timeout' => 4,
          'defaults' => [
            'debug' => TRUE,
            'verify' => TRUE,
          ],
        ]
      );
    }
    // Don't overwrite a previous connection.
    else {
      $this->guzzleClient = $guzzle;
    }
  }

  /**
   * Build the sharepoint request object.
   *
   * @param string $httpMethond
   *   HTTP Methond.
   * @param string $requestUri
   *   uri.
   * @param array $options
   *   Request options to apply.
   *
   * @return Response
   *   Response object from the api request. Returns False if request failed.
   */
  protected function request($httpMethond, $requestUri, $options) {
    // Make sure there is a valid session before making requests.
    if (!$this->session) {
      $this->session = new Session($this, $this->user, $this->password);
    }

    $headers = [
      'Accept' => 'application/json;odata=verbose',
      'content-Type' => 'application/json;odata=verbose',
      'X-RequestDigest' => $this->session->getFormDigest(),
    ];

    $request = new Request($httpMethond, $requestUri, $headers);

    return $this->send($request, $options);
  }

  /**
   * Create a new sharepoint list item.
   *
   * @param array $jsonData
   *   Entity data to be posted to sharepoint.
   *
   * @return Response
   *   Response object from the api request. Returns False if request failed.
   */
  public function createItem($jsonData = [])
  {
    // @TODO Make configurable.
    $requestUri = "web/lists/items";

    $jsonData['__metadata'] = ['type' => self::ITEMTYPE];

    $options['json'] = $jsonData;

    return $this->request('POST', $requestUri, $options);
  }

  /**
   * Test the api connection.
   */
  public function ping()
  {
    // Make sure there is a valid session before making requests.
    if (!$this->session) {
      $this->session = new Session($this, $this->user, $this->password);
    }

    $headers = [
      'Content-Length' => '0',
      'Accept' => 'application/json;odata=verbose',
      'content-Type' => 'application/json;odata=verbose',
    ];

    $request = new Request('POST', 'contextinfos', $headers);

    return $this->send($request);
  }

  /**
   * Send the http request to the Sharepoint.
   *
   * @param Request $request
   *   Request object.
   *
   * @param array $options
   *   Request options.
   *
   * @return Response
   *   Response object from the api request. Returns False if request failed.
   */
  protected function send($request, $options = []) {
    // Add curl options to the payload.
    $options = array_merge($options, $this->session->getCurlOptions());

    // Add debug options to the payload.
    if ($this->debug) {
      $options = array_merge($options, ['debug' => TRUE]);
    }

    $logger = $this->getLogger();

    try {
      $response = $this->guzzleClient->send($request, $options);
    }
    catch (BadResponseException $e) {
      drupal_set_message(
        t(
          'Could not connect to the sharepoint api. See watchdog for more details.'
        ),
        'error'
      );
      $logger->log(WATCHDOG_EMERGENCY, serialize($e->getRequest()));
      $logger->log(WATCHDOG_EMERGENCY, serialize($e->getResponse()));
    }
    catch (\Exception $e) {
      drupal_set_message(
        t(
          'Could not successfully connect to the sharepoint api. Encountered unknown error.'
        ),
        'error'
      );
    }

    return $response;
  }

  /**
   * Gets the PSR 3 Drupal 7 logger.
   *
   * @return Logger
   *   Watchdog logger.
   */
  protected function getLogger()
  {
    if (!$this->logger) {
      $this->logger = new Logger('Sharepoint API');
    }

    return $this->logger;
  }

  /**
   * Creates a new middleware logger for the handler stack.
   *
   * @param string $messageFormat
   *   Log message template.
   *
   * @return callable
   *   New logger middleware.
   *
   * @ TODO add scalar type hint when on PHP 7.
   */
  protected function createGuzzleLoggingMiddleware($messageFormat)
  {
    return Middleware::log(
      $this->getLogger(),
      new MessageFormatter($messageFormat)
    );
  }

  /**
   * Adds middleware logging to the handler stack.
   *
   * @param array $messageFormats
   *   A list of message formats to be used for each added middleware.
   *
   * @return \GuzzleHttp\HandlerStack
   *   The updated stack with the additional middleware loggers.
   */
  protected function createLoggingHandlerStack(array $messageFormats)
  {
    $stack = HandlerStack::create();

    foreach ($messageFormats as $messageFormat) {
      // Use unshift vs push, to add the middleware to the bottom of the stack.
      $stack->unshift($this->createGuzzleLoggingMiddleware($messageFormat));
    }

    return $stack;
  }

  /**
   * Adds middleware for debuging.
   * 
   * @TODO Blow away, was for development purposes only.
   */
  protected function debug()
  {
    $clientHandler = $this->guzzleClient->getConfig('handler');

    // Create a middleware that echoes parts of the request.
    $tapMiddleware = Middleware::tap(function ($request) {
      echo "<pre>";

      /* @var \GuzzleHttp\Psr7\Request $request */
      $headers = $request->getHeaders();
      foreach ($headers as $header => $value) {
        echo "$header: " . end($value) . "\n";
      }
      echo "request uri: " . $request->getUri()->getPath() . "\n";

      $body = $request->getBody();
      echo($body->getContents());
      echo "</pre>";
    });
  }
}
