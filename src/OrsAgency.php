<?php

require_once 'vendor/autoload.php';

/**
 * Class orsAgency
 *
 * handle ors specific requests to openagency - in this case pickupagencylistrequest only
 */
class OrsAgency
{
  private $vip_core;
  private $VipCore;
  private $curl;
  private $error;
  private $err_msg;

  /**
   * orsAgency constructor.
   * @param $vip_core
   */
  public function __construct($vip_core){
    $this->vip_core = $vip_core;
    $this->VipCore = $this->initVipCore($this->vip_core);
    $this->curl = new curl();
    $this->error = FALSE;
    $this->err_msg = NULL;
  }

  /**\brief Expands one or more library object to the corresponding branch objects
   *
   * @param $agencies zero or more agencies
   * @return array of objects contaning all branch-ids in $agencies
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
   * @param $agency; agency to fetch pickupAgencyList for
   * @return array of branch-ids in agency
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function fetch_library_list($agency) {
    $libs = array();

    $agency = $this->strip_agency($agency);
    $is_main_agency = FALSE;

    $start = $this->time_since();
    try {
      $aList = $this->VipCore->pickupAgencyList([$agency], [], [], [], [], [], 'aktive');
      if (isset($aList->library) && isset($aList->library[0]) && isset($aList->library[0]->pickupAgency)) {
        foreach ($aList->library[0]->pickupAgency as $lib) {
          if (isset($lib->branchType) && $lib->branchType === 'H' && $lib->branchId === $agency) {
            $is_main_agency = TRUE;
          }
        }
        foreach ($aList->library[0]->pickupAgency as $lib) {
          $libs[] = $is_main_agency ? $lib->branchId : $agency;
        }
      }
      if (empty($libs)) {
        $this->setError('cannot_find_agency');
      }
    } catch (Exception $e) {
      $this->setError('open find order service not available: ' . $e->getMessage());
      VerboseJson::log(ERROR, 'VipCore::pickupAgencyList returned ' . $e->getMessage());
    }
    VerboseJson::log(TRACE, array(
            'system' => 'ORS2',
            'query' => $agency,
            'url' => $this->vip_core['url'],
            'total_time' => $this->time_since($start))
    );
    return $libs;
  }

  /**\brief
   * Fetch branches for the agency and check against requesterAgencyId or responderAgencyId
   * @param $param request parameters as xml-object
   * @return bool FALSE if requesterAgencyId or responderAgencyId contains non-valid agency
   * @throws \GuzzleHttp\Exception\GuzzleException
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

  /**
   * @param $valid_list
   * @param $selected_list
   * @param $error_text
   * @return bool
   */
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
   * @param $msg
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
   * @param $id
   * @return string|string[]|null
   */
  private function strip_agency($id) {
    return preg_replace('/\D/', '', $id);
  }

  /**
   * @param int $start
   * @return float|int|mixed
   */
  private function time_since($start = 0) {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec) - $start;
  }

  /** Initialize VipCore with memcached or redis
   *
   * @param $config
   * @return \DBC\VC\VipCore|null
   */
  private function initVipCore($config) {
    try {
      $cacheMiddleware = null;
      if ($config['memcached']) {
        $memcached = [['url' => $config['memcached']['url'], 'port' => $config['memcached']['port']]];
        $cacheMiddleware = \DBC\VC\CacheMiddleware\MemcachedCacheMiddleware::createCacheMiddleware(
            $memcached, $config['memcached']['expire'], 'OS'
        );
      }
      elseif ($config['redis']) {
        $redis = ['url' => $config['redis']['url'], 'port' => $config['redis']['port']];
        $cacheMiddleware = \DBC\VC\CacheMiddleware\PredisCacheMiddleware::createCacheMiddleware(
            $redis, $config['redis']['expire'], 'OS'
        );
      }
      else {
        VerboseJson::log(ERROR, 'No cache settings for vipCore');
      }
      return new \DBC\VC\VipCore(
          $config['url'], $config['timeout'], VerboseJson::$tracking_id, $cacheMiddleware);
    } catch (Error $e) {
      VerboseJson::log(FATAL, 'Error initializing vipCore: ' . $e->getMessage());
    }
    return null;
  }

}
