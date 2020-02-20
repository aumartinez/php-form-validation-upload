<?php

class Model {
  private $html;
  
  public function __construct() {
    
  }
  
  public function build_page() {
    $html = "";
    if (file_exists( HTML . DS . "form-temp.html")) {
      $html .= file_get_contents(HTML . DS . "form-temp.html");
      $html .= "\n";
    }
        
    return $html;
  }
  
}

?>