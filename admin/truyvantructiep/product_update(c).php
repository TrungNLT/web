<?php
include($_SERVER['DOCUMENT_ROOT'] . "/admin/inc/header.php");
include($_SERVER['DOCUMENT_ROOT'] . "/database/connect.php");

$message = ""; // Biến để lưu thông báo

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql1 = "SELECT * FROM category WHERE status = 1";
    $categorys = mysqli_query($conn, $sql1);

    $sql2 = "SELECT * FROM brands WHERE status = 1";
    $brands = mysqli_query($conn, $sql2);

    $query = "SELECT * FROM Products WHERE ProductId = '$id'";
    $data = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($data);
}

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
    } elseif (!is_numeric($quantity) || !is_numeric($avaiable_quantity)) {
        $message = "Số lượng sản phẩm phải là số.";
    } elseif ($buy_price < 0 || $sell_price < 0) {
        $message = "Giá sản phẩm không được là số âm.";
    } elseif ($quantity < 0 || $avaiable_quantity < 0) {
        $message = "Số lượng sản phẩm không được là số âm.";
    } elseif ($avaiable_quantity > $quantity) {
        $message = "Số lượng bán không được lớn hơn số lượng nhập.";
    } else {
        // Kiểm tra hình ảnh
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $div = explode('.', $file_name);
            $file_ext = strtolower(end($div));
            $unique_image = substr(md5(time()), 0, 10) . '.' . $file_name;

            // Di chuyển file upload
            move_uploaded_file($file_tmp, "..//uploads//" . $unique_image);

            // Cập nhật thông tin sản phẩm với hình ảnh mới
            $query = "UPDATE `products` SET `Name`='$name', `Image`='$unique_image', `Quantity`='$quantity', 
                      `Avaiable_quantity`='$avaiable_quantity', `Description`='$description',
                      `BuyPrice`='$buy_price', `SellPrice`='$sell_price', `Status`='$status', 
                      `CategoriId`='$id_categories', `BrandId`='$id_brands' WHERE ProductId = '$id'";
        } else {
            // Nếu người dùng không chọn file hình ảnh, giữ nguyên hình ảnh cũ
            $query = "UPDATE `products` SET `Name`='$name', `Quantity`='$quantity', 
                      `Avaiable_quantity`='$avaiable_quantity', `Description`='$description',
                      `BuyPrice`='$buy_price', `SellPrice`='$sell_price', `Status`='$status', 
                      `CategoriId`='$id_categories', `BrandId`='$id_brands' WHERE ProductId = '$id'";
        }

        $update = mysqli_query($conn, $query);
        if ($update) {
            $message = "Cập nhật sản phẩm thành công.";
            echo "<script>setTimeout(function() { window.location.href = 'product_list.php'; }, 2000);</script>";
        } else {
            $message = "Xảy ra lỗi khi cập nhật.";
        }
    }
}

?>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Cập nhật sản phẩm</h4>
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
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['Name']) ?>" placeholder="Bánh kem Le Castella" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Thương hiệu</label>
                                <select name="id_brands" class="form-control">
                                    <option value="">--------------Loại thương hiệu--------------</option>
                                    <?php foreach ($brands as $value) { ?>
                                        <option value="<?php echo $value["BrandId"] ?>" <?php echo (($value['BrandId'] == $product['BrandId']) ? 'selected' : '') ?>>
                                            <?php echo htmlspecialchars($value["BrandName"]) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Loại bánh</label>
                                <select name="id_categories" class="form-control">
                                    <option value="">--------------Loại bánh--------------</option>
                                    <?php foreach ($categorys as $value) { ?>
                                        <option value="<?php echo $value["CategoryId"] ?>" <?php echo (($value['CategoryId'] == $product['CategoriId']) ? 'selected' : '') ?>>
                                            <?php echo htmlspecialchars($value["CategoryName"]) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hình ảnh</label>
                                <input type="file" class="form-control mb-4" id="image" name="image" />
                                <img src="..//uploads//<?php echo htmlspecialchars($product['Image']) ?>" alt="" width="150">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <input type="text" class="form-control" name="description" value="<?php echo htmlspecialchars($product['Description']) ?>" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Giá nhập</label>
                                <input type="text" class="form-control" name="buy_price" value="<?php echo htmlspecialchars($product['BuyPrice']) ?>" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Giá bán</label>
                                <input type="text" class="form-control" name="sell_price" value="<?php echo htmlspecialchars($product['SellPrice']) ?>" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số lượng nhập</label>
                                <input type="text" class="form-control" name="quantity" value="<?php echo htmlspecialchars($product['Quantity']) ?>" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số lượng bán</label>
                                <input type="text" class="form-control" name="avaiable_quantity" value="<?php echo htmlspecialchars($product['Avaiable_quantity']) ?>" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label><br>
                                <label>
                                    <input type="radio" name="status" value="1" <?php echo ($product['Status'] == 1) ? 'checked' : ''; ?>> Hiện
                                </label><br>
                                <label>
                                    <input type="radio" name="status" value="0" <?php echo ($product['Status'] == 0) ? 'checked' : ''; ?>> Ẩn
                                </label>
                            </div>
                            <button type="submit" name="submit" class="btn btn-success mt-4">Cập nhật</button>
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
