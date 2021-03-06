<?php
# Locations
define ("DS", DIRECTORY_SEPARATOR);
define ("ROOT", dirname(__FILE__));

require_once ("config.php");
require_once ("functions.php");
require_once ("dbkey.php");
require_once ("view.php");
require_once ("model.php");

class Validateform extends Model {  
  protected $required;
  protected $model;
  
  protected $sanitized = array();
  protected $imgs = array();
  
  public function __construct() {
    parent::__construct();
    
    session_start();
    
    if (!isset($_POST["submitForm"])) {
      $this->redirect();
    }
    
    $_SESSION["submitForm"] = true;
    
    if (isset($_SESSION["error"])) {
      unset($_SESSION["error"]);
    }
    
    $_SESSION["error"] = array();
    
  } # End construct
  
  public function required() {
        
    $required = REQUIRED;
    
    # Check required
    foreach ($required as $value) {
      if (!isset($_POST[$value]) || $_POST[$value] == "") {
        $_SESSION["error"][] = $value." is required";
      }
    }
    
    $this->error_check();
  }
  
  public function names() {
    # Validate firstName and lastName
    if (!preg_match('/^[\w .]+$/', $_POST["firstName"])) {
      $_SESSION["error"][] = "First name must be letter and numbers only";
    }
    
    if (!preg_match("/^[\w .]+$/", $_POST["lastName"])) {
      $_SESSION["error"][] = "Last name must be letter and numbers only";
    }
    
    $this->error_check();
  }
  
  public function email() {
    # Validate email
    if (isset($_POST["email"]) && $_POST["email"] != "") {
      if(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"][] = "Email is invalid";
      }
    }
    
    $this->error_check();
  }
  
  public function password_match() {
    # Validate password match
    if (isset($_POST["password"]) && isset($_POST["verify"])) {
      if ($_POST["password"] != "" && $_POST["verify"] != "") {
        if ($_POST["password"] != $_POST["verify"]) {
          $_SESSION["error"][] = "Passwords don't match";
        }
        else if (strlen($_POST["password"]) < 6 || strlen($_POST["verify"]) < 6) {
          $_SESSION["error"][] = "Passwords should be more than 6 characters";
        }
      }
    }
    
    $this->error_check();
  }
  
  public function additional() {
    # Additional validations
    if (isset($_POST["state"]) && strlen($_POST["state"]) > 0) {
      if (!is_valid_state($_POST["state"])) {
        $_SESSION["error"][] = "Please choose a valid state.";
      }
    }

    if (isset($_POST["zip"]) && $_POST["zip"] != "") {
      if (!is_valid_zip($_POST["zip"])) {
        $_SESSION["error"][] = "ZIP code error.";
      }
    }

    if (isset($_POST["phone"]) && $_POST["phone"] != "") {
      if (!preg_match("/^[\d]+$/", $_POST["phone"])) {
        $_SESSION["error"][] = "Phone number should be only digits.";
      }
      else if (strlen($_POST["phone"] < 10)) {
        $_SESSION["error"][] = "Phone number must be at least 10 digits.";
      }
      
      if (!isset($_POST["phonetype"]) || $_POST["phonetype"] == "") {
        $_SESSION["error"][] = "Please choose a phone number type.";
      }
      else {
        $validPhoneTypes = array(
        "work",
        "home"
        );
        
        if (!in_array($_POST["phonetype"], $validPhoneTypes)) {
          $_SESSION["error"][] = "Please choose a valid phone number type.";
        }
      }
    }
    
    $this->error_check();
  }
  
  protected function error_check() {
    if (count($_SESSION["error"]) > 0) {
      error_log("Error validating form");
      $this->redirect();
    }
  }
  
  # Sanitize user input
  public function sanitize() {
    $this->sanitized = array();
    
    foreach ($_POST as $key => $value) {
      $this->sanitized[$key] = $this->open_link()->real_escape_string($value);
    }
        
    return $this->sanitized;    
  }
  
  # Upload function
  public function upload() {
    
    if(!isset($_POST["submitForm"])) {
      $_SESSION["error"][] = "nothing";
      return;
    }
    
    # target dir and file type
    $target_dir = dirname(dirname(__FILE__)) . DS . "uploads" . DS;
    $allowed_types = array("jpg", "png", "jpeg", "gif");
    $this->imgs = array();
    
    # 2MB file size limit
    $max_size = 2 * 1024 * 1024;
    
    # Multiple images upload limit
    $limit = 3;
    
    if (empty(array_filter($_FILES["images"]["name"]))) {
      $this->error_check();
      return;
    }
    
    # Check for max file upload limit
    if (count($_FILES["images"]["name"]) > $limit) {
      $_SESSION["error"][] = "Please upload only " . $limit . " images";
      $this->error_check();
    }
    
    foreach ($_FILES["images"]["tmp_name"] as $key => $value) {
        
      $file_tempname = $_FILES["images"]["tmp_name"][$key];
      $file_name = $_FILES["images"]["name"][$key];
      $file_size = $_FILES["images"]["size"][$key];
      $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
      
      $file_path = $target_dir . $file_name;
      
      # File type check
      if (!in_array($file_ext, $allowed_types)) {
        $_SESSION["error"][] = "Invalid image file type: ." . $file_ext;
        $this->error_check();
      }
      
      # File size check
      if ($file_size > $max_size) {
        $_SESSION["error"][] = "Image is larger than 2MB";
        $this->error_check();
      }
      
      if (file_exists($file_path)) {
        $file_path = $target_dir . time() . $file_name;
      }
      
      if (move_uploaded_file($file_tempname, $file_path)) {
        array_push($this->imgs, $file_name);        
      }
      else {
        $_SESSION["error"][] = "Error uploading " . $file_name;
      }
      
    }
    
    $this->error_check();
    return $this->imgs;
  }
    
  # Register user entry
  public function register() {
    $sql = "";
    
    if(file_exists(dirname(ROOT) . DS . SQL . DS . "createtable.sql")) {
      $sql = file_get_contents(dirname(ROOT) . DS . SQL . DS . "createtable.sql");
      $this->set_multyquery($sql);
    }
    
    # Check if user already exists
    $email = $this->sanitized["email"];
    
    $sql = "SELECT id 
          FROM customers
          WHERE email = '{$email}'";
    
    # If user exists return to landing form
    if(count($this->get_query($sql)) == 1) {
      $_SESSION["error"][] = "A user with that e-mail address already exists";
      $this->redirect();
    }
    
    $firstName = $this->sanitized["firstName"];
    $lastName = $this->sanitized["lastName"];
    
    # SHA-512 salt
    $salt = "\$6\$rounds=5000\$".random_str(8)."\$";    
    $password = $this->sanitized["password"];
    $crypted = substr(crypt($password, $salt), strlen($salt));
    
    # Insert images names
    $images = implode(",", $this->imgs);
    
    # Insert all data
    $street = $this->sanitized["address"];
    $city = $this->sanitized["city"];
    $state = $this->sanitized["state"];
    $zip = $this->sanitized["zip"];
    $phone = $this->sanitized["phone"];
    $phonetype = $this->sanitized["phonetype"];

    $sql = "INSERT INTO customers (
          email,
          create_date,
          password,
          salt,
          first_name,
          last_name,
          images,
          street,
          city,
          state,
          zip,
          phone,
          phone_type
          )
          VALUES(
          '{$email}',
          NOW(),
          '{$crypted}',
          '{$salt}',
          '{$firstName}',
          '{$lastName}',
          '{$images}',
          '{$street}',
          '{$city}',
          '{$state}',
          '{$zip}',
          '{$phone}',
          '{$phonetype}'
          )";
    
    $this->set_query($sql);
    
    unset($_SESSION["submitForm"]);
    unset($_SESSION["error"]);
    $_SESSION["success"] = true; 
    $_SESSION["user"] = $this->sanitized["email"];
    $this->redirect();
  }
  
  private function redirect() {
    header("Location: ../form.php");
    exit();
  }   
}

$validate = new Validateform();
$validate->required();
$validate->names();
$validate->email();
$validate->password_match();
$validate->additional();
$validate->sanitize();
$validate->upload();
$validate->register();

?>
