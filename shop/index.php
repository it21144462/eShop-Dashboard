<?php
// Start the session with SameSite and Secure cookie attributes
$cookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $cookieParams['lifetime'],
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => isset($_SERVER['HTTPS']), // Ensure cookie is sent over HTTPS only
    'httponly' => true, // Prevent JavaScript access
    'samesite' => 'Lax' // You can also set this to 'Strict' depending on your security needs
]);

session_start();

// Check if user is logged in
if (!isset($_SESSION['cuslogin']) || $_SESSION['cuslogin'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<?php include 'inc/header.php';?>
<?php include 'inc/slider.php';?>

<div class="main">
    <div class="content">
        <div class="content_top">
            <div class="heading">
                <h3>Feature Products</h3>
            </div>
            <div class="clear"></div>
        </div>
        <div class="section group">

            <?php
            $getFpd = $pd->getFeaturedProduct();
            if ($getFpd) {
                while ($result = $getFpd->fetch_assoc()) { 
            ?>

                <div class="grid_1_of_4 images_1_of_4">
                     <a href="details.php?proid=<?php echo $result['productId']; ?>"><img src="admin/<?php echo $result['image']; ?>" alt="" /></a>
                     <h2><?php echo $result['productName']; ?></h2>
                     <p><?php echo $fm->textShorten($result['body'], 60); ?></p>
                     <p><span class="price">TK.<?php echo $result['price']; ?></span></p>
                     <div class="button"><span><a href="details.php?proid=<?php echo $result['productId']; ?>" class="details">Details</a></span></div>
                </div>

            <?php 
                } 
            } 
            ?>
        </div>

        <div class="content_bottom">
            <div class="heading">
                <h3>New Products</h3>
            </div>
            <div class="clear"></div>
        </div>
        <div class="section group">

            <?php
            $getNpd = $pd->getNewProduct();
            if ($getNpd) {
                while ($result = $getNpd->fetch_assoc()) { 
            ?>

                <div class="grid_1_of_4 images_1_of_4">
                     <a href="details.php?proid=<?php echo $result['productId']; ?>"><img class="img1" src="admin/<?php echo $result['image']; ?>" /></a>
                     <h2><?php echo $result['productName']; ?></h2>
                     <p><span class="price">TK.<?php echo $result['price']; ?></span></p>
                     <div class="button"><span><a href="details.php?proid=<?php echo $result['productId']; ?>" class="details">Details</a></span></div>
                </div>

            <?php 
                } 
            } 
            ?>
        </div>
    </div>
</div>

<?php include 'inc/footer.php';?>
