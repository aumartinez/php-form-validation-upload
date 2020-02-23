<?php

class Model {  
  protected $view;
  
  public function __construct() {
    $this->view = new View();    
  }
  
  public function build_page($page) {
    $html_src = "";
    if (file_exists( HTML . DS . $page . ".html")) {
      $html_src .= file_get_contents(HTML . DS . $page . ".html");
      $html_src .= "\n";
    }
    
    # If errors are returned    
    $error_key = "ERROR";
    $error_mess = "";
            
    if (isset($_SESSION["error"]) && isset($_SESSION["submitForm"])) {
      unset($_SESSION["submitForm"]);
      
      $error_mess = "\n";
      $error_mess .= "Errors found.";
      $error_mess .= "<br />\n";
      $error_mess .= "<br />\n";
        
        foreach ($_SESSION["error"] as $error) {
          $error_mess .= $error . "<br />\n";
        }
    }
    
    # If form is successful
    $success_key = "SUCCESS";
    $success_mess = "";
    
    $this->view->add_locale($error_key, $error_mess);
    $this->view->add_locale($success_key, $success_mess);
    $html = $this->view->replace_locales($html_src);
        
    return $html;
  }
  
}

?>