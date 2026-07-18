<?php
$page_title="Shop"; include("includes/header.php");
if (isset($_POST["product_id"])) {
    require_csrf();
    $id=(int)$_POST["product_id"]; $product=find_row("products.txt",0,$id);
    if (isset($product[0]) && (int)$product[5] > 0) { if (!isset($_SESSION["cart"])) { $_SESSION["cart"]=array(); } if (!isset($_SESSION["cart"][$id])) { $_SESSION["cart"][$id]=0; } if ($_SESSION["cart"][$id] < (int)$product[5]) { $_SESSION["cart"][$id]=$_SESSION["cart"][$id]+1; audit("Added product " . $id . " to cart"); echo '<div class="notice success">Added to your cart.</div>'; } }
}
$category=isset($_GET["category"])?safe_field($_GET["category"]):"All";
?>
<section class="page-heading"><span class="eyebrow">MediNest collection</span><h1>Pharmacy essentials,<br>without the clutter.</h1><p>Browse our classroom-demo catalog by category.</p></section>
<div class="filters"><a href="store.php">All</a><a href="store.php?category=Pain%20Relief">Pain Relief</a><a href="store.php?category=Vitamins">Vitamins</a><a href="store.php?category=First%20Aid">First Aid</a><a href="store.php?category=Health%20Devices">Devices</a></div>
<section class="product-grid">
<?php foreach(read_rows("products.txt") as $product) { if ($category=="All" || $product[2]==$category) { ?>
<article class="product-card"><div class="product-visual <?php echo h($product[6]); ?>" role="img" aria-label="<?php echo h($product[1]); ?> product photo"></div><small><?php echo h($product[2]); ?></small><h2><?php echo h($product[1]); ?></h2><p><?php echo h($product[3]); ?></p><div class="product-bottom"><strong>&#8369;<?php echo number_format((float)$product[4],2); ?></strong><form method="post"><?php echo csrf_field(); ?><input type="hidden" name="product_id" value="<?php echo (int)$product[0]; ?>"><button <?php if ($product[5]<=0) echo "disabled"; ?>><?php echo $product[5]>0?"Add to cart":"Out of stock"; ?></button></form></div></article>
<?php }} ?>
</section>
<?php include("includes/footer.php"); ?>
