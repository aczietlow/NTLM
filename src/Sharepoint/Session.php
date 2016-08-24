<?php

namespace Piper\Sharepoint;

use GuzzleHttp\Psr7\Request;
use Piper\Sharepoint\ResponseParser;

// @TODO add caching with omni caching.
class Session {

  /**
   * The user:password string used for authentication with the curl handler.
   * @var string
   */
  private $curlUserpwd;

  /**
   * Server's Form Digest Value.
   * @var string
   */
  protected $formDigest;

  /**
   * Opens a connection to the Sharepoint API.
   *
   * @param API $api
   *   The http client.
   * @param string $user
   *   NTLM Username.
   * @param string $password
   *   User password.
   */
  public function __construct(API $api, $user, $password)
  {
    $this->api = $api;
    $this->curlUserpwd = $user . ":" . $password;

    // @TODO check if session is cached.
    $this->createSession();
  }

  /**
   * Create a session for the sharepoint API.
   */
  protected function createSession()
  {
    $options = $this->getCurlOptions();
    $headers = [
      'Content-Length' => '0',
      'Accept' => 'application/json;odata=verbose',
      'content-Type' => 'application/json;odata=verbose',
    ];
    $request = new Request('POST', 'contextinfo', $headers);

    $response = $this->api->guzzleClient->send($request, $options);

    // @TODO Is this reusable for multiple transactions.
    $this->formDigest = ResponseParser::getFormDigestValue($response);
  }

  /**
   * Gets the server form digest value.
   * 
   * @return string
   *   Server form digest value.
   * 
   * @TODO The logger could handle this.
   */
  public function getFormDigest()
  {
    return $this->formDigest;
  }

  /**
   * @return array
   *   Curl options to handle NTLM authentication.
   */
  public function getCurlOptions()
  {
    return [
      'curl' => [
        CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
        CURLOPT_USERPWD  => $this->curlUserpwd,
      ],
    ];
  }
}
