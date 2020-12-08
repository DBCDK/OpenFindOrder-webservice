<?php
  class ofoAaa extends aaa {

    private $authorizationError;
    private $vipCoreSettings;

    /**
     * \brief set $vipCoreSettings
     **/
    public function setOpenagencyList($setup) {
      $this->vipCoreSettings = $setup['vipcore'];
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
      $orsAgency = new orsAgency($this->vipCoreSettings);
      $branches = $orsAgency->fetch_library_list($this->group);
      if ($orsAgency->getError()) {
        self::local_verbose(WARNING, 'ofoAaa(' . __LINE__ . '):: orsAgency error: ' . $orsAgency->getErrorMsg());
        $this->authorizationError = 'authentication_error';
        return FALSE;
      }
      // Is ofo:agency part of authentication:groupIdAut branches?.
      if (!in_array($this->agency, $branches)) {
        // TO DO:update openfindorder.xsd -> add errorType.
        $this->authorizationError = 'authentication_error';
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
      return $this->authorizationError;
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

    /**
     * \brief
     * Log function. Same as aaa:local_verbose
     * NB: the aaa:local_verbose function ought to be 'protected', not 'private'.
     *
     * @param string $level
     * @param string $msg
     */
    private function local_verbose($level, $msg) {
      if (method_exists('VerboseJson', 'log')) {
        VerboseJson::log($level, $msg);
      }
      elseif (method_exists('verbose', 'log')) {
        verbose::log($level, $msg);
      }
    }

  }
