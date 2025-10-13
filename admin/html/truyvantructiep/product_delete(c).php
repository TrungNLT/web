<?php
    include ($_SERVER['DOCUMENT_ROOT'] . "/database/connect.php");

    if(isset($_GET['id'])){
      $id = $_GET['id'];
    }

    $query = "DELETE FROM Products WHERE ProductId = $id";
    $delete = mysqli_query($conn, $query);
    
    if ($delete) {
        header('Location: product_list.php');
        exit(); // Đảm bảo dừng mã sau khi chuyển hướng
    } else {
        // Hiển thị lỗi chi tiết
        echo "Xảy ra lỗi khi xóa: " . mysqli_error($conn);
    }
?>