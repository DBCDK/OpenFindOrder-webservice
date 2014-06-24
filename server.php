<?php
/**
 *
 * This file is part of Open Library System.
 * Copyright © 2009, Dansk Bibliotekscenter a/s,
 * Tempovej 7-11, DK-2750 Ballerup, Denmark. CVR: 15149043
 *
 * Open Library System is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Open Library System is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Open Library System.  If not, see <http://www.gnu.org/licenses/>.
*/

define(DEBUG_x, FALSE);

require_once('OLS_class_lib/webServiceServer_class.php');
require_once('xsdparse.php');

class openFindOrder extends webServiceServer {
  //public $stat;

  /** \brief
      constructor; start watch; call parent's constructor
   */
  public function __construct() {
    parent::__construct('openfindorder.ini');

    define('THIS_NAMESPACE', $this->xmlns['ofo']);
    $this->watch->start('openfindorderWS');
    //  $this->stat = new stats();
  }

  /** \brief
      destructor: stop watch; log for statistics
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
    foreach ($methods as $key =>$value) {
      echo '    ' . $value . '<br/>';
    }

    echo '</pre>';
    die();
  }

  /** \brif
   *  The service request for orders which has been finished manually
   * @param; request parameters in request-xml object.
   */
  public function findManuallyFinishedIllOrders($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /** \brif
   *  The service request for open endUser orders
   * @param; request parameters in request-xml object.
   */
  public function findAllOpenEndUserOrders($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /** \brif
   *  The service request for orders on material not localized to the end user agency.
   * @param; request parameters in request-xml object.
   */
  public function findNonLocalizedEndUserOrders($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /** \brief
   *  The service request for orders on material localized to the end user agency.
   * @param; request parameters in request-xml object.
   */
  public function findLocalizedEndUserOrders($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /** \brief
   *  The service request for closed ill orders
   * @param; request parameters in request-xml object.
   */
  public function findClosedIllOrders($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /** \brief
   *  The service request for open ill orders
   * @param; request parameters in request-xml object.
   */
  public function findOpenIllOrders($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }


  /** \brief
   *  The service request for all ill orders
   * @param; request parameters in request-xml object.
   */
  public function findAllIllOrders($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /** \brief
   *  The service request for all non ill orders
   * @param; request parameters in request-xml object.
   */
  public function findAllNonIllOrders($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }


  /** \brief
   * The service request for all orders (optionally for a specific order system)
   * @param; request parameters in request-xml object.
   */
  public function findAllOrders($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /**\brief
   * The service request for a specific order (orderId)
   * @param; request parameters in request-xml object.
   */
  public function findSpecificOrder($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /**\brief
   * The service request for orders from a specific user (userId, userName or userMail)
   * @param; request parameters in request-xml object.
   */
  public function findOrdersFromUser($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /**\brief
   * The service request for orders from unknown users (general)
   * @param; request parameters in request-xml object.
   */
  public function findOrdersFromUnknownUser($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /**\brief
   * -- not yet defined
   *
   */
  public function findOrdersWithStatus($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);
/*
    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
*/
    return $this->send_error('placeholder - request not yet defined');
  }

  /**\brief
   * The service request for reason for auto forward (autoForwardReason)
   * @param; request parameters in request-xml object.
   */
  public function findOrdersWithAutoForwardReason($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /**\brief
   * The service request for automatically forwarded orders (general)
   * @param; request parameters in request-xml object.
   */
  public function findAutomatedOrders($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /**\brief
   * The service request for automatically forwarded orders (general)
   * @param; request parameters in request-xml object.
   */
  public function findOwnAutomatedOrders($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /**\brief
   * The service request for orders from a specific ill-cooperation (kvik, norfri or articleDirect)
   * @param; request parameters in request-xml object.
   */
  public function findOrderType($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /**\brief
   *  The service request for a biblographical search of orders
   * @param; request parameters in request-xml object
   */
  public function bibliographicSearch($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /**\brief
   * The service request for the receipt of an order
   * @param; request parameters in request-xml object
   */
  public function getReceipts($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error, 'getReceiptsResponse');

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->getReceiptsResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /**\brief
   * The service request for formatting an order receipt
   * @param; request parameters in request-xml object
   */
  public function formatReceipt($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error, 'getReceiptsResponse');

    $OFO_s = new OFO_solr($this->soap_action, $this->config); 

    if (!$receipt = json_decode($param->json->_value)) {
      return $this->send_error('Error decoding json string', 'getReceiptsResponse');
    }

    $order->resultPosition->_value = 1;
    $order->resultPosition->_namespace = THIS_NAMESPACE;
    foreach ($OFO_s->xmlfields as $key => $upper_key) {
      if ($receipt->$key) {
        $order->$key->_value = $OFO_s->modify_some_data($key, $receipt->$key);
        $order->$key->_namespace = THIS_NAMESPACE;
      }
    }
    $orders[0]->_value = $order;
    $orders[0]->_namespace = THIS_NAMESPACE;

    return $this->getReceiptsResponse($orders, '1', '');
  }

  /**\brief
   * The service request for non-automatatically forwarded orders (general)
   *  @param; request parameters in request-xml object
   */
  public function findNonAutomatedOrders($param) {
    if ($error = OFO_authentication::authenticate($this->aaa, __FUNCTION__))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows, $OFO_s->solr_query);
  }

  /* ------------------------------- private function --------------------------------------- */

  /**\brief
   * Generate response-object from given array of orders.
   * @orders; array of orders
   * return; orders as xml-objects
   */
  private function findOrderResponse($orders, $number_of_orders = 0, $debug_info = '') {
    $response->findOrdersResponse->_namespace = THIS_NAMESPACE;

    if ($orders === FALSE) {
      return $this->send_error('no orders found');
    }

    // empty result-set
    if (empty($orders))
      return $this->send_error('no orders found');

    $result = &$response->findOrdersResponse->_value->result;
    $result->_namespace = THIS_NAMESPACE;
    $result->_value->numberOfOrders->_namespace = THIS_NAMESPACE;
    $result->_value->numberOfOrders->_value = $number_of_orders;

    if ($orders->error) {
      $orders->error->_namespace = THIS_NAMESPACE;
      $response->findOrdersResponse->_value = $orders;
    } else
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
    $response->getReceiptsResponse->_namespace = THIS_NAMESPACE;

    if ($receipts === FALSE) {
      return $this->send_error('no orders found', 'getReceiptsResponse');
    }

    // empty result-set
    if (empty($receipts))
      return $this->send_error('no orders found', 'getReceiptsResponse');

    $result = &$response->getReceiptsResponse;
    $result->_namespace = THIS_NAMESPACE;
    $result->_value->numberOfReceipts->_namespace = THIS_NAMESPACE;
    $result->_value->numberOfReceipts->_value = $number_of_receipts;

    if ($receipts->error) {
      $receipts->error->_namespace = THIS_NAMESPACE;
      $result->_value = $receipts;
    } else
      $result->_value->receipt = $receipts;

    $result->_value->debugInfo->_value = $debug_info;
    return $response;
  }

  /** \brief
   * send errormessage as xml response-object
   */
  private function send_error($message, $response_tag = 'findOrdersResponse') {
    $response->$response_tag->_namespace = THIS_NAMESPACE;

    $error->_namespace = THIS_NAMESPACE;
    $error->_value = $message;
    $response->$response_tag->_value->error = $error;

    return $response;
  }
}

/*
 * MAIN
 */

$ws = new openFindOrder();

$ws->handle_request();

/**\brief
 * Class to handle connection to solr and correlation to xml-schema
 */
class OFO_solr {
  public static $error;
  public static $vip_connect;
  public static $numrows;
  public static $solr_query;
  public static $xmlfields = array();

  private $curl;
  private $action;
  private $fields = array();
  private $solr_url;
  private $agency_url;

  /**\brief
   * load setups and parse xsd for fields to return
   * @param; soap_action and config-object
   */
  public function __construct($action, $config) {
    self::$error = null;
    if (!$this->solr_url = $config->get_value('solr_order_uri', 'setup'))
      die('no url to order-SOLR in config-file');
    if (!$this->agency_url = $config->get_value('openagency_agency_list', 'setup'))
      die('no url to openAgency in config-file');

    // get xml schema
    $schemafile = $config->get_value('schema', 'setup');
    if (!file_exists($schemafile))
      die('xsd not found: ' . $schemafile);

    $schema = new xml_schema();
    $schema->get_from_file($schemafile);

    // set xml-fields
    $this->action = $action;
    if (in_array($this->action, array('getReceipts', 'formatReceipt')))
      $this->xmlfields = $schema->get_sequence_array('receipt');
    else
      $this->xmlfields = $schema->get_sequence_array('order');

    $this->curl = new curl();
    $this->curl->set_option(CURLOPT_TIMEOUT, 30);
  }

  public function __destruct() { }

  /**\brief
   * Get orders from database. 
   * @param; request parameters as xml-object
   * return; array of found orders
   */
  public function findOrders($param) {
    $consistency = $this->check_agency_consistency($param);
    if ($consistency === TRUE) {
      $this->solr_query = $this->set_solr_query($param);
      if ($res = $this->do_solr($param, $this->solr_query)) {
        $this->numrows = (int) $res['response']['numFound'];
        foreach ($res['response']['docs'] as &$doc) {
          $orders[] = $this->extract_fields($doc, ++$start);
        }
      }
      else {
        $orders->error->_value = 'no orders found';
      }
    }
    else {
      $orders->error->_value = $consistency;
    }
    return $orders;
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

  /* ------------------------------- private function --------------------------------------- */

  /**\brief Expands one or more library object to the corresponding branch objects
   * 
   * @agencies; zero or more agencies
   * return; array of objects contaning all branch-ids in $agencies
   */
  private function expand_library($agencies) {
    $ret = $libs = array();
    if ($agencies) {
      if (!is_array($agencies)) {
        $help[]->_value = $agencies->_value;
        $agencies = $help;
      }
      foreach ($agencies as $agency) {
        $libs = array_merge($libs, $this->fetch_library_list($agency->_value));
      }
      foreach (array_unique($libs) as $lib) {
        $ret[]->_value = $lib;
      }
    }
    return $ret;
  }

  /**\brief Expands one or more libraries using openagency::pickupAgencyList
   * 
   * @agency; agency to fetch pickupAgencyList for
   * return; array of branch-ids in agency
   */
  private function fetch_library_list($agency) {
    $libs = array();
    $url = sprintf($this->agency_url, $this->strip_agency($agency));
    $res = unserialize($this->curl->get($url));
    if ($res && $res->pickupAgencyListResponse->_value->library) {
      foreach ($res->pickupAgencyListResponse->_value->library[0]->_value->pickupAgency as $sublib) {
        $libs[] = $sublib->_value->branchId->_value;
      }
    }
    else {
      $curl_status = $this->curl->get_status();
      verbose::log(ERROR, 'Error getting agency: ' . $url . 
                          ' http: ' . $curl_status['http_code'] . 
                          ' errno: ' . $curl_status['errno'] . 
                          ' error: ' . $curl_status['error']);
    }
    return $libs;
  }

  /**\brief
   * Fetch branches for the agency and check against requesterAgencyId or responderAgencyId 
   * @param; request parameters as xml-object
   * return; FALSE if requesterAgencyId or responderAgencyId contains non-valid agency
   */
  private function check_agency_consistency(&$param) {
    $libs = $this->fetch_library_list($param->agency->_value);
    if ($libs) {
      if ($param->requesterAgencyId) 
        return $this->check_in_list($libs, $param->requesterAgencyId, 'requester_not_in_agency');
      else
        return $this->check_in_list($libs, $param->responderAgencyId, 'responder_not_in_agency');
    }
  }

  private function check_in_list($valid_list, $selected_list, $error_text) {
    if (is_array($selected_list)) {
      foreach ($selected_list as $sel) {
        if ($sel->_value && !in_array($this->strip_agency($sel->_value), $valid_list))
          return $error_text;
      }
    }
    else {
      return in_array($this->strip_agency($selected_list->_value), $valid_list);
    }
    return TRUE;
  }

  /**\brief
   * Handle one order.
   * @data; a row of data from solr
   * @resultPosition; rownumber of result
   * return; one order as xml-object
   */
  private function extract_fields(&$data, $resultPosition) {
    $ret->_namespace = THIS_NAMESPACE;

    $ret->_value->resultPosition->_value = $resultPosition;;
    $ret->_value->resultPosition->_namespace = THIS_NAMESPACE;

    // column-names from database MUST match xml-fields for this loop to work
    // new loop to ensure roworder as defined in xml-schema
    foreach ($this->xmlfields as $key =>$upper_key) {
      $values = $data[strtolower($key)];
      if (!is_array($values)) {
        $values = array($values);
      }
      foreach ($values as $value) {
        if (self::valid_data($key, $value)) {
          $tmp->_value = self::modify_some_data($key, $value);
          $tmp->_namespace = THIS_NAMESPACE;
          if ($key == 'pid') {
            $ret->_value->{$key}[] = $tmp;
          } 
          else {
            $ret->_value->$key = $tmp;
          } 
          unset($tmp);
        }
      }
    }
    return $ret;
  }

  private function valid_data($key, $val) {
    return ($val 
         && $val != '0001-01-01'
         && $val != 'uninitialized'
         && ($key != 'pidOfPrimaryObject' || substr($val, 0, 1) != 'D'));
  }

  private function do_solr($param, $solr_query) {
    if (!$start = $param->start->_value)
      $start = 1;
    if (!$rows = $param->stepValue->_value)
      $rows = 10;
    if ($param->sortKey->_value == 'creationDateAscending') {
      $sort = '&sort=creationdate%20asc';
    } 
    elseif ($param->sortKey->_value == 'creationDateDescending') {
      $sort = '&sort=creationdate%20desc'; 
    }
    $url = $this->solr_url . 
             'q=' . urlencode($solr_query) . 
             $sort .
             '&start=' . ($start - 1) . 
             '&rows=' . $rows .
             '&defType=edismax&debugQuery=on&wt=phps';
    verbose::log(DEBUG, 'Trying in solr with: ' . $url);
    $solr_result = $this->curl->get($url);
    if (empty($solr_result)) {
      $curl_status = $this->curl->get_status();
      verbose::log(ERROR, 'Error getting solr: ' . $url . 
                          ' http: ' . $curl_status['http_code'] . 
                          ' errno: ' . $curl_status['errno'] . 
                          ' error: ' . $curl_status['error']);
      return FALSE;
    }
    else {
      return unserialize($solr_result);
    }
  }

  /** \brief build the solr-search corresponding to the user-request
   *         use the solr edismax search handler syntax
   *         OR searches has to be field:(a OR b) and not (field:a OR field:b)
   *  return a solr-query string
   */
  private function set_solr_query($param) {
    $requester = $this->expand_library($param->requesterAgencyId);
    $responder = $this->expand_library($param->responderAgencyId);
    switch ($this->action) {
      case "findManuallyFinishedIllOrders":
        $ret = 'ordertype:inter_library_request';
        if (isset($param->requesterOrderState->_value)) {
          $ret .= 'AND requesterorderstate:' . $param->requesterOrderState->_value;
        } 
        elseif (isset($param->providerOrderState->_value)) {
          $ret .= 'AND providerorderstate:' . $param->providerOrderState->_value;
        }
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findAllOpenEndUserOrders':
        $ret = 'closed:N AND ordertype:(enduser_request OR enduser_illrequest)';
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findAllOrders':
        $ret = '';
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findAllIllOrders':
        $ret = 'ordertype:inter_library_request';
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findAllNonIllOrders':
        $ret = 'ordertype:(enduser_request OR enduser_illrequest)';
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findSpecificOrder':
        if ($param->orderType->_value == 'enduser_order') {
          $ret = 'ordertype:(enduser_request OR enduser_illrequest)';
        }
        elseif ($param->orderType->_value == 'inter_library_order') {
          $ret = 'ordertype:inter_library_request';
        }
        $ret = $this->add_one_par($param->orderId, 'orderid', $ret);
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findOrdersFromUser':
        if ($param->orderType->_value == 'enduser_order') {
          $ret = 'ordertype:(enduser_request OR enduser_illrequest)';
        }
        elseif ($param->orderType->_value == 'inter_library_order') {
          $ret = 'ordertype:inter_library_request';
        }
        $ret = $this->add_one_par($param->userId, 'userid', $ret);
        $ret = $this->add_one_par($param->userMail, 'usermail', $ret);
        $ret = $this->add_one_par($param->userName, 'username', $ret);
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
// Spooky ... this OR-part below has to be the last and no () around it ????
// apparently the edismax searchHandler parse (a OR b OR c) as some list where all members should be present
// Users are therefore encouraged/recommended to use userId, userMail and userName instead of userFreeText
        if ($uft = $param->userFreeText->_value) {
          $ret .= ($ret ? ' AND ' : '') . 'userid:"' . $uft . '" OR usermail:"' . $uft . '" OR username:"' . $uft . '"';
        }
        break;
      case 'findOrdersFromUnknownUser':
        $ret = 'useridauthenticated:no';
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'bibliographicSearch':
        if ($param->orderType->_value == 'enduser_order') {
          $ret = 'ordertype:(enduser_request OR enduser_illrequest)';
        }
        elseif ($param->orderType->_value == 'inter_library_order') {
          $ret = 'ordertype:inter_library_request';
        }
        $ret = $this->add_one_par($param->author, 'author', $ret);
        if ($param->bibliographicFreeText) {
// also spooky here ... only titles are found, swap author and title and only authors are found
// 2do?: split input field into author and title specific field
          $ret = $this->add_one_par($param->bibliographicFreeText, 'author', $ret, 'AND (');
          $ret = $this->add_one_par($param->bibliographicFreeText, 'title', $ret, 'OR');
        }
        $ret = $this->add_one_par($param->title, 'title', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findOrdersWithStatus':
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findOrdersWithAutoForwardReason':
        $ret = $this->add_one_par($param->autoForwardReason, 'autoforwardreason', $ret);
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findAutomatedOrders':
        $order_type = ($param->orderType->_value ? $param->orderType->_value : 'inter_library_request');
        $ret = 'ordertype:' . $order_type . ' AND autoforwardresult:automated';
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findOwnAutomatedOrders':
        $order_type = ($param->orderType->_value ? $param->orderType->_value : 'inter_library_request');
        $ret = 'ordertype:' . $order_type . ' AND autoforwardresult:yes';
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findNonAutomatedOrders':
        $ret = 'autoforwardreason:non_automated';
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findOrderType':
        $ret = $this->add_one_par($param->articleDirect, 'articledirect', $ret);
        $ret = $this->add_one_par($param->kvik, 'kvik', $ret);
        $ret = $this->add_one_par($param->norfri, 'norfri', $ret);
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'getReceipts':
        $ret = $this->add_one_par($param->orderId, 'orderid', $ret);
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findOpenIllOrders':
        $ret = 'ordertype:inter_library_request';
        $ret .= ' AND -provideranswer:*';
        if ($param->requesterAgencyId) {
          $ret .= ' AND -requesterorderstate:finished';
        }
        if ($param->responderAgencyId) {
          $ret .= ' AND -providerorderstate:finished';
        }
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findClosedIllOrders':
        $ret = 'ordertype:inter_library_request';
        if ($param->orderStatus->_value == 'shipped') {
          $ret .= ' AND isshipped:Y';
        }
        elseif ($param->orderStatus->_value) {
          $ret .= ' AND provideranswer:' . $param->orderStatus->_value;
        }
        else {
          $ret .= ' AND provideranswer:*';
        }
        if ($param->requesterAgencyId) {
          $ret .= ' AND -requesterorderstate:finished';
        }
        if ($param->responderAgencyId) {
          $ret .= ' AND -providerorderstate:finished';
        }
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findLocalizedEndUserOrders':
        $ret = 'ordertype:enduser_request';
        $ret .= ' AND closed:' . ($this->xs_boolean($param->closed->_value) ? 'Y' : 'N');
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findNonLocalizedEndUserOrders':
        $ret = 'ordertype:enduser_illrequest';
        $ret .= ' AND closed:' . ($this->xs_boolean($param->closed->_value) ? 'Y' : 'N');
        $ret = $this->add_one_par($requester, 'requesterid', $ret);
        $ret = $this->add_one_par($responder, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      default:
        die('no or wrong action');
        break;
    }
    if (! $ret) {
      self::$error = 'query could not be set for ' . $this->action;
      return FALSE;
    }

    return $ret;
  }

  /**\brief
   * handles general parms: agency, orderSystem, fromDate, toDate
   */
  private function add_common_pars($param, $ret = '') {
    // solr intervals as
    // creationdate:[2012-03-06T00:00:00Z TO 2076-03-06T23:59:59Z]
    // creationdate:[2012-03-06T00:00:00Z TO *]
    // creationdate:[* TO 2012-03-06T00:00:00Z]
    $ret = $this->add_one_par($param->requesterAgencyId, 'pickupagencyid', $ret);
// search in responderid: is already done ... which is correct ???
    $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
    if ($param->fromDate->_value || $param->toDate->_value) {
      $from = $to = '*';
      if ($param->fromDate->_value) {
        $from = $this->solr_date($param->fromDate->_value);
      }
      if ($param->toDate->_value) {
        $to = $this->solr_date($param->toDate->_value . '+23 hours 59 minutes 59 seconds');
      }
      if ($ret) $ret .= ' AND ';
      $ret .= 'creationdate:[' . $from . ' TO ' . $to . ']';
    }
    if ($param->lastModification->_value) {
      $from = $this->solr_date($param->lastModification->_value);
      if ($ret) $ret .= ' AND ';
      $ret .= 'lastmodification:[' . $from . ' TO *]';
    }

    return $ret;
  }

  private function solr_date($some_time) {
    date_default_timezone_set('UTC');
    return date('Y-m-d\TH:i:s\Z', strtotime($some_time));

  }

  /**\brief
   * handles one parameter 
   */
  private function add_one_par($par, $search_field, $ret = '', $op = 'AND') {
    if (is_array($par)) {
      foreach ($par as $val) {
        if ($val->_value)
          $help .= ($help ? ' OR ' : '') . $this->normalize_query_term($val->_value, $search_field);
      }
      if ($help)
        $ret .= ($ret ? " $op " : '') . $search_field . ':(' . $help . ')';
    }
    else {
      if ($par->_value)
        $ret .= ($ret ? " $op " : '') . $search_field . ':' . $this->normalize_query_term($par->_value, $search_field);
    }
    return $ret;
  }

  /** \brief
   *  return normalized agency for selected fields, escape solr meta-chars and use proximity for multi term queries
   */
  private function normalize_query_term($query_term, $field) {
    static $solr_e_from = array('+', '-', ':', '!');
    static $solr_e_to = array();
    if (empty($solr_e_to)) {
      foreach ($solr_e_from as $ch) $solr_e_to[] = '\\' . $ch;
    }
    if ($field == 'requesterid' || $field == 'responderid')
      $query_term = $this->strip_agency($query_term);
    elseif (strpos($query_term, ' ')) {
      $query_term = '"' . str_replace('"', '', $query_term) . '"~3';
    }
    return str_replace($solr_e_from, $solr_e_to, $query_term);
  }

  /** \brief
   *  return true if xs:boolean is so
   */
  private function xs_boolean($str) {
    return (strtolower($str) == 'true' || $str == 1);
  }

  /** \brief
   *  return only digits, so something like DK-710100 returns 710100
   */
  private function strip_agency($id) {
    return preg_replace('/\D/', '', $id);
  }

}

class OFO_authentication {
  public static function authenticate(&$aaa, $function) {
    if ($aaa->has_right('netpunkt.dk', 500))
      return;
    else 
      return 'authentication_error';
  }
}
