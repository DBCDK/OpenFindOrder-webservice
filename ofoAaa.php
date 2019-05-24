<?php
  class ofoAaa extends aaa {

    private $openagencyAgencyList;
    private $authorizationError;

    /**
     * \brief gets user id
     *
     * @returns string
     **/
    public function getUser() {
      return $this->user;
    }

    /**
     * \brief gets user group id
     *
     * @returns string
     **/
    public function getGroup() {
      return $this->group;
    }

    /**
     * \brief set $openagencyAgencyList
     **/
    public function setOpenagencyList($setup) {
      $this->openagencyAgencyList = $setup['openagency_agency_list'];
    }

    /**
     * \brief gets authorization
     *
     * @param stdClass $param
     * @returns boolean
     **/
    public function authorization($param) {
      // If not, check if groupId has authorization for agency.
      $this->user = $param->authentication->_value->userIdAut->_value;
      $this->group = $this->strip_agency($param->authentication->_value->groupIdAut->_value);
      $this->agency = $param->agency->_value;
      $orsAgency = new orsAgency($this->openagencyAgencyList);
      $branches = $orsAgency->fetch_library_list($this->agency);
      if ($orsAgency->getError()) {
        $this->authorizationError = $orsAgency->getErrorMsg();
        return FALSE;
      }
      if (!in_array($this->group, $branches)) {
        $this->authorizationError = 'groupid not in agency';
        return FALSE;
      }
      return TRUE;
    }

    /**\brief
     */
    public function getAuthorizationError() {
      return 'authorization error: ' . $this->authorizationError;
    }

    /** \brief
     *  return only digits, so something like DK-710100 returns 710100
     */
    private function strip_agency($id) {
      return preg_replace('/\D/', '', $id);
    }

  }
?>
