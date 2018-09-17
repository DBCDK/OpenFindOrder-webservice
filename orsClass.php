<?php
/**
 * @file
 * class to handle request for orders.
 * ors -service url:
 * http://ors-maintenance.iscrum-staging.svc.cloud.dbc.dk:8080/api/
 *
 * api documentation:
 * https://dbcjira.atlassian.net/wiki/spaces/IS/pages/18087971/ORS2-Maintenance+API+beskrivelse
 *
 */

// include class to look up pickupagencies
require_once "orsAgency.php";
require_once "xsdparse.php";

class orsClass {
  private $config;
  private $action;
  private $curl;
  private $xmlfields;
  
  private $query;
  private $response;
  private $start;
  private $step;
  private $total;
  private $status;
  private $message;
  private $error;
  private $err_msg;
  

  /**
   * orsClass constructor.
   *
   * @param string $action
   * @param inifile $config
   */
  public function __construct($action, $config) {
    $this->config = $config;
    $this->action = $action;
    $this->curl = new curl();
    $this->curl->set_option(CURLOPT_TIMEOUT, 30);
    $this->step = NULL;
    $this->query = NULL;
    $this->start = NULL;
    $this->total = NULL;
    $this->error = FALSE;
    $this->err_msg = NULL;
    $this->status = NULL;
    $this->message = NULL;
    $this->response = array();
    
    // get xml schema
    $schemafile = $config->get_value('schema', 'setup');
    if (!file_exists($schemafile)) {
      die('xsd not found: ' . $schemafile);
    }
    $schema = new xml_schema();
    $schema->get_from_file($schemafile);

    // set xml-fields
    $this->action = $action;
    if (in_array($this->action, array('getReceipts', 'formatReceipt'))) {
      $this->xmlfields = $schema->get_sequence_array('receipt');
    }
    else {
      $this->xmlfields = $schema->get_sequence_array('order');
    }
  }

  /**
   * Parse input, and create a query.
   *
   * @param stdClass $param
   */
  public function setQuery($param) {
    $orsAgency = new orsAgency($this->config->get_value('openagency_agency_list', 'setup'));

    $consistency = $orsAgency->check_agency_consistency($param);
    if (!$consistency) {
      $this->setError($orsAgency->getErrorMsg());
      return false;
    }

    $ret = array();
    
    switch ($this->action) {
      case 'findAllOrders':
        break;
      case 'findAllIllOrders':
        $ret['orderType'] = array('inter_library_request');
        break;
      case 'findAllNonIllOrders':
        $ret['orderType'] = array('enduser_request', 'enduser_illrequest');
        break;
      case 'findAllOpenEndUserOrders':
        $ret['orderType'] = array('enduser_request', 'enduser_illrequest');
        $ret['closed'] = 'false';
        break;
      case 'findOpenIllOrders':
        $ret['orderType'] = array('inter_library_request');
        $ret['provideranswer'] = 'empty';
        $ret['isshipped'] = false;
        if (!empty($param->requesterAgencyId)) {
          $ret['requesterorderstate'] = array('not finished');
        }
        if ($param->responderAgencyId) {
          $ret['providerorderstate'] = array('not finished');
        }
        break;
      case 'findOrdersFromUnknownUser':
        $ret['userIdAuthenticated'] = 'no';
        break;
      case 'findAutomatedOrders':
        $order_type = ($param->orderType->_value ? $param->orderType->_value : 'inter_library_request');
        $ret['orderType'] = array($order_type);
        $ret['autoForwardResult'] = 'automated';
        break;
      case 'findNonAutomatedOrders':
        $ret['autoForwardReason'] = 'non_automated';
        break;
      case 'findOwnAutomatedOrders':
        $order_type = ($param->orderType->_value ? $param->orderType->_value : 'inter_library_request');
        $ret['orderType'] = array($order_type);
        $ret['autoforwardown'] = 'yes';
        break;
      case 'findClosedIllOrders':
        $ret['orderType'] = array('inter_library_request');
        if ($param->orderStatus->_value == 'shipped') {
          $ret['isshipped'] = true;
        }
        elseif ($param->orderStatus->_value) {
          $ret['provideranswer'] = $param->orderStatus->_value;
        }
        else {
          $ret['provideranswer'] = 'empty';
        }
        if ($param->requesterAgencyId) {
          $ret['requesterOrderState'] = array('finished');
        }
        if ($param->responderAgencyId) {
          $ret['providerOrderState'] = array('finished');
        }
        break;
      case 'findManuallyFinishedIllOrders':
        $ret['orderType'] = array('inter_library_request');
        if (isset($param->requesterOrderState->_value)) {
          $ret['requesterOrderState'] = $param->requesterOrderState->_value;
        }
        elseif (isset($param->providerOrderState->_value)) {
          $ret['providerOrderState'] =  $param->providerOrderState->_value;
        }
        break;
      case 'findSpecificOrder':
        if ($param->orderType->_value == 'enduser_order') {
          $ret['ordertype'] = array('enduser_request', 'enduser_illrequest');
        }
        elseif ($param->orderType->_value == 'inter_library_order') {
          $ret['ordertype'] = array('inter_library_order');
        }
        $this->add_string('orderId', $param->orderId, $ret);
        break;
      case 'findOrdersFromUser':
        if ($param->orderType->_value == 'enduser_order') {
          $ret['ordertype'] = array('enduser_request', 'enduser_illrequest');
        }
        elseif ($param->orderType->_value == 'inter_library_order') {
          $ret['ordertype'] = array('inter_library_order');
        }
        $this->add_string('userId', $param->userId, $ret);
        $this->add_string('userMail', $param->userMail, $ret);
        $this->add_string('userName', $param->userName, $ret);
        $this->add_string('userFreeText', $param->userFreeText, $ret);
        break;
      case 'findLocalizedEndUserOrders':
        $ret['ordertype'] = array('enduser_request');
        $ret['closed'] = ($this->xs_boolean($param->closed->_value) ? 'true' : 'false');
        break;
      case 'findNonLocalizedEndUserOrders':
        $ret['ordertype'] = array('enduser_illrequest');
        $ret['closed'] = ($this->xs_boolean($param->closed->_value) ? 'true' : 'false');
        break;
      case 'findOrdersWithAutoForwardReason':
        $this->add_string('autoforwardreason', $param->autoForwardReason, $ret);
        break;
      case 'findOrderOfType': 
        $ret['ordertype'] = array('enduser_request', 'enduser_illrequest');
        // string: electronic|pickup|postal
        $this->add_string('articleDirect', $param->articleDirect, $ret);
        // boolean
        $this->add_string('kvik', $param->kvik, $ret);
        $this->add_string('norfri', $param->norfri, $ret);
        break;
      case 'bibliographicSearch':
        if ($param->orderType->_value == 'enduser_order') {
          $ret['ordertype'] = array('enduser_request', 'enduser_illrequest');
        }
        elseif ($param->orderType->_value == 'inter_library_order') {
          $ret['ordertype'] = array('inter_library_request');
        }
        $this->add_string('author', $param->author, $ret);
        $this->add_string('title', $param->title, $ret);
        $this->add_string('bibliographicFreeText', $param->bibliographicFreeText, $ret);
        break;
      case 'getReceipts':
        if (isset($param->orderId->_value)) {
          $this->add_string('orderId', $param->orderId, $ret);
        }
        break;
      case 'formatReceipt': //  See: openFindOrder->formatReceipt()
        break;
      default:
        break;
    }

    // NB: Bør dette ikke kun gælde for hovedbiblioteks-nummer ?
    $requester = $orsAgency->expand_library($param->requesterAgencyId);
    $responder = $orsAgency->expand_library($param->responderAgencyId);
    
    switch ($this->action) {
      case 'findAllOrders':
      case 'findAllIllOrders':
      case 'findAllNonIllOrders':
      case 'findAllOpenEndUserOrders':
      case 'findOpenIllOrders':
      case 'findOrdersFromUnknownUser':
      case 'findAutomatedOrders':
      case 'findNonAutomatedOrders':
      case 'findOwnAutomatedOrders':
      case 'findClosedIllOrders':
      case 'findManuallyFinishedIllOrders':
      case 'findSpecificOrder':
      case 'findOrdersFromUser':
      case 'findLocalizedEndUserOrders':
      case 'findNonLocalizedEndUserOrders':
      case 'findOrdersWithAutoForwardReason':
      case 'getReceipts':
        $this->add_common_pars($param, $ret);
        $this->add_list('requesterId', $requester, $ret);
        $this->add_list('responderId', $responder, $ret);
        break;
      case 'bibliographicSearch':
      case 'findOrderOfType': 
        $this->add_common_pars($param, $ret);
        break;
      case 'formatReceipt': //  See: openFindOrder->formatReceipt()
        break;
      default:
        break;
    }

    $this->setQueryArray($ret);
  }


  /**
   * @param array $param
   *
   * This is mainly so we can set it in howRU.
   */
  public function setQueryArray($param) {
    $this->query = $param;
  }
    
  /**
   * CURL request for orders.
   * @param array $request
   *
   * NB: ORS2 documentation has a GET /api/orders/{orderId} method,
   *     but POSTing a JSON query with an orderId variable works just as well.
   *
   */
  public function findOrders() {
    $json = json_encode($this->query);
    $url = $this->config->get_value('ors2_url', 'ORS');
    // this is for the (find)order api
    $url .= 'orders';
    
    // initialize curl for post request
    $this->curl->set_post($json);
    $this->curl->set_url($url);
    $this->curl->set_option(CURLOPT_RETURNTRANSFER, TRUE);
    $this->curl->set_option(CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $result = $this->curl->get();
    
    // Check cURL response.
    $status = $this->curl->get_status();
    if ($this->curl->has_error()) {
      VerboseJson::log(ERROR, array(
        'system' => 'ORS2',
        'query' => $json,
        'url' => $status['url'],
        'total_time' => $status['total_time'],
        'http_code' => $status['http_code'] ,
        'errno' => $status['errno'] ,
        'error' => $status['error'])
      );
      $this->setError('open find order service not available');
    }
    else {
      VerboseJson::log(TRACE, array(
        'system' => 'ORS2',
        'query' => $json ,
        'url' => $status['url'],
        'total_time' => $status['total_time'])
      );
    }
    
    $this->response = $this->parseResponse($result);
    
  }

  /**
   * Parse ORS2 response.
   * @return array
   */
  public function parseResponse($result) {
    $result = json_decode($result, true);

    $this->start =    !empty($result['start'])   ? $result['start'] :   NULL;
    $this->step =     !empty($result['step'])    ? $result['step'] :    NULL;
    $this->total =    !empty($result['total'])   ? $result['total'] :   NULL;
    $this->status =   !empty($result['status'])  ? $result['status'] :  NULL;
    $this->message =  !empty($result['message']) ? $result['message'] : NULL;
    
    if (!empty($result['error'])) {
      $this->setError($result['error']);
    }
    
    $orders = array();
    foreach ($result['orderList'] as $n => $resultObject) {
      
      $orders[$n]->_value->resultPosition->_namespace = THIS_NAMESPACE;
      $orders[$n]->_value->resultPosition->_value = $n + 1;
      
      $orders[$n]->_value->orderId->_namespace = THIS_NAMESPACE;
      $orders[$n]->_value->orderId->_value = $resultObject['orderKey'];
      
      $orders[$n]->_value->requesterId->_namespace = THIS_NAMESPACE;
      $orders[$n]->_value->requesterId->_value = $resultObject['requesterId'];
      
      // Ignore values:
      // $resultObject['responderId'];
      // $resultObject['active'];
      // $resultObject['activeId'];
      // $resultObject['creationTimestamp'];
      // $resultObject['lastAccessTimestamp'];

      // to check:
      $buffer = array();
      foreach ($resultObject['orderJSON'] as $key => $orderItem) {
        switch ($key) {
          // Not in XSD order element:
          case 'autCreationDate':
          case 'autMatType':
          case 'invalid':
          case 'isRecalled':
          case 'numberOfRecalls':
          case 'seriesTitleNumber':
            break;
          case 'autoForwardDeliverToday':
          case 'autoForwardOwn':
          case 'cancelled':
          case 'closed':
          case 'copy':
          case 'exactEdition':
          case 'isShipped':
          case 'kvik':
          case 'norfri':
          case 'renewed':
            $buffer[$key] = ($this->xs_boolean($orderItem)) ? 'true' : 'false';
            break;
          case 'cancelledDate':
          case 'creationDate':
          case 'desiredDateDue':
          case 'expectedDelivery':
          case 'fromDate':
          case 'lastRenewalDate':
          case 'needBeforeDate':
          case 'providerAnswerDate':
          case 'receivedDate':
          case 'renewPendingDate':
          case 'resendToRequesterDate':
          case 'resendToResponderDate':
          case 'returnedDate':
          case 'toDate':
            // convert timestamp (ex: "2018-09-02T17:07:28Z") to dateformat YYYY-MM-DD
            $datetime = new DateTime($orderItem);
            $datetime->setTimezone(new DateTimeZone('Europe/Copenhagen'));
            $buffer[$key] = $datetime->format('Y-m-d');
            break;
          case 'closedDate': // not type="xs:dateTime" in xsd, but old webservice return dateTime.
          case 'dateDue':
          case 'shippedDate':
            $datetime = new DateTime($orderItem);
            $datetime->setTimezone(new DateTimeZone('Europe/Copenhagen'));
            $buffer[$key] = $datetime->format('Y-m-d\TH:i:s\Z');
            break;
          case 'lastRelevantModification':
            $datetime = new DateTime($orderItem);
            $datetime->setTimezone(new DateTimeZone('Europe/Copenhagen'));
            $buffer['lastModification'] = $datetime->format('Y-m-d\TH:i:s\Z');
            break;
          default:
            $buffer[$key] = $orderItem;
        }
      }
      
      foreach ($resultObject['userData'] as $key => $orderItem) {
        switch ($key) {
          case 'userIdAuthenticated':
            $buffer[$key] = ($orderItem == 'yes') ? 'true' : 'false';
            break;
          default:
            $buffer[$key] = $orderItem;
        }
      }
      
      // Case-insensitive sorting.
      ksort($buffer, SORT_NATURAL | SORT_FLAG_CASE);
      
      foreach ($buffer as $key => $orderItem) {
        $orders[$n]->_value->$key->_namespace = THIS_NAMESPACE;
        $orders[$n]->_value->$key->_value = $orderItem;
      }

      $orders[$n]->_namespace = THIS_NAMESPACE;
      
    }
    
    return $orders;
  }


  /**
   * Return ORS2 query.
   * @return string
   */
  public function getQuery() {
    return json_encode($this->query);
  }

  /**
   * Return ORS2 response.
   *
   * @return array
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * Return ORS2 start.
   * @return array
   */
  public function getStart() {
    return $this->start;
  }
  
  /**
   * Return ORS2 stepValue.
   * @return array
   */
  public function getStep() {
    return $this->step;
  }
  
  /**
   * Return ORS2 total.
   * @return array
   */
  public function getTotal() {
    return $this->total;
  }
  
  /**
   * Return ORS2 status.
   * @return array
   */
  public function getStatus() {
    return $this->status;
  }
  
  /**
   * Return ORS2 message.
   * @return array
   */
  public function getMessage() {
    return $this->message;
  }
  
  /**
   * Return schema fields.
   * @return array
   */
  public function getXmlfields() {
    return $this->xmlfields;
  }
  
  /**
   * Set errors.
   */
  private function setError($msg) {
    $this->error = TRUE;
    $this->err_msg = $msg;
  }

  /**
   * Return errorstatus.
   * @return boolean
   */
  public function getError() {
    return $this->error;
  }

  /**
   * Return errors messages (if any).
   * @return string
   */
  public function getErrorMsg() {
    return $this->err_msg;
  }


  /**
   * Add common parameters set for all requests to query.
   * agency, fromDate, toDate, pagination etc

   * @param stdClass $param
   * @return array
   */
  private function add_common_pars($param, &$ret) {
     // List (string).
    if (isset($param->requesterAgencyId->_value)) {
      $ret['pickupAgencyId'] = array($param->requesterAgencyId->_value);
    } else if (is_array($param->requesterAgencyId)) {
      foreach ($param->requesterAgencyId as $requesterAgency) {
        $ret['pickupAgencyId'][] = $requesterAgency->_value;
      }
    }
     // List (string).
    if (isset($param->requesterAgencyId->_value)) {
      $ret['requesterId'] = array($param->requesterAgencyId->_value);
    } else if (is_array($param->requesterAgencyId)) {
      foreach ($param->requesterAgencyId as $requesterAgency) {
        $ret['requesterId'][] = $requesterAgency->_value;
      }
    }
    // List (string).
    if (isset($param->responderAgencyId->_value)) {
      $ret['responderId'] = array($param->responderAgencyId->_value);
    } else if (is_array($param->responderAgencyId)) {
      foreach ($param->responderAgencyId as $responder) {
        $ret['responderId'][] = $responderAgencyId->_value;
      }
    }
    // String(s).
    // NB: Only sort by creationDate is defined by the XSD.
    if (isset($param->sortKey->_value)) {
      $ret['sortKey'] = 'creationDate';
      switch ($param->sortKey->_value) {
        case 'creationDateAscending':
          $ret['sortDirection'] = 'ASC';
          break;
        case 'creationDateDescending':
        default:
          $ret['sortDirection'] = 'DESC';
      }
    }
    // Number.
    if (isset($param->stepValue->_value)) {
      $step_value = (int) $param->stepValue->_value;
      $ret['stepValue'] = ($step_value) ? $step_value : 10;
    }
    // Number.
    if (isset($param->start->_value)) {
      $start = (int) $param->start->_value;
      $ret['start'] = ($start) ? $start : 0;
    }
    
    // Date (string). Format: "YYYY-MM-DD".
    // XSD: lastModification. ORS2: lastModificationDate.
    if (isset($param->lastModification->_value)) { // date (string)
      $ret['lastModificationDate'] = $param->lastModification->_value;
    }
    // Date (string).
    if (isset($param->fromDate->_value)) {
      $ret['fromDate'] = $param->fromDate->_value;
    }
    // Date (string).
    if (isset($param->toDate->_value)) { // date (string)
      $ret['toDate'] = $param->toDate->_value;
    }
    
    return $ret;
  }

  /**
   * Add one parameter to query array
   *
   * @param $key
   * @param $par
   * @param $params
   */
  private function add_string($key = null, $par = null, &$params) {
    if (empty($par) || empty($par->_value) || empty($key) || !is_array($params)) {
      return;
    }
    $params[$key] = $par->_value;
  }

  /**
   * Add one parameter to query array
   *
   * @param $key
   * @param $par
   * @param $params
   */
  private function add_list($key = null, $par = null, &$params) {
    $ret = array();
    if (empty($par) || empty($key) || !is_array($params)) {
      return;
    }
    if (!is_array($par)) {
      $par = array($par);
    }
    foreach ($par as $val) {
      $ret[] = $val->_value;
    }
    if (!empty($ret)) {
      $params[$key] = $ret;
    };
  }

  /** \brief
   *  return true if xs:boolean is so
   */
  private function xs_boolean($str) {
    return (strtolower($str) == 'true' || strtolower($str) == 'yes' || $str === TRUE);
  }

  /**\brief
   *
   */
  public function modify_some_data($key, $val) {
    switch ($key) {
      case 'placeOnHold':
        break;
      case 'expectedDelivery':
      case 'providerAnswerDate':
        return substr($val, 0, 10);
      case 'creationDate':
      case 'needBeforeDate':
        if ($p = strpos($val, 'T')) return substr($val, 0, $p);
        break;
      default:
        if (in_array($val, array('yes', 'Y'))) return 'true';
        if (in_array($val, array('no', 'N'))) return 'false';
    }
    return $val;
  }

}