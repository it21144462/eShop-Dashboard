<?php
$filepath = realpath(dirname(__FILE__));
include_once($filepath . '/../lib/Database.php');
include_once($filepath . '/../helpers/Formate.php');
include_once 'lib/Session.php'; // Adjust the path as needed



class Customer {

    private $db;
    private $fm;

    public function __construct() {
        $this->db = new Database();
        $this->fm = new Format();
    }

    // Customer Registration
    public function customerRegistration($data) {
        if (!is_array($data)) {
            return "<span class='error'>Invalid form data!</span>";
        }

        // Use filter_var to sanitize inputs
        $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $address = filter_var($data['address'], FILTER_SANITIZE_STRING);
        $city = filter_var($data['city'], FILTER_SANITIZE_STRING);
        $country = filter_var($data['country'], FILTER_SANITIZE_STRING);
        $zip = filter_var($data['zip'], FILTER_SANITIZE_STRING);
        $phone = filter_var($data['phone'], FILTER_SANITIZE_STRING);
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $pass = mysqli_real_escape_string($this->db->link, md5($data['pass']));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "<span class='error'>Invalid email format!</span>";
        }

        if ($name == "" || $address == "" || $city == "" || $country == "" || $zip == "" || $phone == "" || $email == "" || $pass == "") {
            return "<span class='error'>Fields must not be empty!</span>";
        }

        $mailquery = "SELECT * FROM tbl_customer WHERE email = '$email' LIMIT 1";
        $mailchk = $this->db->select($mailquery);
        if ($mailchk != false) {
            return "<span class='error'>Email already exists!</span>";
        } else {
            $query = "INSERT INTO tbl_customer(name, address, city, country, zip, phone, email, pass) 
                      VALUES('$name', '$address', '$city', '$country', '$zip', '$phone', '$email', '$pass')";
            $inserted_row = $this->db->insert($query);
            if ($inserted_row) {
                return "<span class='success'>Customer Data inserted successfully.</span>";
            } else {
                return "<span class='error'>Customer Data not inserted.</span>";
            }
        }
    }

    // Customer Registration for Google Users
    public function customerRegistrationGoogle($email, $name) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $name = filter_var($name, FILTER_SANITIZE_STRING);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "<span class='error'>Invalid email format!</span>";
        }

        // Check if user already exists
        $mailquery = "SELECT * FROM tbl_customer WHERE email = '$email' LIMIT 1";
        $mailchk = $this->db->select($mailquery);
        if ($mailchk != false) {
            return "<span class='error'>Email already exists!</span>";
        } else {
            // Register the user with just name and email
            $query = "INSERT INTO tbl_customer(name, email) VALUES('$name', '$email')";
            $inserted_row = $this->db->insert($query);
            if ($inserted_row) {
                header("Location: index.php");
                exit(); 
                // return "<span class='success'>Customer Data inserted successfully.</span>";
            } else {
                return "<span class='error'>Customer Data not inserted.</span>";
            }
        }
    }

    // Customer Login
    public function customerLogin($email, $pass) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $pass = mysqli_real_escape_string($this->db->link, md5($pass)); // Hash the entered password

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "<span class='error'>Invalid email format!</span>";
        }

        if (empty($email) || empty($pass)) {
            return "<span class='error'>Fields must not be empty!</span>";
        }

        $query = "SELECT * FROM tbl_customer WHERE email = ? AND pass = ?";
        $stmt = $this->db->link->prepare($query);
        $stmt->bind_param('ss', $email, $pass);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $value = $result->fetch_assoc();
            Session::set("cuslogin", true);
            Session::set("cmrId", $value['id']);
            Session::set("cmrName", $value['name']);
            header("Location: cart.php");
            exit();
        } else {
            return "<span class='error'>Email or Password not matched!</span>";
        }
    }

    // Customer Login with Google
    public function customerLoginWithGoogle($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "<span class='error'>Invalid email format!</span>";
        }

        // Check if user exists in the database
        $query = "SELECT * FROM tbl_customer WHERE email = ?";
        $stmt = $this->db->link->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $value = $result->fetch_assoc();
            // If user exists, set session variables
            Session::set("cuslogin", true);
            Session::set("cmrId", $value['id']);
            Session::set("cmrName", $value['name']);
            header("Location: cart.php");
            exit();
        } else {
            // If user does not exist, register them using the simplified method
            return $this->customerRegistrationGoogle($email, $email); // Use email as default name or get it from Google
        }
    }


    // Get Customer Data
    public function getCustomerData($id) {
        $query = "SELECT * FROM tbl_customer WHERE id = '$id'";
        return $this->db->select($query);
    }

    // Customer Update
    public function customerUpdate($data, $cmrId) {
        // Sanitization
        $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $address = filter_var($data['address'], FILTER_SANITIZE_STRING);
        $city = filter_var($data['city'], FILTER_SANITIZE_STRING);
        $country = filter_var($data['country'], FILTER_SANITIZE_STRING);
        $zip = filter_var($data['zip'], FILTER_SANITIZE_STRING);
        $phone = filter_var($data['phone'], FILTER_SANITIZE_STRING);
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "<span class='error'>Invalid email format!</span>";
        }

        if ($name == "" || $address == "" || $city == "" || $country == "" || $zip == "" || $phone == "" || $email == "") {
            return "<span class='error'>Fields must not be empty!</span>";
        } else {
            $query = "UPDATE tbl_customer
                      SET name = '$name', address = '$address', city = '$city', country = '$country', zip = '$zip', 
                          phone = '$phone', email = '$email'
                      WHERE id = '$cmrId'";
            $updated_row = $this->db->update($query);
            if ($updated_row) {
                return "<span class='success'>Customer Data updated successfully.</span>";
            } else {
                return "<span class='error'>Customer Data not updated!</span>";
            }
        }
    }
}
?>
