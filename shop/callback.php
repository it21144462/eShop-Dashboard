<?php
// session_start();
// require_once 'vendor/autoload.php'; // Include the Composer autoload file
// include 'config/config.php'; // Include your configuration file

// // Create a new Google client
// $client = new Google\Client();
// $client->setClientId(GOOGLE_CLIENT_ID);
// $client->setClientSecret(GOOGLE_CLIENT_SECRET);
// $client->setRedirectUri(GOOGLE_REDIRECT_URI);
// $client->addScope("email"); // Request email scope
// $client->addScope("profile"); // Request profile scope

// // Check if we have an authorization code in the URL
// if (isset($_GET['code'])) {
//     // Exchange the authorization code for an access token
//     $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

//     // Check for errors
//     if (array_key_exists('error', $token)) {
//         // Handle error appropriately
//         echo 'Error: ' . $token['error'];
//         exit();
//     }

//     // Store the access token in the session
//     $_SESSION['access_token'] = $token['access_token'];

//     // Get user info
//     $client->setAccessToken($token['access_token']);
//     $oauth2 = new Google\Service\Oauth2($client);
//     $userInfo = $oauth2->userinfo->get();

//     // Store user info in session or database
//     $_SESSION['user_email'] = $userInfo->email;
//     $_SESSION['user_name'] = $userInfo->name;

//     // Redirect to a welcome page or home page
//     header('Location: index.php'); // Change this to your desired page
//     exit();
// } else {
//     // If no code is present, redirect back to the login page
//     header('Location: index.php'); // Change this to your login page
//     exit();
// }




session_start(); // Start the session

// Include necessary files
include_once 'lib/Database.php'; // Adjust the path as needed
include_once 'classess/Customer.php'; // Adjust the path as needed
require_once 'vendor/autoload.php'; // Include the Composer autoload file
require_once 'config/config.php'; // Change to require_once

// Define Google URLs only if they are not already defined
if (!defined('GOOGLE_USER_INFO_URL')) {
    define('GOOGLE_USER_INFO_URL', 'https://www.googleapis.com/oauth2/v3/userinfo'); // User info URL
}

if (!defined('GOOGLE_TOKEN_URL')) {
    define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token'); // Token URL
}

if (isset($_GET['code'])) {
    // Get the authorization code
    $code = $_GET['code'];

    // Prepare the data for the token request
    $data = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];

    // Use cURL to make the request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GOOGLE_TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $token_response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log('Curl error: ' . curl_error($ch)); // Log cURL error
        echo "<span class='error'>Curl error: " . curl_error($ch) . "</span>";
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    // Log the response
    error_log('Token response: ' . $token_response);

    $token_response = json_decode($token_response, true);

    if (isset($token_response['access_token'])) {
        // Get user info
        $user_info_response = file_get_contents(GOOGLE_USER_INFO_URL . '?access_token=' . $token_response['access_token']);
        
        // Log the user info response
        error_log('User info response: ' . $user_info_response);

        $user_info = json_decode($user_info_response, true);

        if (isset($user_info['email'])) {
            $customer = new Customer();
            $login_result = $customer->customerLoginWithGoogle($user_info['email']);
            echo $login_result; // Display any messages returned
        } else {
            echo "<span class='error'>Error: User information not received!</span>";
        }
    } else {
        echo "<span class='error'>Error: Access token not received!</span>";
        // Log the full token response for debugging
        error_log('Full token response: ' . print_r($token_response, true));
    }
} else {
    echo "<span class='error'>Error: Authorization code not received!</span>";
}





?>
