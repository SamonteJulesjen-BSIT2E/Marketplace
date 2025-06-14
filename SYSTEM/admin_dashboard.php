<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>ANIMAPLEX Dashboard</title>
  <link rel="stylesheet" href="admindash.css" />

   
<body>
<div class="sidebar">
  <h2>BLACK MARKET</h2>
  <nav>
    <div class="nav-section">
      <h3>📦 Dashboard</h3>
      <a data-section="orders" class="active">Orders</a>
      <a data-section="messages">Messages</a>
    </div>
    <div class="nav-section">
      <h3>👥 Management</h3>
      <a data-section="users">Users</a>
      <a data-section="products">Products</a>
    </div>
    <div class="nav-section">
      <a href="login.php" class="logout-button">◀️ Logout</a>
    </div>
  </nav>
</div>

<div class="main">
  <div id="orders">
    <h1>Orders</h1>
    <input type="text" class="section-search" placeholder="Search orders...">
    <form method="POST">
      <button type="submit" name="delete_cancelled">Delete Cancelled Orders</button>
    </form>
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer Name</th>
          <th>Order Date</th>
          <th>Shipping Address</th>
          <th>Description</th>
          <th>Payment Method</th>
          <th>Payment Status</th>
          <th>Status</th>
          <th>Total Price</th>
          <th>Print</th>
        </tr>
      </thead>
      <tbody>
        <!-- Orders populated by PHP -->
      </tbody>
    </table>
  </div>

  <div id="messages" class="hidden">
    <h1>Messages</h1>
    <input type="text" class="section-search" placeholder="Search messages...">
    <table>
      <thead>
        <tr>
          <th>Message ID</th>
          <th>Username</th>
          <th>Message</th>
          <th>Sent At</th>
        </tr>
      </thead>
      <tbody>
        <!-- Messages populated by PHP -->
      </tbody>
    </table>
  </div>

  <div id="users" class="hidden">
    <h1>Users</h1>
    <input type="text" class="section-search" placeholder="Search users...">
    <table>
      <thead>
        <tr><th>User ID</th><th>Username</th><th>Password</th></tr>
      </thead>
      <tbody>
        <!-- Users populated by PHP -->
      </tbody>
    </table>
  </div>

  <div id="products" class="hidden">
    <h1>📦 Product Management</h1>
    <div class="message"> <!-- Placeholder for messages --></div>
    <button id="toggleAddProductBtn">➕ Add Product</button>
    <div id="addProductFormContainer" class="hidden">
      <form method="POST" enctype="multipart/form-data">
        <label>Product Name:</label>
        <input type="text" name="Products_name" required>

        <label>Price (₱):</label>
        <input type="number" name="price" step="0.01" required>

        <label>Description:</label>
        <textarea name="description" required></textarea>

        <label>Image:</label>
        <input type="file" name="product_image" accept="image/*" required>

        <button type="submit">Add Product</button>
      </form>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.sidebar nav a').forEach(link => {
    link.addEventListener('click', () => {
      document.querySelectorAll('.sidebar nav a').forEach(a => a.classList.remove('active'));
      link.classList.add('active');
      document.querySelectorAll('.main > div').forEach(div => div.classList.add('hidden'));
      const section = link.getAttribute('data-section');
      if (section) {
        document.getElementById(section).classList.remove('hidden');
      }
    });
  });

  document.getElementById('toggleAddProductBtn').addEventListener('click', () => {
    const formContainer = document.getElementById('addProductFormContainer');
    formContainer.classList.toggle('hidden');
  });

  document.querySelectorAll('.section-search').forEach(searchInput => {
    searchInput.addEventListener('input', () => {
      const section = searchInput.closest('div');
      const filter = searchInput.value.toLowerCase();
      const table = section.querySelector('table');
      const rows = table.querySelectorAll('tbody tr');

      rows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        row.style.display = rowText.includes(filter) ? '' : 'none';
      });
    });
  });
</script>
</body>
</html>
