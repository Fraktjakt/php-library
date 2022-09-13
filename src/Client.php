<?php namespace Fraktjakt;

/**
*  Fraktjakt Client
*  @author T. Almroth
*/

class Client {

  const VERSION  = '1.0.0';
  const SERVER_TEST  = 'https://testapi.fraktjakt.se';
  const SERVER_PRODUCTION  = 'https://api.fraktjakt.se';

  private $_consignorId;
  private $_consignorKey;
  private $_testMode;
  private $_timeout = 25;
  private $_lastRequest;
  private $_lastResponse;
  private $_lastLog;

  public function setConsignorId(string $id) {
    $this->_consignorId = $id;
    return $this;
  }

  public function setConsignorKey(string $secret) {
    $this->_consignorKey = $secret;
    return $this;
  }

  public function setTestMode(bool $state) {
    $this->_testMode = $state;
    return $this;
  }

  public function setTimeout(int $seconds) {
    $this->_timeout = $seconds;
    return $this;
  }

  public function getLastLog() {

    if (empty($this->_lastRequest) && empty($this->_lastResponse)) return false;

    $log = (
      '##'. str_pad(' Request Parameters ', 80, '#', STR_PAD_RIGHT) . "\r\n\r\n" .
      ((!empty($this->_lastRequest['parameters'])) ? $this->_lastRequest['parameters'] : "n/a\r\n") . "\r\n" .

      '##'. str_pad(' Response Parameters ', 80, '#', STR_PAD_RIGHT) . "\r\n\r\n" .
      ((!empty($this->_lastResponse['parameters'])) ? $this->_lastResponse['parameters'] : "n/a\r\n") . "\r\n"
    );

    if (!empty($this->_lastRequest['head'])) {
      $log .= (
        '##'. str_pad(' ['. date('Y-m-d H:i:s', $this->_lastRequest['timestamp']) .'] Raw HTTP Request ', 80, '#', STR_PAD_RIGHT) . "\r\n\r\n" .
        $this->_lastRequest['head'] .
        $this->_lastRequest['body'] . "\r\n\r\n"
      );
    }

    if (!empty($this->_lastResponse['head'])) {
      $log .= (
        '##'. str_pad(' ['. date('Y-m-d H:i:s', $this->_lastResponse['timestamp']) .'] Raw HTTP Response — '. number_format($this->_lastResponse['bytes'], 0, '.', ',') .' bytes transferred in '. number_format($this->_lastResponse['duration']) .' ms ', 80, '#', STR_PAD_RIGHT) . "\r\n\r\n" .
        $this->_lastResponse['head'] .
        $this->_lastResponse['body'] . "\r\n\r\n"
      );
    }

    return $log;
  }

  public function Order(array $request, string $encoding = 'UTF-8') {

    $this->_lastRequest = [];
    $this->_lastResponse = [];

    if (isset($request['shipping_product_id']) && $request['shipping_product_id'] == '0') {
      throw new \Exception('You cannot place orders for custom shipping products (shipping_product_id: 0)');
    }

    $request['consignor']['id'] = $this->_consignorId;
    $request['consignor']['key'] = $this->_consignorKey;

    if (empty($request['consignor']['api_version'])) {
      $request['consignor']['api_version'] = '3.9';
    }

    // Rewrite commodities depth for XML because arrays do not have duplicate keys
    if (!empty($request['commodities'])) {
      $commodities = [];
      foreach ($request['commodities'] as $commodity) {
        $commodities[] = [
          'commodity' => $commodity,
        ];
      }
      $request['commodities'] = $commodities;
    }

    // Rewrite parcels depth for XML because arrays do not have duplicate keys
    if (!empty($request['parcels'])) {
      $parcels = [];
      foreach ($request['parcels'] as $parcel) {
        $parcels[] = [
          'parcel' => $parcel,
        ];
      }
      $request['parcels'] = $parcels;
    }

    $request = $this->_arrayToXml($request, 'OrderSpecification', $encoding);

    if ($this->_testMode) {
      $url = self::SERVER_TEST.'/orders/order_xml';
    } else {
      $url = self::SERVER_PRODUCTION.'/orders/order_xml';
    }

    $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];

    $request = http_build_query([
      'xml' => $request,
      'md5_checksum' => md5($request)
    ], '', '&');

    $result = $this->_call('POST', $url, $request, $headers);

    if (empty($result['shipment_id'])) {
      throw new \Exception('Missing shipment ID in result');
    }

    if (empty($result['order_id'])) {
      throw new \Exception('Missing order ID in result');
    }

    return $result;
  }

  public function Query(array $request, string $encoding = 'UTF-8') {

    $this->_lastRequest = [];
    $this->_lastResponse = [];

    if (empty($request['address_to']['street_address_1'])) {
      throw new \Exception('You must provide an address for delivery');
    }

    if (empty($request['address_to']['postal_code'])) {
      throw new \Exception('You must provide a postal code for delivery');
    }

    if (empty($request['address_to']['country_code'])) {
      throw new \Exception('You must provide a country for delivery');
    }

    $request['consignor']['id'] = $this->_consignorId;
    $request['consignor']['key'] = $this->_consignorKey;

    if (empty($request['consignor']['api_version'])) {
      $request['consignor']['api_version'] = '3.9';
    }

    // Rewrite commodities depth for XML because arrays do not have duplicate keys
    if (!empty($request['commodities'])) {
      $commodities = [];
      foreach ($request['commodities'] as $commodity) {
        $commodities[] = [
          'commodity' => $commodity,
        ];
      }
      $request['commodities'] = $commodities;
    }

    // Rewrite parcels depth for XML because arrays do not have duplicate keys
    if (!empty($request['parcels'])) {
      $parcels = [];
      foreach ($request['parcels'] as $parcel) {
        $parcels[] = [
          'parcel' => $parcel,
        ];
      }
      $request['parcels'] = $parcels;
    }

    $request = $this->_arrayToXml($request, 'shipment', $encoding);

    if ($this->_testMode) {
      $url = self::SERVER_TEST.'/fraktjakt/query_xml';
    } else {
      $url = self::SERVER_PRODUCTION.'/fraktjakt/query_xml';
    }

    $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];

    $request = http_build_query([
      'xml' => $request,
      'md5_checksum' => md5($request),
    ], '', '&');

    $result = $this->_call('POST', $url, $request, $headers);

    // Rewrite shipping_products depth for Array because arrays cannot have duplicate keys
    if (isset($result['shipping_products'])) {
      $shipping_products = [];
      if (!empty($result['shipping_products']['shipping_product'])) {
        if (array_keys($result['shipping_products']['shipping_product'])[0] == '0') {
          foreach ($result['shipping_products']['shipping_product'] as $shipping_product) {
            $shipping_products[] = $shipping_product;
          }
        } else {
          $shipping_products[] = $result['shipping_products']['shipping_product'];
        }
      }
      $result['shipping_products'] = $shipping_products;
    }

    if (empty($result['shipping_products'])) {
      throw new \Exception('No shipping products found');
    }

    return $result;
  }

  public function Requery(array $request, string $encoding = 'UTF-8') {

    $this->_lastRequest = [];
    $this->_lastResponse = [];

    $request['consignor']['id'] = $this->_consignorId;
    $request['consignor']['key'] = $this->_consignorKey;

    if (empty($request['consignor']['api_version'])) {
      $request['consignor']['api_version'] = '3.9';
    }

    $request = $this->_arrayToXml($request, 'shipment', $encoding);

    if ($this->_testMode) {
      $url = self::SERVER_TEST.'/fraktjakt/requery_xml';
    } else {
      $url = self::SERVER_PRODUCTION.'/fraktjakt/requery_xml';
    }

    $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];

    $request = http_build_query([
      'xml' => $request,
      'md5_checksum' => md5($request)
    ], '', '&');

    $result = $this->_call('POST', $url, $request, $headers);

    // Rewrite shipping_products depth for Array because arrays cannot have duplicate keys
    if (isset($result['shipping_products'])) {
      $shipping_products = [];
      if (!empty($result['shipping_products']['shipping_product'])) {
        if (array_keys($result['shipping_products']['shipping_product'])[0] == '0') {
          foreach ($result['shipping_products']['shipping_product'] as $shipping_product) {
            $shipping_products[] = $shipping_product;
          }
        } else {
          $shipping_products[] = $result['shipping_products']['shipping_product'];
        }
      }
      $result['shipping_products'] = $shipping_products;
    }

    if (empty($result['shipping_products'])) {
      throw new \Exception('No shipping products found');
    }

    return $result;
  }

  public function Shipment(array $request, string $encoding = 'UTF-8') {

    $this->_lastRequest = [];
    $this->_lastResponse = [];

    $request['consignor']['id'] = $this->_consignorId;
    $request['consignor']['key'] = $this->_consignorKey;

    if (empty($request['consignor']['api_version'])) {
      $request['consignor']['api_version'] = '3.9';
    }

    // Rewrite commodities depth for XML because arrays do not have duplicate keys
    if (!empty($request['commodities'])) {
      $commodities = [];
      foreach ($request['commodities'] as $commodity) {
        $commodities[] = [
          'commodity' => $commodity,
        ];
      }
      $request['commodities'] = $commodities;
    }

    // Rewrite parcels depth for XML because arrays do not have duplicate keys
    if (!empty($request['parcels'])) {
      $parcels = [];
      foreach ($request['parcels'] as $parcel) {
        $parcels[] = [
          'parcel' => $parcel,
        ];
      }
      $request['parcels'] = $parcels;
    }

    $request = $this->_arrayToXml($request, 'CreateShipment', $encoding);

    if ($this->_testMode) {
      $url = self::SERVER_TEST.'/shipments/shipment_xml';
    } else {
      $url = self::SERVER_PRODUCTION.'/shipments/shipment_xml';
    }

    $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];

    $request = http_build_query([
      'xml' => $request,
      'md5_checksum' => md5($request)
    ], '', '&');

    $result = $this->_call('POST', $url, $request, $headers);

    if (empty($result['shipment_id'])) {
      throw new \Exception('Missing shipment ID in result');
    }

    return $result;
  }

  public function Trace(array $request, string $encoding = 'UTF-8') {

    $this->_lastRequest = [];
    $this->_lastResponse = [];

    $request['consignor_id'] = $this->_consignorId;
    $request['consignor_key'] = $this->_consignorKey;

    if ($this->_testMode) {
      $url = self::SERVER_TEST.'/trace/xml_trace';
    } else {
      $url = self::SERVER_PRODUCTION.'/trace/xml_trace';
    }

    $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];

    $request = http_build_query($request, '', '&');

    $result = $this->_call('POST', $url, $request, $headers);

    // Rewrite shipping_states depth for Array because arrays cannot have duplicate keys
    if (isset($result['shipping_states'])) {
      $shipping_states = [];
      if (!empty($result['shipping_states']['shipping_state'])) {
        // Future proof for case of multiple statuses (sequentially indexed)
        if (array_keys($result['shipping_states']['shipping_state'])[0] == '0') {
          foreach ($result['shipping_states']['shipping_state'] as $shipping_state) {
            $shipping_states[] = $shipping_state;
          }
        } else {
          $shipping_states[] = $result['shipping_states']['shipping_state'];
        }
      }
      $result['shipping_states'] = $shipping_states;
    }

    return $result;
  }

  public function ConvertLength($value, $from, $to) {

    $units = [
      'cm' => 1,
      'mm' => 10,
      'in' => 0.3937,
    ];

    if ($from == $to) return $value;

    if (!isset($units[$from])) throw new \Exception("Cannot convert from length unit $from");
    if (!isset($units[$to])) throw new \Exception("Cannot convert from length unit $to");


    return $value * ($units[$to] / $units[$from]);
  }

  public function ConvertWeight($value, $from, $to) {

    $units = [
      'kg' => 1,
      'g' => 1000,
      'lb' => 2.2046,
      'oz' => 35.274,
    ];

    if ($from == $to) return $value;

    if (!isset($units[$from])) throw new \Exception("Cannot convert from weight unit $from");
    if (!isset($units[$to])) throw new \Exception("Cannot convert from weight unit $to");

    return $value * ($units[$to] / $units[$from]);
  }

  private function AutoUpdate() {
    $contents = $this->_call('GET', 'https://raw.githubusercontent.com/Fraktjakt/php-library/master/src/Client.php');
    if ($this->_lastResponse['status_code'] == 200) {
      file_put_contents(__FILE__, $contents);
    }
  }

  private function _call(string $method, string $url, string $data = null, $headers = []) {

    $headers['User-Agent'] = 'Fraktjakt-Client-PHP/'.self::VERSION;

    if (!empty($data) && empty($headers['Content-Length'])) {
      $headers['Content-Length'] = strlen($data);
    }

    if (empty($headers['Connection'])) {
      $headers['Connection'] = 'Close';
    }

    $parts = parse_url($url);

    if (empty($parts['port'])) {
      $parts['port'] = (!empty($parts['scheme']) && $parts['scheme'] == 'https') ? 443 : 80;
    }

    switch(@$parts['scheme']) {
      case 'https': $parts['scheme'] = 'ssl'; break;
      default: $parts['scheme'] = 'tcp'; break;
    }

    $requestHeaders = $method ." ". $parts['path'] . ((isset($parts['query'])) ? '?' . $parts['query'] : '') ." HTTP/1.1\r\n"
                    . "Host: ". $parts['host'] ."\r\n";

    foreach ($headers as $key => $value) {
      $requestHeaders .= "$key: $value\r\n";
    }

    $this->_lastRequest = [
      'timestamp' => time(),
      'head' => $requestHeaders . "\r\n",
      'body' => $data,
      'parameters' => null,
    ];

    if (strtoupper($method) == 'POST' && preg_match('#application/x-www-form-urlencoded#i', $headers['Content-Type'])) {

      parse_str($data, $requestParameters);

      if (isset($requestParameters['xml'])) {
        $this->_lastRequest['parameters'] = preg_replace('#(\R+)#', "\r\n", urldecode($requestParameters['xml']));
      } else {
        $this->_lastRequest['parameters'] = $requestParameters;
      }

    } else if (strtoupper($method) == 'POST' && preg_match('#(application|text)/xml#i', $headers['Content-Type'])) {

      $this->_lastRequest['parameters'] = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    } else if (strtoupper($method) == 'POST' && preg_match('#(application|text)/json#i', $headers['Content-Type'])) {

      $this->_lastRequest['parameters'] = $data;

    } else if (strtoupper($method) == 'GET') {

      parse_str(parse_url($url, PHP_URL_QUERY), $requestParameters);

      $this->_lastRequest['parameters'] = json_encode($requestParameters, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    $microtimeStart = microtime(true);

    if (!$socket = stream_socket_client(strtr('scheme://host:port', $parts), $errno, $errstr, $this->_timeout)) {
      throw new \Exception('Error calling URL ('. $url .'): '. $errstr);
    }

    stream_set_timeout($socket, $this->_timeout);

    fwrite($socket, $requestHeaders . "\r\n" . $data);

    $response = '';
    while (!feof($socket)) {

      if ((microtime(true) - $microtimeStart) > $this->_timeout) {
        throw new \Exception('Timeout during retrieval');
        return false;
      }

      $response .= fgets($socket);
    }

    fclose($socket);

    $responseHeaders = substr($response, 0, strpos($response, "\r\n\r\n") + 4);
    $responseBody = substr($response, strpos($response, "\r\n\r\n") + 4);

    preg_match('#HTTP/\d(\.\d)?\s(\d{3})#', $responseHeaders, $matches);
    $statusCode = $matches[2];

    $this->_lastResponse = [
      'timestamp' => time(),
      'statusCode' => $statusCode,
      'head' => $responseHeaders,
      'duration' => round((microtime(true) - $microtimeStart)*1000),
      'bytes' => strlen($responseHeaders . $responseBody),
      'body' => $responseBody,
      'parameters' => null,
    ];

    if (!$result = rtrim($responseBody)) {
      throw new \Exception('No response from remote machine');
    }

    // Extract response parameters
    if (preg_match('#Content-Type: (application|text)/xml#i', $responseHeaders)){

      // Pretty printed response
      $dom = new \DOMDocument();
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $dom->loadXML($responseBody);
      $this->_lastResponse['parameters'] = $dom->saveXML();

      // Parse XML result
      if (!$xml = @simplexml_load_string($responseBody, 'SimpleXMLElement', LIBXML_NOCDATA)) {
        throw new \Exception('Invalid response from remote machine');
      }

      if (!empty($xml->error_message)) {
        throw new \Exception($xml->error_message);
      }

      if (!isset($xml->code) || (string)$xml->code == '2') {
        throw new \Exception('Error Code 2');
      }

      // Convert to array
      if (!$result = $this->_xmlToArray($xml)) {
        throw new \Exception('Could not convert result to an array' . PHP_EOL . print_r($xml, true));
      }

    } else if (preg_match('#Content-Type: (application|text)/json#i', $responseHeaders)){

      $json = json_decode($responseBody);
      $this->_lastResponse['parameters'] = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    } else {
      if (preg_match('#Content-Type: ([^\r\n]+)#i', $responseHeaders, $matches)) {
        throw new \Exception('Unexpected response content type ('. $matches[1] .')');
      } else {
        throw new \Exception('Unknown response content type');
      }
    }

    return $result;
  }

  private function _xmlToArray($xml) {
    return json_decode(json_encode($xml), true);
  }

  private function _arrayToXml(array $array, string $rootElement, string $encoding) {

    $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="'. $encoding .'"?><'.$rootElement.'/>');

    $convert = function(array $array, &$xml) use (&$convert, $encoding) {

      foreach ($array as $key => $value) {

        if (is_array($value)) {

          if (is_numeric($key)) {
            $convert($value, $xml);
          } else {
            $subnode = $xml->addChild($key);
            $convert($value, $subnode);
          }

        } else {
          if (!empty($value)) {
            $value = html_entity_decode($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, $encoding);
            $value = str_replace('&', '&amp;', $value); // Fix PHP issue #36795
          }
          $xml->addChild($key, $value);
        }
      }
    };

    $convert($array, $xml);

    libxml_use_internal_errors(true);

    $dom = new \DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());

    if ($errors = libxml_get_errors()) {
      foreach ($errors as $error) {
        libxml_clear_errors();
        throw new \Exception('Error while encoding XML:'. $error->message);
      }
    }

    return $dom->saveXML();
  }
}
