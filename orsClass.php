<?php
/**
 * @file
 * class to handle request for orders.
 * ors -service url:
 * http://ors-maintenance.iscrum-staging.svc.cloud.dbc.dk:8080/api/
 *
 * api documentation:
 * http://wiki.dbc.dk/bin/view/Udvikler/Ors2MaintenanceApi
 *
 *
 */

// include class to look up pickupagencies
require_once "orsAgency.php";

class orsClass {
  private $config;
  private $action;
  private $curl;
  private $xmlfields;

  /**
   * orsClass constructor.
   * @param string $action
   * @param inifile $config
   */
  public function __construct($action, $config) {
    $this->config = $config;
    $this->action = $action;
    $this->curl = new curl();
    $this->curl->set_option(CURLOPT_TIMEOUT, 30);
  }

  /**
   * @param $param
   */
  public function findOrders($param) {
    $orsAgency = new orsAgency($this->config->get_value('openagency_agency_list', 'setup'));
    $requester = $orsAgency->expand_library($param->requesterAgencyId);
    $responder = $orsAgency->expand_library($param->responderAgencyId);

    $ret = array();
    switch ($this->action) {
      case "findManuallyFinishedIllOrders":
        $ret = array(
          'ordertype'=>'inter_library_request'
        );
        if (isset($param->requesterOrderState->_value)) {
          $ret['requesterorderstate'] = $param->requesterOrderState->_value;
        }
        elseif (isset($param->providerOrderState->_value)) {
          $ret['providerorderstate'] =  $param->providerOrderState->_value;
        }
        $common = $this->add_common_pars($param);
        $ret = array_merge($ret, $common);

        $ret['requesterid'] = $this->add_one_par($requester);
        $ret['responderid'] = $this->add_one_par($responder);
        break;
      case 'findAllOpenEndUserOrders':
        $ret = array(
          'closed' => 'N',
          'ordertype' => array('enduser_request', 'enduser_illrequest')
        );

        $ret['requesterid'] = $this->add_one_par($requester);
        $ret['responderid'] = $this->add_one_par($responder);
        $common = $this->add_common_pars($param);
        $ret = array_merge($ret, $common);
        break;
      default:
        break;
    }

    // TODO somehow switch between post and get here depending on the action
    return $this->do_post_request($ret);
  }


  /**
   * Do post request for orders.
   * @param array $request
   *
   * TODO some orderrequests are GET, but to find orders we do POST. see documentation for details
   *
   * TODO we need a similar method for GET like: private function do_get_request(array $request) {
   *
   *
   */
  private function do_post_request(array $request) {
    $json = json_encode($request);
    $url = $this->config->get_value('maintenance_url', 'ORS');
    // this is for the (find)order api
    $url .= 'orders';

    // initialize curl for post request
    $this->curl->set_post($json);
    $this->curl->set_url($url);
    $this->curl->set_option(CURLOPT_RETURNTRANSFER, TRUE);
    $this->curl->set_option(CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $result = $this->curl->get();
    // TODO errorhandling

    return $result;
  }

  /**
   * Add common parameters set for all requests to query.
   * agency, fromDate, toDate, pagination etc

   * @param stdClass $param
   * @return array
   */
  private function add_common_pars($param) {
    $ret = array();
    $ret['pickupagencyid'] = $param->requesterAgencyId->_value;
    $ret['responderid'] = $param->responderAgencyId->_value;

    // TODO handle date intervals (fromdate, todate)
    // TODO handle pagination (start, stepvalue)
    return $ret;
  }

  /**
   * Add one parameter to query array
   *
   * @param $par
   * @return array
   */
  private function add_one_par($par) {
    $ret = array();
    if (!is_array($par)) {
      $par = array($par);
    }
    foreach ($par as $val) {
      $ret[] = $val->_value;
    }

    return $ret;
  }

  /**
   * This one is for parsing the xsd describing the openfindorder service.
   * if schema validation is on we need to deliver elements in correct order
   *
   * ... but do we need it now ??
   *
   */
  private function setSchema() {
    // get xml schema
    $schemafile = $this->config->get_value('schema', 'setup');
    if (!file_exists($schemafile)) {
      die('xsd not found: ' . $schemafile);
    }

    $schema = new xml_schema();
    $schema->get_from_file($schemafile);

    // set xml-fields
    if (in_array($this->action, array('getReceipts', 'formatReceipt'))) {
      $this->xmlfields = $schema->get_sequence_array('receipt');
    }
    else {
      $this->xmlfields = $schema->get_sequence_array('order');
    }
  }
}