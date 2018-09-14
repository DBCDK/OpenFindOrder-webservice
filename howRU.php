<?php

require_once('OLS_class_lib/inifile_class.php');
require_once('OLS_class_lib/curl_class.php');
require_once('OLS_class_lib/aaa_class.php');

require_once "xsdparse.php";
require_once "orsAgency.php";
require_once "orsClass.php";

$howru = new howRU('openfindorder.ini');


class howRU {

  protected $config;
  protected $error = FALSE;
  protected $error_msg = array();
  
  /**
   * howRU constructor.
   * @param inifile $config
   */
  public function __construct($inifile) {

    // Get inifile.
    if (!file_exists($inifile)) {
      $this->error = TRUE;
      $this->error_msg[] = 'Inifile not found: ' . $inifile;
    }
    $this->config = new inifile($inifile);
    
    // Check openAgency.
    $openagency = $this->config->get_value('openagency_agency_list', 'setup');
    $orsAgency = new orsAgency($openagency);
    $orsAgency->fetch_library_list('100400');
    if ($orsAgency->getError()) {
      $this->error = TRUE;
      $this->error_msg[] = $orsAgency->getErrorMsg();
    }
    
    // Check ORS2.
    $ors = new orsClass('findAllOrders', $this->config);
    $param['pickupAgencyId'] = array('100400');
    $param['requesterId']    = array('100400', '100401');
    $ors->setQueryArray($param);
    $ors->findOrders();
    if ($ors->getError()) {
      $this->error = TRUE;
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
      die(implode('; ', $this->error_msg));
    }
    
    die('Gr8');
    
  }

}

?>