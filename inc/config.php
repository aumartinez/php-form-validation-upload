<?php

# Locations
define ("DS", DIRECTORY_SEPARATOR);
define ("ROOT", dirname(__FILE__));

# TEMP folder location, can change name if desired
define ("HTML", "html"); 
define ("INC", "inc");

#Required Form fields
define ("REQUIRED", 
  array(  "firstName", 
          "lastName", 
          "email", 
          "password", 
          "verify"));

?>