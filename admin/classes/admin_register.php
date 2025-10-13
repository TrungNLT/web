<?php
include($_SERVER['DOCUMENT_ROOT'] . "/lib/session.php"); // Import class Session
include($_SERVER['DOCUMENT_ROOT'] . "/lib/database.php");
include($_SERVER['DOCUMENT_ROOT'] . "/helpers/format.php");
?>

<?php
class admin_register
{
    private $db;
    private $fm;

    public function __construct()
    {
        $this->db = new Database();
        $this->fm = new Format();
    }

    public function insert_Admin($data) {
        $username = mysqli_real_escape_string($this->db->link, $data['Username']);
        $email = mysqli_real_escape_string($this->db->link, $data['Email']);
        $password = mysqli_real_escape_string($this->db->link, $data['Password']);
        $hashedPassword = md5($password); // Mã hóa mật khẩu bằng MD5

        if (empty($username) || empty($email) || empty($password)) {
            return "Vui lòng điền đầy đủ thông tin!";
        } else {
            $query = "INSERT INTO admin (Username, Email, Password, Role) VALUES ('$username', '$email', '$hashedPassword', 2)";
            $result = $this->db->insert($query);

            if ($result) {
                return "Đăng ký thành công!";
            } else {
                return "Đăng ký thất bại!";
            }
        }
    }
}

?>