<?php namespace Fraktjakt;

/**
*  Fraktjakt Client
*  @author T. Almroth
*/

class Client {

  const VERSION  = '1.0.0';
  const SERVER_TEST  = 'https://api2.fraktjakt.se';
  const SERVER_PRODUCTION  = 'https://www.fraktjakt.se';

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

    $request['consignor']['api_version'] = '3.0.0';
    $request['consignor']['id'] = $this->_consignorId;
    $request['consignor']['key'] = $this->_consignorKey;

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

    return array(
      'status' => 'ok',
      'link' => ($this->_testMode ? self::SERVER_TEST : self::SERVER_PRODUCTION) . '/shipments/show/'. $result['shipment_id'] . '?access_code=' . $result['access_code'],
    ) + $result;
  }

  public function Query(array $request, string $encoding = 'UTF-8') {

    $request['consignor']['api_version'] = '3.0.0';
    $request['consignor']['id'] = $this->_consignorId;
    $request['consignor']['key'] = $this->_consignorKey;

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
        foreach ($result['shipping_products']['shipping_product'] as $shipping_product) {
          $shipping_products[] = $shipping_product;
        }
      }
      $result['shipping_products'] = $shipping_products;
    }

    if (empty($result['shipping_products'])) {
      throw new \Exception('No shipping products found');
    }

    return array(
      'status' => 'ok',
      'link' => ($this->_testMode ? self::SERVER_TEST : self::SERVER_PRODUCTION) . '/shipments/show/'. $result['id'] . '?access_code=' . $result['access_code'],
    ) + $result;
  }

  public function Requery(array $request, string $encoding = 'UTF-8') {

    $request['consignor']['api_version'] = '3.0.0';
    $request['consignor']['id'] = $this->_consignorId;
    $request['consignor']['key'] = $this->_consignorKey;

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

    return array(
      'status' => 'ok',
      'link' => ($this->_testMode ? self::SERVER_TEST : self::SERVER_PRODUCTION) . '/shipments/show/'. $result['id'] . '?access_code=' . $result['access_code'],
    ) + $result;
  }

  public function Shipment(array $request, string $encoding = 'UTF-8') {

    $request['consignor']['api_version'] = '3.0.0';
    $request['consignor']['id'] = $this->_consignorId;
    $request['consignor']['key'] = $this->_consignorKey;

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

    return array(
      'status' => 'ok',
      'link' => ($this->_testMode ? self::SERVER_TEST : self::SERVER_PRODUCTION) . '/shipments/show/'. $result['shipment_id'] . '?access_code=' . $result['access_code'],
    ) + $result;
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

    return array(
      'status' => 'ok',
    ) + $result;
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

    $found_body = false;
    $response_headers = '';
    $response_body = '';
    $microtime_start = microtime(true);

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
      if ((microtime(true) - $microtime_start) > $this->_timeout) {
       throw new \Exception('Timout during retrieval');
       return false;
      }

      $line = fgets($socket);
      if ($line == "\r\n") {
       $found_body = true;
       continue;
      }

      if ($found_body) {
       $response_body .= $line;
       continue;
      }

      $response_headers .= $line;
    }

    fclose($socket);

    preg_match('#HTTP/\d(\.\d)?\s(\d{3})#', $response_headers, $matches);
    $status_code = $matches[2];

    $this->_lastResponse = array(
      'timestamp' => time(),
      'status_code' => $status_code,
      'head' => $response_headers,
      'duration' => round(microtime(true) - $microtime_start, 3),
      'bytes' => strlen($response_headers . "\r\n" . $response_body),
      'body' => $response_body,
    );

    parse_str($data, $request_object);
    if (isset($request_object['xml'])) {
      $xml = preg_replace('#(\R+)#', "\r\n", urldecode($request_object['xml']));
    }

    $this->_lastLog = (
      "## XML Request Object ##############################\r\n\r\n" .
      ((!empty($xml)) ? $xml : '(Null)') . "\r\n\r\n" .
      "## [". date('Y-m-d H:i:s', $this->_lastRequest['timestamp']) ."] HTTP Request ##############################\r\n\r\n" .
      $this->_lastRequest['head']."\r\n" .
      $this->_lastRequest['body']."\r\n\r\n" .
      "## [". date('Y-m-d H:i:s', $this->_lastResponse['timestamp']) ."] HTTP Response — ". number_format($this->_lastResponse['bytes'], 0, '.', ',') ." bytes transferred in ". (float)$this->_lastResponse['duration'] ." s ##############################\r\n\r\n" .
      $this->_lastResponse['head']."\r\n" .
      $this->_lastResponse['body']."\r\n\r\n"
    );

    if (empty($response_body)) {
      throw new \Exception('No response from remote machine');
    }

  // Parse XML result
    if (!$xml = @simplexml_load_string($response_body, 'SimpleXMLElement', LIBXML_NOCDATA)) {
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

    $dom = new \DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
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
