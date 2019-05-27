<?php
  class ofoAaa extends aaa {

    private $openagencyAgencyList;
    private $authorizationError;

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
      // Check if groupId has authorization for agency.
      $this->user = $param->authentication->_value->userIdAut->_value;
      $this->group = $this->stripAgency($param->authentication->_value->groupIdAut->_value);
      $this->agency = $param->agency->_value;
      $orsAgency = new orsAgency($this->openagencyAgencyList);
      $branches = $orsAgency->fetch_library_list($this->group);
      if ($orsAgency->getError()) {
        $this->authorizationError = $orsAgency->getErrorMsg();
        return FALSE;
      }
      if (!in_array($this-agency, $branches)) {
        $this->authorizationError = 'agency not subset of groupid';
        return FALSE;
      }
      return TRUE;
    }

    /**
     * \brief
     * Return error string.
     *
     * @return string
     */
    public function getAuthorizationError() {
      return 'authorization error: ' . $this->authorizationError;
    }

    /**
     * \brief
     * return only digits, so something like DK-710100 returns 710100
     *
     * @param string
     * @return string
     *
     */
    private function stripAgency($id) {
      return preg_replace('/\D/', '', $id);
    }

  }
?>
