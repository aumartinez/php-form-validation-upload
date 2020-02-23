<?php

class Model {  
  protected $view;
  
  public function __construct() {
    $this->view = new View();    
  }
  
  public function build_page() {
    $html_src = "";
    if (file_exists( HTML . DS . "form.html")) {
      $html_src .= file_get_contents(HTML . DS . "form.html");
      $html_src .= "\n";
    }
    
    $error = "ERROR";
    $error_mess = "";
    
    if (isset($_SESSION["error"]) && isset($_SESSION["submitForm"])) {
      unset($_SESSION["submitForm"]);
      
      $error_mess = "\n";
      $error_mess .= "Errors found.";
      $error_mess .= "<br />\n";
        
        foreach ($_SESSION["error"] as $error) {
          $error_mess .= $error."<br />\n";
        }
    }
    
    $this->view->add_locale($error, $error_mess);    
    $html = $this->view->replace_locales($html_src);
        
    return $html;
  }
  
}

?>