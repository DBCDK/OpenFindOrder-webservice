<?php

class openFindOrder extends webServiceServer {
  //public $stat;

  /** \brief
   * constructor; start watch; call parent's constructor
   */
  public function __construct() {
    parent::__construct('openfindorder.ini');

    define('THIS_NAMESPACE', $this->xmlns['ofo']);
    $this->watch->start('openfindorderWS');

    $this->aaa = new ofoAaa($this->config->get_section('aaa'));
    $this->aaa->setOpenagencyList($this->config->get_section('setup'));
  }

  /** \brief
   * destructor: stop watch; log for statistics
   */
  public function __destruct() {
    $this->watch->stop('openfindorderWS');
    //verbose::log(TIMER, $this->watch->dump());
  }

  /** \brief Echos config-settings
   *
   */
  public function show_info() {
    echo '<pre>';
    echo 'version             ' . $this->config->get_value('version', 'setup') . '<br/>';
    echo 'log                 ' . $this->config->get_value('logfile', 'setup') . '<br/>';
    echo 'db                  ' . $this->config->get_value('connectionstring', 'setup') . '<br/>';
    echo 'xsd                 ' . $this->config->get_value('schema', 'setup') . '<br/>';
    echo 'wsdl                ' . $this->config->get_value('wsdl', 'setup') . '<br/>';

    echo 'implemented methods:' . '<br/>';
    $methods = $this->config->get_value('soapAction', 'setup');
    foreach ($methods as $key => $value) {
      echo '    ' . $value . '<br/>';
    }

    echo '</pre>';
    die();
  }

  /** \brief
   * Common request handler for most methods.
   * @param; request parameters in request-xml object.
   */
  public function requestHandler($param, $method = NULL) {
    self::auditTrail($param);

    if ($error = ofoAuthentication::authenticate($this->aaa, __FUNCTION__)) {
      return $this->send_error($error);
    }

    if (!$this->in_house()) {
      if (!$this->aaa->authorization($param)) {
        $error = $this->aaa->getAuthorizationError();
        return $this->send_error($error);
      }
    }

    $ors = new orsClass($this->soap_action, $this->config);
    $ors->setQuery($param);

    if ($ors->getError()) {
      return $this->send_error($ors->getErrorMsg());
    }
    $ors->findOrders();
    switch ($method) {
      case 'getReceipts':
        return $this->getReceiptsResponse($ors->getResponse(), $ors->getTotal(), $ors->getQuery());
      default:
        return $this->findOrderResponse($ors);
    }
  }

  /** \brief
   * The service request for all orders (optionally for a specific order system)
   * @param; request parameters in request-xml object.
   */
  public function findAllOrders($param) {
    return $this->requestHandler($param);
  }

  /** \brief
   *  The service request for all ill orders
   * @param; request parameters in request-xml object.
   */
  public function findAllIllOrders($param) {
    return $this->requestHandler($param);
  }

  /** \brief
   * The service request for orders which has been finished manually
   * @param; request parameters in request-xml object.
   */
  public function findManuallyFinishedIllOrders($param) {
    return $this->requestHandler($param);
  }

  /** \brief
   *  The service request for open endUser orders
   * @param; request parameters in request-xml object.
   */
  public function findAllOpenEndUserOrders($param) {
    return $this->requestHandler($param);
  }

  /** \brief
   *  The service request for orders on material not localized to the end user agency.
   * @param; request parameters in request-xml object.
   */
  public function findNonLocalizedEndUserOrders($param) {
    return $this->requestHandler($param);
  }

  /** \brief
   *  The service request for orders on material localized to the end user agency.
   * @param; request parameters in request-xml object.
   */
  public function findLocalizedEndUserOrders($param) {
    return $this->requestHandler($param);
  }

  /** \brief
   *  The service request for closed ill orders
   * @param; request parameters in request-xml object.
   */
  public function findClosedIllOrders($param) {
    return $this->requestHandler($param);
  }

  /** \brief
   *  The service request for open ill orders
   * @param; request parameters in request-xml object.
   */
  public function findOpenIllOrders($param) {
    return $this->requestHandler($param);
  }

  /** \brief
   *  The service request for all non ill orders
   * @param; request parameters in request-xml object.
   */
  public function findAllNonIllOrders($param) {
    return $this->requestHandler($param);
  }

  /**\brief
   * The service request for a specific order (orderId)
   * @param; request parameters in request-xml object.
   */
  public function findSpecificOrder($param) {
    return $this->requestHandler($param);
  }

  /**\brief
   * The service request for orders from a specific user (userId, userName or userMail)
   * @param; request parameters in request-xml object.
   */
  public function findOrdersFromUser($param) {
    return $this->requestHandler($param);
  }

  /**\brief
   * The service request for orders from unknown users (general)
   * @param; request parameters in request-xml object.
   */
  public function findOrdersFromUnknownUser($param) {
    return $this->requestHandler($param);
  }

  /**\brief
   * The service request for reason for auto forward (autoForwardReason)
   * @param; request parameters in request-xml object.
   */
  public function findOrdersWithAutoForwardReason($param) {
    return $this->requestHandler($param);
  }

  /**\brief
   * The service request for automatically forwarded orders (general)
   * @param; request parameters in request-xml object.
   */
  public function findAutomatedOrders($param) {
    return $this->requestHandler($param);
  }

  /**\brief
   * The service request for automatically forwarded orders (general)
   * @param; request parameters in request-xml object.
   */
  public function findOwnAutomatedOrders($param) {
    return $this->requestHandler($param);
  }

  /**\brief
   * The service request for orders from a specific ill-cooperation (kvik, norfri or articleDirect)
   * @param; request parameters in request-xml object.
   */
  public function findOrderOfType($param) {
    return $this->requestHandler($param);
  }

  /**\brief
   *  The service request for a biblographical search of orders
   * @param; request parameters in request-xml object
   */
  public function bibliographicSearch($param) {
    return $this->requestHandler($param);
  }

  /**\brief
   * The service request for non-automatatically forwarded orders (general)
   * @param; request parameters in request-xml object
   */
  public function findNonAutomatedOrders($param) {
    return $this->requestHandler($param);
  }

  /**\brief
   * The service request for the receipt of an order
   * @param; request parameters in request-xml object
   */
  public function getReceipts($param) {
    return $this->requestHandler($param, 'getReceipts');
  }

  /**\brief
   * The service request for formatting an order receipt
   * @param; request parameters in request-xml object
   */
  public function formatReceipt($param) {

    if ($error = ofoAuthentication::authenticate($this->aaa, __FUNCTION__)) {
      return $this->send_error($error, 'formatReceipt');
    }

    $ors = new orsClass($this->soap_action, $this->config);
    if (!$receipt = self::xs_json_decode($param->json->_value)) {
      return $this->send_error('Error decoding json string', 'formatReceipt');
    }

    $order = new stdClass();
    $order->resultPosition->_value = 1;
    $order->resultPosition->_namespace = THIS_NAMESPACE;
    self::isil_or_dnucni($receipt->pickUpAgencyId, $receipt->pickUpAgencyIdType);
    self::isil_or_dnucni($receipt->requesterId, $receipt->requesterIdType);
    self::isil_or_dnucni($receipt->responderId, $receipt->responderIdType);
    foreach ($ors->getXmlfields() as $key => $upper_key) {
      if (!empty($receipt->$key)) {
        $order->$key->_value = $ors->modify_some_data($key, $receipt->$key);
        $order->$key->_namespace = THIS_NAMESPACE;
      }
    }
    $orders = [];
    $orders[0]->_value = $order;
    $orders[0]->_namespace = THIS_NAMESPACE;

    return $this->getReceiptsResponse($orders, '1', '');
  }

  /* ------------------------------- private function --------------------------------------- */

  /**\brief
   * Generate response-object from given array of orders.
   * @orders; array of orders
   * return; orders as xml-objects
   */
  private function findOrderResponse($ors) {

    $orders = $ors->getResponse();
    $total = $ors->getTotal();
    $debug_info = $ors->getQuery();
    $status = $ors->getStatus();

    $response = new stdClass();
    $response->findOrdersResponse->_namespace = THIS_NAMESPACE;

    if ($status == 'ERROR') {
      // See: openfindorder.xsd -> errorType
      return $this->send_error('open find order service not available', 'findOrdersResponse', $debug_info);
    }

    if ($status == 'OTHER') {
      return $this->send_error('open find order service not available', 'findOrdersResponse', $debug_info);
    }

    // Empty result-set.
    if ($total == 0) {
      return $this->send_error('no orders found', 'findOrdersResponse', $debug_info);
    }

    // Total > 0, but parse failed.
    if (empty($orders)) {
      // See: openfindorder.xsd -> errorType
      return $this->send_error('Error decoding json string', 'findOrdersResponse', $debug_info);
    }

    $result = &$response->findOrdersResponse->_value->result;
    $result->_namespace = THIS_NAMESPACE;
    $result->_value->numberOfOrders->_namespace = THIS_NAMESPACE;
    $result->_value->numberOfOrders->_value = $total;
    $result->_value->order = $orders;
    $result->_value->debugInfo->_value = $debug_info;

    return $response;
  }

  /**\brief
   * Generate response-object from given array of orders.
   * @receipts; array of orders
   * return; receipts as xml-objects
   */
  private function getReceiptsResponse($receipts, $number_of_receipts = 0, $debug_info = '') {

    $response = new stdClass();
    $response->getReceiptsResponse->_namespace = THIS_NAMESPACE;

    if ($receipts === FALSE) {
      return $this->send_error('no orders found', 'getReceiptsResponse');
    }

    // empty result-set
    if (empty($receipts)) {
      return $this->send_error('no orders found', 'getReceiptsResponse');
    }

    $result = &$response->getReceiptsResponse;
    $result->_namespace = THIS_NAMESPACE;
    $result->_value->numberOfReceipts->_namespace = THIS_NAMESPACE;
    $result->_value->numberOfReceipts->_value = $number_of_receipts;

    if ($receipts->error) {
      $receipts->error->_namespace = THIS_NAMESPACE;
      $result->_value = $receipts;
    }
    else {
      $result->_value->receipt = $receipts;
    }

    $result->_value->debugInfo->_value = $debug_info;
    return $response;
  }

  /**\brief
   * set ISIL or DNUCNI as type and modify id properly
   * @param; id
   * @param; type
   */
  private function isil_or_dnucni(&$id, &$type) {
    $number = preg_replace('/\D/', '', $id);
    if ((strlen($number) == 6) && in_array($number[0], array('7', '8'))) {
      $id = 'DK-' . $number;
      $kode = 'ISIL';
    }
    else {
      $kode = 'DNUCNI';
    }
    if (empty($type)) {
      $type = $kode;
    }
  }

  /** \brief
   * decode a json string and change booleans to xs:boolean types
   */
  private function xs_json_decode($json_str) {
    if ($obj = json_decode($json_str)) {
      foreach ($obj as &$item) {
        if (is_bool($item)) {
          $item = ($item ? 'true' : 'false');
        }
      }
    }
    return $obj;
  }

  /** \brief
   * send errormessage as xml response-object
   */
  private function send_error($message, $response_tag = 'findOrdersResponse', $debug_info = null) {
    $response = new stdClass();
    $response->$response_tag->_namespace = THIS_NAMESPACE;
    $error = new stdClass();
    $error->_namespace = THIS_NAMESPACE;
    $error->_value = $message;
    $response->$response_tag->_value->error = $error;

    if ($debug_info) {
      $debug = new stdClass();
      $debug->_namespace = THIS_NAMESPACE;
      $debug->_value = $debug_info;
      $response->$response_tag->_value->debugInfo = $debug;
    }

    return $response;
  }

  /**
   * Call AuditTrail::log with user request and authentication info
   *
   * @param $param Object containing the actual request
   *
   */
  private function auditTrail($param) {
    $user = $param->authentication->_value->groupIdAut->_value . '::' . $param->authentication->_value->userIdAut->_value;
    try {
      \DBC\AT\AuditTrail::log(
          $user == '::' ? 'no-user-specified' : $user,
          [$_SERVER['HTTP_X_FORWARDED_FOR'] ?: $_SERVER['REMOTE_ADDR']],
          'openFindOrder' . '::' . $this->soap_action,
          'read',
          json_encode(self::cleanBadgerfish($param->requesterAgencyId)),
          [json_encode(self::cleanBadgerfish($param))]
      );
    } catch (Exception $e) {
      VerboseJson::log(ERROR, 'Cannot write audit trail. Message: ' . $e->getMessage());
    }
  }

  /** remove namespace objects and implode value level
   *
   * @param $misc
   * @return bool|float|int|stdClass|string
   */
  private function cleanBadgerfish($misc) {
    if (is_scalar($misc) || empty($misc)) return $misc;
    $ret = new stdClass();
    foreach ($misc as $key => $value) {
      if ($key !== '_namespace') {
        if ($key === '_value') {
          return self::cleanBadgerfish($value);
        } else {
          $ret->$key = self::cleanBadgerfish($value);
        }
      }
    }
    return $ret;
  }
}
