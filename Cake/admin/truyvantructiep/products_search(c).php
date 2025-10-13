<?php
include($_SERVER["DOCUMENT_ROOT"] . '/admin/inc/header.php');
include($_SERVER['DOCUMENT_ROOT'] . "/admin/inc/navbar.php");
include($_SERVER['DOCUMENT_ROOT'] . "/database/connect.php");

// Khởi tạo biến tìm kiếm
$searchQuery = '';
if (isset($_POST['submit'])) {
    $searchQuery = mysqli_real_escape_string($conn, $_POST['search']);
}

// Truy vấn tổng số sản phẩm theo từ khóa tìm kiếm
$query = "SELECT a.ProductId, a.Name, a.Image, c.CategoryName, b.BrandName, a.BuyPrice, a.SellPrice, a.CountView, a.Status, a.Quantity, a.Avaiable_quantity 
          FROM `products` a, category c, brands b 
          WHERE a.CategoriId = c.CategoryId 
          AND a.BrandId = b.BrandId 
          AND is_accept = 1 
          AND a.Name LIKE '%$searchQuery%'";

$Products = mysqli_query($conn, $query);
$total = mysqli_num_rows($Products);

$limit = 5;
$page = ceil($total / $limit);
$cr_page = (isset($_GET['page']) ? $_GET['page'] : 1);
$start = ($cr_page - 1) * $limit;

// Truy vấn sản phẩm với phân trang
$query2 = "SELECT a.ProductId, a.Name, a.Image, c.CategoryName, b.BrandName, a.BuyPrice, a.SellPrice, a.CountView, a.Status, a.Quantity, a.Avaiable_quantity 
           FROM `products` a, category c, brands b 
           WHERE a.CategoriId = c.CategoryId 
           AND a.BrandId = b.BrandId 
           AND is_accept = 1 
           AND a.Name LIKE '%$searchQuery%' 
           ORDER BY CountView DESC 
           LIMIT $start, $limit";

$Products = mysqli_query($conn, $query2);

// Truy vấn danh mục sản phẩm
$query3 = "SELECT * FROM Category WHERE status = 1";
$Category = mysqli_query($conn, $query3);
?>

<div class="layout-page">
  <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
      <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
        <i class="bx bx-menu bx-sm"></i>
      </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
      <!-- User -->
      <ul class="navbar-nav flex-row align-items-center ms-auto">
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
          <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <div class="avatar avatar-online">
              <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="#">
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <div class="avatar avatar-online">
                      <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <span class="fw-semibold d-block"><?php echo session::get('Username') ?></span>
                    <small class="text-muted">Admin</small>
                  </div>
                </div>
              </a>
            </li>
            <li>
              <div class="dropdown-divider"></div>
            </li>
            <li>
              <a class="dropdown-item" href="?action=logout">
                <i class="bx bx-power-off me-2"></i>
                <span class="align-middle">Log Out</span>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>

  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="fw-bold py-3 mb-4">Danh sách sản phẩm</h4>

      <form action="products_search.php" method="POST" class="form-control">
        <input placeholder="Tìm kiếm sản phẩm" name="search" type="text" value="<?php echo htmlspecialchars($searchQuery); ?>">
        <input type="submit" name="submit" value="Tìm kiếm" class="btn btn-primary">
      </form>

      <div class="card">
        <div class="table-responsive text-nowrap">
          <table class="table">
            <thead>
              <tr>
                <th>STT</th>
                <th>Tên sản phẩm</th>
                <th>Hình ảnh</th>
                <th>Loại bánh</th>
                <th>Thương hiệu</th>
                <th>Giá nhập</th>
                <th>Giá bán</th>
                <th>Số lượng nhập</th>
                <th>Số lượng bán</th>
                <th>Số lượt xem</th>
                <th>Trạng thái</th>
                <th>Chức năng</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              <?php if (mysqli_num_rows($Products) > 0) : ?>
                <?php foreach ($Products as $key => $value) : ?>
                  <tr>
                    <td><?php echo $key + 1 + $start; ?></td>
                    <td><?php echo $value['Name'] ?></td>
                    <td>
                      <img src="../uploads/<?php echo $value['Image'] ?>" alt="" width="100">
                    </td>
                    <td><?php echo $value['CategoryName'] ?></td>
                    <td><?php echo $value['BrandName'] ?></td>
                    <td><?php echo $value['BuyPrice'] ?></td>
                    <td><?php echo $value['SellPrice'] ?></td>
                    <td><?php echo $value['Quantity'] ?></td>
                    <td>
                      <?php if ($value['Avaiable_quantity'] > 0) : ?>
                        <?php echo $value['Avaiable_quantity']; ?>
                      <?php else : ?>
                        <span style="color: red;">Hết hàng</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo $value['CountView'] ?></td>
                    <td><?php echo ($value['Status'] == 1) ? 'Hiện' : 'Ẩn'; ?></td>
                    <td>
                      <button type="button" class="btn btn-primary">
                        <a style="color: white;" href="product_update.php?id=<?php echo $value['ProductId'] ?>">Sửa</a>
                      </button>
                      <button type="button" class="btn btn-danger">
                        <a style="color: white;" href="product_delete.php?id=<?php echo $value['ProductId'] ?>" onclick="return confirm('Bạn có chắc chắn xóa ?')">Xóa</a>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else : ?>
                <tr>
                  <td colspan="12" class="text-center">Không tìm thấy sản phẩm nào</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-4">
        <button type="button" class="btn btn-success">
          <a style="color: white;" href="product_add.php">Thêm Mới</a>
        </button>
      </div>

      <?php if ($page > 1) { ?>
        <hr>
        <nav aria-label="Page navigation">
          <ul class="pagination">
            <?php if ($cr_page - 1 > 0) { ?>
              <li class="page-item first">
                <a class="page-link" href="products_search.php?page=1"><i class="tf-icon bx bx-chevrons-left"></i></a>
              </li>
              <li class="page-item prev">
                <a class="page-link" href="products_search.php?page=<?php echo $cr_page - 1 ?>"><i class="tf-icon bx bx-chevron-left"></i></a>
              </li>
            <?php } ?>
            <?php for ($i = 1; $i <= $page; $i++) { ?>
              <li class="page-item <?php echo (($cr_page == $i) ? 'active' : '') ?>">
                <a class="page-link" href="products_search.php?page=<?php echo $i ?>"><?php echo $i ?></a>
              </li>
            <?php } ?>
            <?php if ($cr_page + 1 <= $page) { ?>
              <li class="page-item next">
                <a class="page-link" href="products_search.php?page=<?php echo $cr_page + 1 ?>"><i class="tf-icon bx bx-chevron-right"></i></a>
              </li>
              <li class="page-item last">
                <a class="page-link" href="products_search.php?page=<?php echo $page ?>"><i class="tf-icon bx bx-chevrons-right"></i></a>
              </li>
            <?php } ?>
          </ul>
        </nav>
      <?php } ?>
    </div>
  </div>
</div>

<?php include($_SERVER["DOCUMENT_ROOT"] . '/admin/inc/footer.php'); ?>
