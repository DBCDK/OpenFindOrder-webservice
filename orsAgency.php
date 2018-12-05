<?php

/**
 * Class orsAgency
 *
 * handle ors specific requests to openagency - in this case pickupagencylistrequest only
 */
class orsAgency{
  private $agency_url;
  private $curl;
  private $error;
  private $err_msg;
  
  public function __construct($agency_url){
    $this->agency_url = $agency_url;
    $this->curl = new curl();
    $this->error = FALSE;
    $this->err_msg = NULL;
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
  public function fetch_library_list($agency) {
    $libs = array();
    $agency = $this->strip_agency($agency);
    $is_main_agency = FALSE;
    $url = sprintf($this->agency_url, $agency);

    $res = unserialize($this->curl->get($url));
    if ($res && $res->pickupAgencyListResponse->_value->library) {
      foreach ($res->pickupAgencyListResponse->_value->library[0]->_value->pickupAgency as $sublib) {
        if (
          !empty($sublib->_value->branchType->_value) &&
          $sublib->_value->branchType->_value === 'H' &&
          $sublib->_value->branchId->_value === $agency
        ) {
          $is_main_agency = TRUE;
        }
      }
      foreach ($res->pickupAgencyListResponse->_value->library[0]->_value->pickupAgency as $sublib) {
        if ($is_main_agency) {
          $libs[] = $sublib->_value->branchId->_value;
        }
        else {
          $libs[] = $agency;
        }
      }
    }
    else if ($res && $res->pickupAgencyListResponse->_value->error) {
      $agency_error = $res->pickupAgencyListResponse->_value->error->_value;
      switch ($agency_error) {
        case 'authentication_error':
        case 'no_userid_selected':
        case 'profile_not_found':
        case 'error_in_request':
          VerboseJson::log(ERROR, array('openAgency error' => $agency_error));
          // no break
        case 'agency_not_found':
        case 'no_agencies_found':
          $this->setError('cannot_find_agency');
          break;
        case 'service_unavailable':
        default:
          $this->setError('open find order service not available');
          VerboseJson::log(ERROR, array('openAgency error' => $agency_error));
          break;
      }
    }

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

    return $libs;
  }

  /**\brief
   * Fetch branches for the agency and check against requesterAgencyId or responderAgencyId
   * @param; request parameters as xml-object
   * return; FALSE if requesterAgencyId or responderAgencyId contains non-valid agency
   */
  public function check_agency_consistency(&$param) {
    $libs = $this->fetch_library_list($param->agency->_value);
    if ($this->getError()) {
      return FALSE;
    }
    if ($param->requesterAgencyId) {
      return $this->check_in_list($libs, $param->requesterAgencyId, 'requester_not_in_agency');
    }
    else if ($param->responderAgencyId) {
      return $this->check_in_list($libs, $param->responderAgencyId, 'responder_not_in_agency');
    }
    return FALSE;
  }

  private function check_in_list($valid_list, $selected_list, $error_text) {
    if (is_array($selected_list)) {
      foreach ($selected_list as $sel) {
        if ($sel->_value && !in_array($this->strip_agency($sel->_value), $valid_list)) {
          $this->setError($error_text);
          return FALSE;
        }
      }
    }
    else {
        if (!in_array($this->strip_agency($selected_list->_value), $valid_list)) {
          $this->setError($error_text);
          return FALSE;
        }
    }
    return TRUE;
  }

  /**
   * Set errors.
   */
  private function setError($msg) {
    $this->error = TRUE;
    $this->err_msg = $msg;
  }

  /**
   * Return error status.
   * @return boolean
   */
  public function getError() {
    return $this->error;
  }

  /**
   * Return errors messages (if any).
   * @return array
   */
  public function getErrorMsg() {
    return $this->err_msg;
  }


  /** \brief
   *  return only digits, so something like DK-710100 returns 710100
   */
  private function strip_agency($id) {
    return preg_replace('/\D/', '', $id);
  }
}