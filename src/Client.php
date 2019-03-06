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
  private $_timeout = 20;
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
    return !empty($this->_lastLog) ? $this->_lastLog : '## (Null) ##';
  }

  public function Order(array $request, string $encoding = 'UTF-8') {

    if (isset($request['shipping_product_id']) && $request['shipping_product_id'] == '0') {
      throw new \Exception('You cannot place orders for custom shipping products (shipping_product_id: 0)');
    }

    $request['consignor']['id'] = $this->_consignorId;
    $request['consignor']['key'] = $this->_consignorKey;

    if (empty($request['consignor']['api_version'])) {
      $request['consignor']['api_version'] = '3.2';
    }

  // Rewrite commodities depth for XML because arrays do not have duplicate keys
    if (!empty($request['commodities'])) {
      $commodities = array();
      foreach ($request['commodities'] as $commodity) {
        $commodities[] = array(
          'commodity' => $commodity,
        );
      }
      $request['commodities'] = $commodities;
    }

  // Rewrite parcels depth for XML because arrays do not have duplicate keys
    if (!empty($request['parcels'])) {
      $parcels = array();
      foreach ($request['parcels'] as $parcel) {
        $parcels[] = array(
          'parcel' => $parcel,
        );
      }
      $request['parcels'] = $parcels;
    }

    $request = $this->_arrayToXml($request, 'OrderSpecification', $encoding);

    if ($this->_testMode) {
      $url = self::SERVER_TEST.'/orders/order_xml';
    } else {
      $url = self::SERVER_PRODUCTION.'/orders/order_xml';
    }

    $request = http_build_query(array(
      'xml' => $request,
      'md5_checksum' => md5($request)
    ), '', '&');

    $result = $this->_call('POST', $url, $request);

    if (empty($result['shipment_id'])) {
      throw new \Exception('Missing shipment ID in result');
    }

    if (empty($result['order_id'])) {
      throw new \Exception('Missing order ID in result');
    }

    return $result;
  }

  public function Query(array $request, string $encoding = 'UTF-8') {

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
      $request['consignor']['api_version'] = '3.2';
    }

  // Rewrite parcels depth for XML because arrays do not have duplicate keys
    if (!empty($request['parcels'])) {
      $parcels = array();
      foreach ($request['parcels'] as $parcel) {
        $parcels[] = array(
          'parcel' => $parcel,
        );
      }
      $request['parcels'] = $parcels;
    }

    $request = $this->_arrayToXml($request, 'shipment', $encoding);

    if ($this->_testMode) {
      $url = self::SERVER_TEST.'/fraktjakt/query_xml';
    } else {
      $url = self::SERVER_PRODUCTION.'/fraktjakt/query_xml';
    }

    $request = http_build_query(array(
      'xml' => $request,
      'md5_checksum' => md5($request),
    ), '', '&');

    $result = $this->_call('POST', $url, $request);

  // Rewrite shipping_products depth for Array because arrays cannot have duplicate keys
    if (isset($result['shipping_products'])) {
      $shipping_products = array();
      if (!empty($result['shipping_products']['shipping_product'])) {
        if (count($result['shipping_products']) > 1) {
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

    $request['consignor']['id'] = $this->_consignorId;
    $request['consignor']['key'] = $this->_consignorKey;

    if (empty($request['consignor']['api_version'])) {
      $request['consignor']['api_version'] = '3.2';
    }

    $request = $this->_arrayToXml($request, 'shipment', $encoding);

    if ($this->_testMode) {
      $url = self::SERVER_TEST.'/fraktjakt/requery_xml';
    } else {
      $url = self::SERVER_PRODUCTION.'/fraktjakt/requery_xml';
    }

    $request = http_build_query(array(
      'xml' => $request,
      'md5_checksum' => md5($request)
    ), '', '&');

    $result = $this->_call('POST', $url, $request);

  // Rewrite shipping_products depth for Array because arrays cannot have duplicate keys
    if (isset($result['shipping_products'])) {
      $shipping_products = array();
      if (!empty($result['shipping_products']['shipping_product'])) {
        if (count($result['shipping_products']) > 1) {
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

    $request['consignor']['id'] = $this->_consignorId;
    $request['consignor']['key'] = $this->_consignorKey;

    if (empty($request['consignor']['api_version'])) {
      $request['consignor']['api_version'] = '3.2';
    }

  // Rewrite commodities depth for XML because arrays do not have duplicate keys
    if (!empty($request['commodities'])) {
      $commodities = array();
      foreach ($request['commodities'] as $commodity) {
        $commodities[] = array(
          'commodity' => $commodity,
        );
      }
      $request['commodities'] = $commodities;
    }

    $request = $this->_arrayToXml($request, 'CreateShipment', $encoding);

    if ($this->_testMode) {
      $url = self::SERVER_TEST.'/shipments/shipment_xml';
    } else {
      $url = self::SERVER_PRODUCTION.'/shipments/shipment_xml';
    }

    $request = http_build_query(array(
      'xml' => $request,
      'md5_checksum' => md5($request)
    ), '', '&');

    $result = $this->_call('POST', $url, $request);

    if (empty($result['shipment_id'])) {
      throw new \Exception('Missing shipment ID in result');
    }

    return $result;
  }

  public function Trace(array $request, string $encoding = 'UTF-8') {

    $request['consignor_id'] = $this->_consignorId;
    $request['consignor_key'] = $this->_consignorKey;

    if ($this->_testMode) {
      $url = self::SERVER_TEST.'/trace/xml_trace';
    } else {
      $url = self::SERVER_PRODUCTION.'/trace/xml_trace';
    }

    $request = http_build_query($request);

    $result = $this->_call('POST', $url, $request);

  // Rewrite shipping_states depth for Array because arrays cannot have duplicate keys
    if (isset($result['shipping_states'])) {
      $shipping_states = array();
      if (!empty($result['shipping_states']['shipping_state'])) {

       // Future proof for case of multiple statuses (sequentially indexed)
        if (!array_filter(array_keys($result['shipping_states']['shipping_state']), 'is_string')) {
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

  public function CalculatePackage($items) {

    $package = array(
      'weight' => 0,
      'dimensions' => array(0, 0, 0),
      'weight_unit' => 'kg',
      'length_unit' => 'cm',
    );

    foreach ($items as $item) {

      if (!empty($item['weight_unit'])) {
        $item['weight'] = $this->ConvertWeight($item['weight'], $item['weight_unit'], $package['weight_unit']);
      }

      if (!empty($item['length_unit'])) {
        $item['length'] = $this->ConvertLength($item['length'], $item['length_unit'], $package['length_unit']);
        $item['width'] = $this->ConvertLength($item['width'], $item['length_unit'], $package['length_unit']);
        $item['height'] = $this->ConvertLength($item['height'], $item['length_unit'], $package['length_unit']);
      }

      for ($i=0; $i < $item['quantity']; $i++) {

        $package['weight'] += $item['weight'];

        $item_dimensions = array(
          $item['length'],
          $item['width'],
          $item['height'],
        );

        rsort($item_dimensions, SORT_NUMERIC);

        $package['dimensions'][2] += $item_dimensions[2];
        if ((string)$item_dimensions[1] > (string)$package['dimensions'][1]) $package['dimensions'][1] = $item_dimensions[1];
        if ((string)$item_dimensions[0] > (string)$package['dimensions'][0]) $package['dimensions'][0] = $item_dimensions[0];

        rsort($package['dimensions'], SORT_NUMERIC);
      }
    }

    $package['weight'] = (float)round($package['weight'], 3);
    $package['length'] = (float)round($package['dimensions'][0], 2);
    $package['width'] = (float)round($package['dimensions'][1], 2);
    $package['height'] = (float)round($package['dimensions'][2], 2);

    unset($package['dimensions']);

    return $package;
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

  private function _call(string $method, string $url, string $data = null) {

    $this->_lastRequest = array();
    $this->_lastResponse = array();
    $this->_lastLog = '';

    $headers = array(
      'User-Agent' => 'Fraktjakt-Client-PHP/'.self::VERSION,
    );

    if (empty($headers['Content-Type']) && !empty($data)) {
      $headers['Content-Type'] = 'application/x-www-form-urlencoded';
    }

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

    $out = $method ." ". $parts['path'] . ((isset($parts['query'])) ? '?' . $parts['query'] : '') ." HTTP/1.1\r\n" .
         "Host: ". $parts['host'] ."\r\n";

    foreach ($headers as $key => $value) {
      $out .= "$key: $value\r\n";
    }

    $bodyFound = false;
    $responseHeaders = '';
    $responseBody = '';
    $microtimeStart = microtime(true);

    $this->_lastRequest = array(
      'timestamp' => time(),
      'head' => $out,
      'body' => $data,
    );

    if (!$socket = stream_socket_client(strtr('scheme://host:port', $parts), $errno, $errstr, $this->_timeout)) {
      throw new \Exception('Error calling URL ('. $url .'): '. $errstr);
    }

    stream_set_timeout($socket, $this->_timeout);

    fwrite($socket, $out . "\r\n");
    fwrite($socket, $data);

    while (!feof($socket)) {
      if ((microtime(true) - $microtimeStart) > $this->_timeout) {
       throw new \Exception('Timout during retrieval');
       return false;
      }

      $line = fgets($socket);
      if ($line == "\r\n") {
       $bodyFound = true;
       continue;
      }

      if ($bodyFound) {
       $responseBody .= $line;
       continue;
      }

      $responseHeaders .= $line;
    }

    fclose($socket);

    preg_match('#HTTP/\d(\.\d)?\s(\d{3})#', $responseHeaders, $matches);
    $status_code = $matches[2];

    $this->_lastResponse = array(
      'timestamp' => time(),
      'status_code' => $status_code,
      'head' => $responseHeaders,
      'duration' => round((microtime(true) - $microtimeStart)*1000),
      'bytes' => strlen($responseHeaders . "\r\n" . $responseBody),
      'body' => $responseBody,
    );

    parse_str($data, $request_object);
    if (isset($request_object['xml'])) {
      $xml_request = preg_replace('#(\R+)#', "\r\n", urldecode($request_object['xml']));
    }

  // Pretty printed response
    $dom = new \DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($responseBody);
    $xml_response = $dom->saveXML();

    $this->_lastLog = (
      '##'. str_pad(' XML Request Object ', 80, '#', STR_PAD_RIGHT) . "\r\n\r\n" .
      ((!empty($xml_request)) ? $xml_request : 'n/a') . "\r\n" .

      '##'. str_pad(' XML Response Object ', 80, '#', STR_PAD_RIGHT) . "\r\n\r\n" .
      ((!empty($xml_response)) ? $xml_response : 'n/a') . "\r\n" .

      '##'. str_pad(' ['. date('Y-m-d H:i:s', $this->_lastRequest['timestamp']) .'] Raw HTTP Request ', 80, '#', STR_PAD_RIGHT) . "\r\n\r\n" .
      $this->_lastRequest['head']."\r\n" .
      $this->_lastRequest['body']."\r\n\r\n" .

      '##'. str_pad(' ['. date('Y-m-d H:i:s', $this->_lastResponse['timestamp']) .'] Raw HTTP Response — '. number_format($this->_lastResponse['bytes'], 0, '.', ',') .' bytes transferred in '. number_format($this->_lastResponse['duration']) .' ms ', 80, '#', STR_PAD_RIGHT) . "\r\n\r\n" .
      $this->_lastResponse['head']."\r\n" .
      $this->_lastResponse['body']."\r\n\r\n"
    );

    if (empty($responseBody)) {
      throw new \Exception('No response from remote machine');
    }

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
      throw new \Exception("Could not convert result to an array" . PHP_EOL . print_r($xml, true));
    }

    return $result;
  }

  private function _xmlToArray($xml) {
    return json_decode(json_encode($xml), true);
  }

  private function _arrayToXml(array $array, string $rootElement, string $encoding) {

    $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="'. $encoding .'"?><'.$rootElement.'/>');

    $this->_arrayToXmlIterator($array, $xml);

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

  private function _arrayToXmlIterator(array $array, &$xml) {

    foreach ($array as $key => $value) {

      if (is_array($value)) {

        if (is_numeric($key)) {
          $this->_arrayToXmlIterator($value, $xml);
        } else {
          $subnode = $xml->addChild($key);
          $this->_arrayToXmlIterator($value, $subnode);
        }

      } else {
        $xml->addChild($key, $value);
      }
    }
  }
}
