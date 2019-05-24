<?php

class ofoAuthentication {
  public static function authenticate(&$aaa, $function) {
    if ($aaa->has_right('netpunkt.dk', 500)) {
      return;
    }
    else {
      return 'authentication_error';
    }
  }
}

?>
