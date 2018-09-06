<?php

/**
 * Class orsAgency
 *
 * handle ors specific requests to openagency - in this case pickupagencylistrequest only
 */
class orsAgency{
  private $agency_url;
  private $curl;
  public function __construct($agency_url){
    $this->agency_url = $agency_url;
    $this->curl = new curl();
  }

  /**\brief Expands one or more library object to the corresponding branch objects
   *
   * @agencies; zero or more agencies
   * return; array of objects contaning all branch-ids in $agencies
   */
  public function expand_library($agencies) {
    $ret = $libs = $help = array();
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
      VerboseJson::log(ERROR, array('Error getting agency: ' => $url ,
          ' http: ' => $curl_status['http_code'] ,
          ' errno: ' => $curl_status['errno'] ,
          ' error: ' => $curl_status['error'])
      );
    }
    return $libs;
  }

  /**\brief
   * Fetch branches for the agency and check against requesterAgencyId or responderAgencyId
   * @param; request parameters as xml-object
   * return; FALSE if requesterAgencyId or responderAgencyId contains non-valid agency
   */
  public function check_agency_consistency(&$param) {
    $libs = $this->fetch_library_list($param->agency->_value);
    if ($libs) {
      if ($param->requesterAgencyId) {
        return $this->check_in_list($libs, $param->requesterAgencyId, 'requester_not_in_agency');
      }
      else if ($param->responderAgencyId) {
        return $this->check_in_list($libs, $param->responderAgencyId, 'responder_not_in_agency');
      }
    }
    return FALSE;
  }

  private function check_in_list($valid_list, $selected_list, $error_text) {
    if (is_array($selected_list)) {
      foreach ($selected_list as $sel) {
        if ($sel->_value && !in_array($this->strip_agency($sel->_value), $valid_list)) {
          return $error_text;
        }
      }
    }
    else {
      return in_array($this->strip_agency($selected_list->_value), $valid_list);
    }
    return TRUE;
  }

  /** \brief
   *  return only digits, so something like DK-710100 returns 710100
   */
  private function strip_agency($id) {
    return preg_replace('/\D/', '', $id);
  }
}