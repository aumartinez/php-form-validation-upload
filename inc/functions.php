<?php

function is_valid_state($state) {
  $statesArr = array(
                 "AL",
                 "CA",
                 "CO",
                 "FL",
                 "IL",
                 "NJ",
                 "NY",
                 "WI"
                 );

  if (in_array($state, $statesArr)) {
    return true;
  }
  else {
    return false;
  }
}//End function

function is_valid_zip($zip) {
  if(preg_match("/^[\d]+$/", $zip)) {
    return true;
  }
  else if (strlen($zip) == 5 || strlen($zip) == 9) {
    return true;
  }
  else {
    return false;
  }
}//End function

function random_str($length) {  
  return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

?>
