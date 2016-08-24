<?php

namespace Piper\Sharepoint;

class ResponseParser {

  /**
   * @param \GuzzleHttp\Psr7\Response $response\
   */
  public static function getFormDigestValue($response)
  {
    $jsonResponse = json_decode($response->getBody());

    if (!empty($jsonResponse)) {
      $formDigestValue = $jsonResponse->d->GetContextWebInformation->FormDigestValue;
    }

    return $formDigestValue;
  }
}
