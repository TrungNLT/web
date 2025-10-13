from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import Select
from webdriver_manager.chrome import ChromeDriverManager
import time
import openpyxl

# Hàm ghi log vào Excel
def log_to_excel(level, message):
    file_path = "log.xlsx"
    try:
        # Tạo hoặc mở file Excel
        workbook = openpyxl.load_workbook(file_path)
    except FileNotFoundError:
        workbook = openpyxl.Workbook()
        sheet = workbook.active
        sheet.append(["Level", "Message", "Timestamp"])
    
    sheet = workbook.active
    timestamp = time.strftime("%Y-%m-%d %H:%M:%S", time.gmtime())
    sheet.append([level, message, timestamp])
    workbook.save(file_path)

def check_product_exists(driver, product_name):
    # Duyệt qua các trang từ 1 đến 5
    for page in range(1, 6):
        driver.get(f"http://localhost/admin/html/product_list.php?page={page}")
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//table[contains(@class, 'table')]")))

        # Kiểm tra nếu tên sản phẩm đã có trong danh sách
        try:
            products = driver.find_elements(By.XPATH, "//table[contains(@class, 'table')]//tr/td[2]")
            for product in products:
                if product_name.lower() in product.text.lower():
                    log_to_excel("INFO", f"Sản phẩm '{product_name}' đã tồn tại trên trang {page}.")
                    return True 
        except Exception as e:
            print(f"Error checking products on page {page}: {e}")
    
    return False

def add_product():
    options = Options()
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-gpu")
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)
    driver.maximize_window()

    try:
        driver.get("http://localhost/admin/html/product_add.php")
        log_to_excel("INFO", "Truy cập vào trang đăng nhập thành công.")

        email_input = driver.find_element(By.ID, "email")
        email_input.send_keys("ngogiakhanh005@gmail.com")
        log_to_excel("INFO", "Đã nhập email: tannhut2111@gmail.com")

        password_input = driver.find_element(By.ID, "password")
        password_input.send_keys("12345")
        log_to_excel("INFO", "Đã nhập mật khẩu.")

        sign_in_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        sign_in_button.click()
        log_to_excel("INFO", "Đã click vào nút Đăng nhập.")

        log_to_excel("INFO", "Đăng nhập thành công!")

        products_menu = driver.find_element(By.XPATH, "//a[@href='product_list.php?page=1']")
        driver.execute_script("arguments[0].scrollIntoView(true);", products_menu)
        WebDriverWait(driver, 10).until(EC.element_to_be_clickable(products_menu))
        driver.execute_script("arguments[0].click();", products_menu)
        log_to_excel("INFO", "Đã truy cập vào menu sản phẩm.")

        # Nhập tên sản phẩm
        product_name = "Nhựt"

        # Kiểm tra xem sản phẩm đã tồn tại hay chưa
        if check_product_exists(driver, product_name):
            log_to_excel("INFO", f"Sản phẩm '{product_name}' đã tồn tại, không thêm mới.")
            return 
        
        add_product_button = driver.find_element(By.XPATH, "//a[@href='product_add.php' and @style='color: white']")
        driver.execute_script("arguments[0].scrollIntoView(true);", add_product_button)
        WebDriverWait(driver, 10).until(EC.element_to_be_clickable(add_product_button))
        driver.execute_script("arguments[0].click();", add_product_button)
        log_to_excel("INFO", "Đã click vào nút thêm sản phẩm.")

        # Điền thông tin sản phẩm
        try:
            name_input = driver.find_element(By.ID, "name")
            name_input.send_keys(product_name)
            log_to_excel("INFO", f"Đã nhập tên sản phẩm: {product_name}.")
        except Exception as e:
            log_to_excel("ERROR", f"Lỗi khi nhập tên sản phẩm: {e}")

        # Chọn thương hiệu
        try:
            brand_select = driver.find_element(By.NAME, "id_brands")
            select = Select(brand_select)
            select.select_by_value("96")  # Thay thế bằng giá trị đúng
            product_info_brand = select.first_selected_option.text
            log_to_excel("INFO", f"Đã chọn thương hiệu: {product_info_brand}")
        except Exception as e:
            log_to_excel("ERROR", f"Lỗi khi chọn thương hiệu: {e}")

        # Chọn danh mục
        try:
            category_select = driver.find_element(By.NAME, "id_categories")
            select = Select(category_select)
            select.select_by_value("2")  # Thay thế bằng giá trị đúng
            product_info_category = select.first_selected_option.text
            log_to_excel("INFO", f"Đã chọn danh mục: {product_info_category}")
        except Exception as e:
            log_to_excel("ERROR", f"Lỗi khi chọn danh mục: {e}")

        # Chọn hình ảnh
        try:
            image_input = driver.find_element(By.ID, "image")
            image_input.send_keys("C:/xampp/htdocs/Cake-Sale-Website/admin/uploads/1aba114354.product-big-4.jpg")
            log_to_excel("INFO", "Đã chọn hình ảnh sản phẩm.")
        except Exception as e:
            log_to_excel("ERROR", f"Lỗi khi chọn hình ảnh sản phẩm: {e}")

        # Nhập số lượng nhập
        try:
            quantity_in_input = driver.find_element(By.ID, "quantity")
            quantity_in_input.send_keys("100")
            log_to_excel("INFO", "Đã nhập số lượng nhập sản phẩm.")
        except Exception as e:
            log_to_excel("ERROR", f"Lỗi khi điền số lượng nhập sản phẩm: {e}")
        
        # Nhập số lượng bán
        try:
            quantity_sold_input = driver.find_element(By.ID, "avaiable_quantity")
            quantity_sold_input.send_keys("80")
            log_to_excel("INFO", "Đã nhập số lượng bán sản phẩm.")
        except Exception as e:
            log_to_excel("ERROR", f"Lỗi khi điền số lượng bán sản phẩm: {e}")

        # Nhập giá nhập
        try:
            buy_price_input = driver.find_element(By.ID, "buy_price")
            buy_price_input.send_keys("100000") 
            log_to_excel("INFO", "Đã nhập giá nhập sản phẩm.")
        except Exception as e:
            log_to_excel("ERROR", f"Lỗi khi điền giá nhập sản phẩm: {e}")

        # Nhập giá bán
        try:
            sell_price_input = driver.find_element(By.ID, "sell_price")
            sell_price_input.send_keys("120000")  # Nhập giá bán
            log_to_excel("INFO", "Đã nhập giá bán sản phẩm.")
        except Exception as e:
            log_to_excel("ERROR", f"Lỗi khi điền giá bán sản phẩm: {e}")

        # Nhập mô tả
        try:
            description_input = driver.find_element(By.ID, "description")
            description_input.send_keys("Bánh kem ngon.")
            log_to_excel("INFO", "Đã nhập mô tả sản phẩm.")
        except Exception as e:
            log_to_excel("ERROR", f"Lỗi khi nhập mô tả sản phẩm: {e}")
        
        # Nhấn Submit
        submit_button = driver.find_element(By.NAME, "submit")
        driver.execute_script("arguments[0].click();", submit_button)
        log_to_excel("INFO", "Đã nhấn nút thêm sản phẩm.")

        # Kiểm tra nếu sản phẩm được thêm thành công
        WebDriverWait(driver, 10).until(EC.url_contains("product_list.php"))
        log_to_excel("INFO", "Sản phẩm đã được thêm thành công!")

    except Exception as e:
        log_to_excel("ERROR", f"Có lỗi xảy ra: {e}")
    
    finally:
        driver.quit()

if __name__ == "__main__":
    add_product()
