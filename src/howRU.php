<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_reporting(E_ALL & ~E_NOTICE);


require_once('OLS_class_lib/inifile_class.php');
require_once('OLS_class_lib/curl_class.php');
require_once('OLS_class_lib/xmlconvert_class.php');
require_once('OLS_class_lib/verbose_json_class.php');

require_once "xsdparse.php";
require_once "orsAgency.php";
require_once "orsClass.php";

$howru = new howRU('openfindorder.ini');


class howRU {

  protected $curl;
  protected $config;
  protected $error = FALSE;
  protected $error_msg = array();

  /**
   * howRU constructor.
   * @param inifile $config
   */
  public function __construct($inifile) {

    $this->curl = new curl();

    // Get inifile.
    if (!file_exists($inifile)) {
      $this->error = TRUE;
      $this->error_msg[] = 'Inifile not found: ' . $inifile;
    }
    $this->config = new inifile($inifile);

    $vip_core = $this->config->get_value('vipcore', 'setup');
    $xmlns = $this->config->get_value('xmlns', 'setup');
    define('THIS_NAMESPACE', $xmlns['ofo']);

    // Check openAgency.
    $this->curl->get($vip_core['url'] . 'domainlist');
    $status = $this->curl->get_status();
    if ($this->curl->has_error()) {
      $this->error = TRUE;
      $this->error_msg[] = 'openAgency connection failed.';
      $this->error_msg[] = $status['http_code'] . ': ' . $status['error'] . ' (' . $status['errno'] . ')';
    }

    $orsAgency = new orsAgency($vip_core);
    $list = $orsAgency->fetch_library_list('790900');
    if (!count($list)) {
      $this->error = TRUE;
      $this->error_msg[] = 'orsAgency->fetch_library_list(790900) request failed.';
      $this->error_msg[] = json_encode($list);
    }

    // Check ORS2.
    $ors2_url = $this->config->get_value('ors2_url', 'ORS');
    $this->curl->get($ors2_url . 'howru');
    $status = $this->curl->get_status();
    if ($this->curl->has_error()) {
      $this->error = TRUE;
      $this->error_msg[] = 'ORS2 connection failed.';
      $this->error_msg[] = $status['http_code'] . ': ' . $status['error'] . ' (' . $status['errno'] . ')';
    }

    $ors = new orsClass('findAllOrders', $this->config);
    $param['pickupAgencyId'] = array('100400');
    $param['requesterId']    = array('100400', '100401');
    $ors->setQueryArray($param);
    $ors->findOrders();
    if ($ors->getError()) {
      $this->error = TRUE;
      $this->error_msg[] = 'ORS2 request failed.';
      $this->error_msg[] = $ors->getErrorMsg();
    }

    // Get xml schema.
    $schemafile = $this->config->get_value('schema', 'setup');
    if (!file_exists($schemafile)) {
      $this->error = TRUE;
      $this->error_msg[] = 'XSD not found: ' . $schemafile;
    }
    $schema = new xml_schema();
    $schema->get_from_file($schemafile);

    // set xml-fields
    $this->xmlfields = $schema->get_sequence_array('receipt');
    if (empty($this->xmlfields)) {
      $this->error = TRUE;
      $this->error_msg[] = 'No xmlfields defined for receipt';
    }

    $this->xmlfields = $schema->get_sequence_array('order');
    if (empty($this->xmlfields)) {
      $this->error = TRUE;
      $this->error_msg[] = 'No xmlfields defined for order';
    }

    if ($this->error) {
      header('HTTP/1.0 503 Service Unavailable');
      die(implode("; \n", $this->error_msg));
    }

    die('Gr8');

  }

}

?>
