<?php
include($_SERVER['DOCUMENT_ROOT'] . "/admin/inc/header.php");
include($_SERVER['DOCUMENT_ROOT'] . "/database/connect.php");

$message = ""; // Biến để lưu thông báo

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $id_brands = $_POST['id_brands'];
    $id_categories = $_POST['id_categories'];
    $buy_price = $_POST['buy_price'];
    $sell_price = $_POST['sell_price'];
    $quantity = $_POST['quantity'];
    $avaiable_quantity = $_POST['avaiable_quantity'];
    $status = $_POST['status'];
    $description = $_POST['description'];

    if (!is_numeric($buy_price) || !is_numeric($sell_price)) {
        $message = "Giá sản phẩm phải là số.";
    } 
    else if (!is_numeric($quantity) || !is_numeric($avaiable_quantity)) {
        $message = "Số lượng sản phẩm phải là số.";
    }
    // Kiểm tra giá trị âm
    else if ($buy_price < 0 || $sell_price < 0) {
        $message = "Giá sản phẩm không được là số âm.";
    } 
    else if ($quantity < 0 || $avaiable_quantity < 0) {
        $message = "Số lượng sản phẩm không được là số âm.";
    }
    // Kiểm tra số lượng bán có lớn hơn số lượng nhập
    else if ($avaiable_quantity > $quantity) {
        $message = "Số lượng bán không được lớn hơn số lượng nhập.";
    } 
    else {
        // Kiểm tra tên sản phẩm có bị trùng không
        $checkNameQuery = "SELECT * FROM Products WHERE Name = '$name'";
        $nameResult = mysqli_query($conn, $checkNameQuery);

        if (mysqli_num_rows($nameResult) > 0) {
            $message = "Tên sản phẩm đã tồn tại.";
        } else {
            // Kiểm tra hình ảnh
            if (isset($_FILES['image'])) {
                $file_name = $_FILES['image']['name'];
                $file_tmp = $_FILES['image']['tmp_name'];
                $div = explode('.', $file_name);
                $file_ext = strtolower(end($div));
                $unique_image = substr(md5(time()), 0, 10) . '.' . $file_name;

                // Di chuyển file upload
                move_uploaded_file($file_tmp, "..//uploads//" . $unique_image);

                // Cập nhật thông tin sản phẩm
                $query = "INSERT INTO Products (Name, Image, Quantity, Avaiable_quantity, Description, BuyPrice, SellPrice, Status, CategoriId, BrandId) 
                          VALUES ('$name', '$unique_image', '$quantity', '$avaiable_quantity', '$description', '$buy_price', '$sell_price', '$status', '$id_categories', '$id_brands')";
            }

            $insert = mysqli_query($conn, $query);
            if ($insert) {
                $message = "Thêm sản phẩm thành công.";
                echo "<script>setTimeout(function() { window.location.href = 'product_list.php'; }, 2000);</script>";
            } else {
                $message = "Xảy ra lỗi khi thêm sản phẩm.";
            }
        }
    }
}
?>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Thêm sản phẩm mới</h4>
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Hiển thị thông báo -->
                        <div class="mb-3">
                            <?php if (!empty($message)) { ?>
                                <div class="alert alert-info"><?php echo $message; ?></div>
                            <?php } ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label" for="name">Tên sản phẩm</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Bánh kem Le Castella" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Thương hiệu</label>
                                <select name="id_brands" class="form-control">
                                    <option value="">--------------Loại thương hiệu--------------</option>
                                    <?php 
                                    // Lấy danh sách thương hiệu
                                    $sql2 = "SELECT * FROM brands where status = 1";
                                    $brands = mysqli_query($conn, $sql2);
                                    foreach ($brands as $value) { ?>
                                        <option value="<?php echo $value["BrandId"] ?>">
                                            <?php echo $value["BrandName"] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Loại bánh</label>
                                <select name="id_categories" class="form-control">
                                    <option value="">--------------Loại bánh--------------</option>
                                    <?php 
                                    // Lấy danh sách loại bánh
                                    $sql1 = "SELECT * FROM category where status = 1";
                                    $categorys = mysqli_query($conn, $sql1);
                                    foreach ($categorys as $value) { ?>
                                        <option value="<?php echo $value["CategoryId"] ?>">
                                            <?php echo $value["CategoryName"] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hình ảnh</label>
                                <input type="file" class="form-control mb-4" id="image" name="image" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <input type="text" class="form-control" name="description" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Giá nhập</label>
                                <input type="text" class="form-control" name="buy_price" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Giá bán</label>
                                <input type="text" class="form-control" name="sell_price" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số lượng nhập</label>
                                <input type="text" class="form-control" name="quantity" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số lượng bán</label>
                                <input type="text" class="form-control" name="avaiable_quantity" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label><br>
                                <label>
                                    <input type="radio" name="status" value="1" checked> Hiện
                                </label><br>
                                <label>
                                    <input type="radio" name="status" value="0"> Ẩn
                                </label>
                            </div>
                            <button type="submit" name="submit" class="btn btn-success mt-4">Thêm</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include($_SERVER['DOCUMENT_ROOT'] . "/admin/inc/footer.php");
?>
