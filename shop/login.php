<?php include 'inc/header.php'; ?>
<?php
// Include Google OAuth configuration
require_once 'config/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if customer is logged in
$login = Session::get("cuslogin");
if ($login == true) {
    header("Location: order.php");
    exit();
}

// Initialize variables to store messages
$custLogin = $customerReg = "";

// Handle Google OAuth login
if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Exchange code for access token
    $token = getGoogleAccessToken($code);
    $userInfo = getGoogleUserInfo($token);

    // Here, you can save the user information in your database and log them in
    // You can create a new customer or log in an existing customer based on the email
    $email = $userInfo['email'];
    
    // Attempt to log in the customer using the email retrieved from Google
    $custLogin = $cmr->customerLogin($email, null); // Pass null for password
    if (strpos($custLogin, 'success') !== false) {
        // Successful login or registration
        header("Location: order.php");
        exit();
    } else {
        // If the customer doesn't exist, consider registering them
        $customerReg = $cmr->customerRegistration(array(
            'name' => $userInfo['name'], // Assuming you have a 'name' in userInfo
            'city' => '', // Set default values or prompt user for input
            'zip' => '',
            'email' => $email,
            'address' => '',
            'country' => '',
            'phone' => '',
            'pass' => '' // No password needed for Google login
        ));
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    // Sanitize and validate email and password
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['pass']);

    if ($email && !empty($password)) {
        // Hash the password
        $hashedPass = md5($password);

        // Use prepared statements for database queries
        $custLogin = $cmr->customerLogin($email, $hashedPass);
    } else {
        $custLogin = "Invalid email or password!";
    }
}

// Function to get Google access token
function getGoogleAccessToken($code) {
    $client = new \GuzzleHttp\Client();
    $response = $client->post(GOOGLE_TOKEN_URL, [
        'form_params' => [
            'code' => $code,
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'grant_type' => 'authorization_code',
        ]
    ]);

    $responseData = json_decode($response->getBody(), true);
    return $responseData['access_token'];
}

// Function to get user info from Google
function getGoogleUserInfo($token) {
    $client = new \GuzzleHttp\Client();
    $response = $client->get('https://www.googleapis.com/oauth2/v2/userinfo', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token
        ]
    ]);

    return json_decode($response->getBody(), true);
}
?> 

<div class="main">
    <div class="content">
        <div class="login_panel">
            <?php 
            // Display login error message
            if (!empty($custLogin)) {
                echo $custLogin;
            }
            ?>
            <h3>Existing Customers</h3>
            <p>Sign in with the form below.</p>
            <form action="" method="post">
                <input name="email" placeholder="Email" type="text" required/>
                <input name="pass" placeholder="Password" type="password" required/>
                <div class="buttons">
                    <div><button class="grey" name="login">Log In</button></div>
                </div>                               
            </form>

            <!-- Separate Google OAuth Login Button -->
            <h4><span></span></h4>
            <a href="<?php echo GOOGLE_AUTH_URL . '?client_id=' . GOOGLE_CLIENT_ID . '&redirect_uri=' . GOOGLE_REDIRECT_URI . '&response_type=code&scope=email profile'; ?>">
                <button style=" color: white; font-size: 15px; padding: 7px 24px; border: none; border-radius: 3px;" class="grey">Login with Google</button>
            </a>

        </div>

        <?php
        // Handle registration form submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
            // Sanitize and validate input fields
            $name = htmlspecialchars(trim($_POST['name']));
            $city = htmlspecialchars(trim($_POST['city']));
            $zip = htmlspecialchars(trim($_POST['zip']));
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $address = htmlspecialchars(trim($_POST['address']));
            $country = htmlspecialchars(trim($_POST['country']));
            $phone = htmlspecialchars(trim($_POST['phone']));
            $password = htmlspecialchars(trim($_POST['pass']));

            if ($email && !empty($password)) {
                // Hash the password
                $hashedPass = md5($password);

                // Register the customer
                $customerReg = $cmr->customerRegistration(array(
                    'name' => $name, 
                    'city' => $city, 
                    'zip' => $zip, 
                    'email' => $email, 
                    'address' => $address, 
                    'country' => $country, 
                    'phone' => $phone, 
                    'pass' => $hashedPass
                ));
            } else {
                $customerReg = "Invalid input data!";
            }
        }
        ?>          
        <div class="register_account">
            <?php 
            // Display registration error message
            if (!empty($customerReg)) {
                echo $customerReg;
            }
            ?>
            <h3>Register New Account</h3>
            <form action="" method="post">
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <div><input type="text" name="name" placeholder="Name" required/></div>
                                <div><input type="text" name="city" placeholder="City" required/></div>
                                <div><input type="text" name="zip" placeholder="Zip-Code" required/></div>
                                <div><input type="text" name="email" placeholder="Email" required/></div>
                            </td>
                            <td>
                                <div><input type="text" name="address" placeholder="Address" required/></div>
                                <div><input type="text" name="country" placeholder="Country" required/></div>
                                <div><input type="text" name="phone" placeholder="Phone" required/></div>
                                <div><input type="password" name="pass" placeholder="Password" required/></div>
                            </td>
                        </tr>
                    </tbody>
                </table> 
                <div class="search">
                    <div><button class="grey" name="register">Create Account</button></div>
                </div>
                <div class="clear"></div>
            </form>
        </div>  
        <div class="clear"></div>
    </div>
</div>
<?php include 'inc/footer.php'; ?>
