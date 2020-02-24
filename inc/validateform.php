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
      error_log("Error");
      $this->redirect();
    }
  }
  
  # Register user entry
  public function sanitize() {
    $this->sanitized = array();
    
    foreach ($_POST as $key => $value) {
      $this->sanitized[$key] = $this->open_link()->real_escape_string($value);
    }
        
    return $this->sanitized;    
  }
  
  public function register() {
    $sql = "";
    
    if(file_exists(dirname(ROOT) . DS . SQL . DS . "createtable.sql")) {
      $sql = file_get_contents(dirname(ROOT) . DS . SQL . DS . "createtable.sql");
      $this->set_multyquery($sql);
    }
    
    $email = $this->sanitized["email"];
    
    $sql = "SELECT id 
          FROM customers
          WHERE email = '{$email}'";
    
    if(count($this->get_query($sql)) == 1) {
      $_SESSION["error"][] = "A user with that e-mail address already exists";
      $this->redirect();
    }
    
    $firstName = $this->sanitized["firstName"];
    $lastName = $this->sanitized["lastName"];
    
    $salt = "\$6\$rounds=5000\$".randomStr(8)."\$";    
    $password = $this->sanitized["password"];
    $crypted = substr(crypt($password, $salt), strlen($salt));
    
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
  
  public function upload() {
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($_FILES["images"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    # Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["images"]["tmp_name"]);
        if($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }
    
    # Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }
    
    # Check file size
    if ($_FILES["images"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["images"]["tmp_name"], $target_file)) {
            echo "The file ". basename( $_FILES["images"]["name"]). " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
  }
  
  private function redirect() {
    header("Location: ../form.php");
    exit();
  }   
}

$validate = new Validateform();
$validate->required();
$validate->names();
$validate->password_match();
$validate->additional();
$validate->sanitize();
$validate->upload();
//$validate->register();

?>