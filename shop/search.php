<?php include 'inc/header.php'; ?>

<?php 
// Initialize search variable with sanitization
$search = isset($_GET['search']) ? mysqli_real_escape_string($db->link, $_GET['search']) : ''; // Sanitize input

if (empty($search)) {
    header("Location: 404.php");
    exit(); // Ensure no further code execution after redirect
}

// Sanitize output to prevent XSS
$sanitizedSearch = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
?>

<div class="main">
    <div class="content">
        <div class="content_top">
            <div class="heading">
                <h3>All Searching Product</h3>
            </div>
            <div class="clear"></div>
        </div>
        <div class="section group">

            <?php 
            // Prepare the SQL query
            $query = "SELECT * FROM tbl_product WHERE productName LIKE '%$sanitizedSearch%' OR body LIKE '%$sanitizedSearch%' ORDER BY productId DESC LIMIT 30";
            $post = $db->select($query);

            if ($post) {
                while ($result = $post->fetch_assoc()) { 
            ?>
                    <div class="grid_1_of_4 images_1_of_4">
                        <a href="details.php?proid=<?php echo htmlspecialchars($result['productId'], ENT_QUOTES, 'UTF-8'); ?>">
                            <img src="admin/<?php echo htmlspecialchars($result['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" />
                        </a>
                        <h2><?php echo htmlspecialchars($result['productName'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p><?php echo htmlspecialchars(strip_tags($fm->textShorten($result['body'], 60)), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><span class="price">TK. <?php echo htmlspecialchars($result['price'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <div class="button"><span><a href="details.php?proid=<?php echo htmlspecialchars($result['productId'], ENT_QUOTES, 'UTF-8'); ?>" class="details">Details</a></span></div>
                    </div>
                <?php 
                } 
            } else { 
                ?>
                <p style="color: red; font-size: 35px; font-weight: bold; text-align: center;">Your Search Query not found !!.</p>
            <?php 
            } 
            ?>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>
