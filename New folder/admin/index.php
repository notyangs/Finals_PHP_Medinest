<?php
$page_title = "Seller dashboard";
$base = "../";
include("../includes/header.php");

if (!is_admin()) {
    echo '<section class="center-card">
            <h1>Admin access only.</h1>
            <a class="button" href="../login.php">Sign in</a>
          </section>';
    include("../includes/footer.php");
    return;
}

$products = read_rows("products.txt");
$orders = read_rows("orders.txt");
$users = read_rows("users.txt");

$low = 0;
foreach ($products as $p) {
    if ($p[5] < 20) {
        $low++;
    }
}
?>

<section class="admin-heading">
    <div>
        <span class="eyebrow">Seller workspace</span>
        <h1>Good day, <?php echo htmlspecialchars($_SESSION["name"]); ?>.</h1>
        <p>Here is the current MediNest overview.</p>
    </div>

    <a class="button" href="products.php">Add product</a>
</section>


<section class="dashboard-hero">
    <img
        src="../assets/images/hero.png"
        alt="MediNest Hero"
        class="dashboard-hero-img">
</section>

<section class="stat-grid">
    <article>
        <span>Products</span>
        <strong><?php echo count($products); ?></strong>
        <small>catalog records</small>
    </article>

    <article>
        <span>Low stock</span>
        <strong><?php echo $low; ?></strong>
        <small>below 20 units</small>
    </article>

    <article>
        <span>Orders</span>
        <strong><?php echo count($orders); ?></strong>
        <small>recorded orders</small>
    </article>

    <article>
        <span>Users</span>
        <strong><?php echo count($users); ?></strong>
        <small>buyer and admin</small>
    </article>
</section>

<section class="admin-links">
    <a href="products.php">
        <b>Catalog</b>
        <span>Add and update stock or prices &rarr;</span>
    </a>

    <a href="users.php">
        <b>System users</b>
        <span>Add and modify admin roles &rarr;</span>
    </a>

    <a href="reports.php">
        <b>Reports</b>
        <span>Inventory and audit activity &rarr;</span>
    </a>
</section>

<?php include("../includes/footer.php"); ?>