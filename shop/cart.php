<?php 
include 'inc/header.php';

// Check if user is logged in
$login = Session::get("cuslogin");
if ($login == false) {
    header("Location:login.php");
    exit();  // Always use exit() after a header redirect to stop script execution
}

// Use a prepared statement to safely handle $_GET['delpro']
if (isset($_GET['delpro'])) {
    $delId = preg_replace('/[^-a-zA-Z0-9_]/', '', $_GET['delpro']); // Additional sanitization
    $delProduct = $ct->delProductByCart($delId);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $cartId = filter_input(INPUT_POST, 'cartId', FILTER_SANITIZE_NUMBER_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);

    // Ensure that $cartId and $quantity are valid numbers
    if ($cartId && $quantity !== false) {
        $updateCart = $ct->updateCartQuantity($cartId, $quantity);

        if ($quantity <= 0) {
            $delProduct = $ct->delProductByCart($cartId);
        }
    } else {
        echo "Invalid input.";
    }
}

// Avoid header redirect looping with a simple check
if (!isset($_GET['id'])) {
    echo "<meta http-equiv='refresh' content='0;URL=?id=nayem' />";
}
?>

<style>
.payment1 {
    width: 500px;
    min-height: 200px;
    text-align: center;
    border: 1px solid #ddd;
    margin: 0 auto;
    padding: 50px;
}
.payment1 h2 {
    border-bottom: 1px solid #ddd;
    margin-bottom: 40px;
    padding-bottom: 10px;
}
.payment1 a {
    background: #ff0000;
    border-radius: 3px;
    color: #fff;
    font-size: 25px;
    padding: 5px 30px;
}
.back a {
    width: 160px;
    margin: 5px auto 0;
    padding: 7px 0;
    text-align: center;
    display: block;
    background: #555;
    border: 1px solid #333;
    color: #fff;
    border-radius: 3px;
    font-size: 25px;
}
</style>

<div class="main">
    <div class="content">
        <div class="cartoption">
            <div class="cartpage">
                <h2>Your Cart</h2>
                <?php 
                if (isset($updateCart)) {
                    echo $updateCart;
                }
                if (isset($delProduct)) {
                    echo $delProduct;
                }
                ?>
                <table class="tblone">
                    <tr>
                        <th width="5%">SL</th>
                        <th width="30%">Product Name</th>
                        <th width="10%">Image</th>
                        <th width="15%">Price</th>
                        <th width="15%">Quantity</th>
                        <th width="15%">Total Price</th>
                        <th width="10%">Action</th>
                    </tr>
                    <tr>
                    <?php 
                    $getPro = $ct->getCartProduct();
                    if ($getPro) {
                        $i = 0;
                        $sum = 0;
                        $qty = 0;
                        while ($result = $getPro->fetch_assoc()) {
                            $i++;
                    ?>
                        <td><?php echo htmlspecialchars($i); ?></td>
                        <td><?php echo htmlspecialchars($result['productName']); ?></td>
                        <td><img src="admin/<?php echo htmlspecialchars($result['image']); ?>" alt="" /></td>
                        <td>Tk. <?php echo htmlspecialchars($result['price']); ?></td>
                        <td>
                            <form action="" method="post">
                                <input type="hidden" name="cartId" value="<?php echo htmlspecialchars($result['cartId']); ?>"/>
                                <input type="number" name="quantity" value="<?php echo htmlspecialchars($result['quantity']); ?>"/>
                                <input type="submit" name="submit" value="Update"/>
                            </form>
                        </td>
                        <td>Tk. <?php echo $result['price'] * $result['quantity']; ?></td>
                        <td><a onclick="return confirm('Are you Sure to Delete!')" href="?delpro=<?php echo htmlspecialchars($result['cartId']); ?>">X</a></td>
                    </tr>
                    <?php 
                        $qty += $result['quantity'];
                        $sum += $result['price'] * $result['quantity'];
                        Session::set("qty", $qty);
                        Session::set("sum", $sum);
                    } 
                    } ?>
                </table>

                <?php if ($ct->checkCartTable()): ?>
                <table style="float:right;text-align:left;" width="40%">
                    <tr>
                        <th>Sub Total : </th>
                        <td>TK. <?php echo htmlspecialchars($sum); ?></td>
                    </tr>
                    <tr>
                        <th>VAT : </th>
                        <td>10%</td>
                    </tr>
                    <tr>
                        <th>Grand Total :</th>
                        <td>TK. <?php echo $sum + ($sum * 0.1); ?></td>
                    </tr>
                </table>
                <?php else: 
                    header("Location:index.php");
                    exit();
                endif; ?>
            </div>
            <div class="shopping">
                <div class="shopleft">
                    <a href="index.php"><img src="images/shop.png" alt="" /></a>
                </div>
                <div class="shopright">
                    <a href="payment.php"><img src="images/check.png" alt="" /></a>
                </div>
            </div>
        </div>  
        <div class="clear"></div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>
