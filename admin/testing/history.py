import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

def fetch_order_history():
    options = Options()
    options.add_argument("--no-sandbox")
    # options.add_argument("--headless")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-gpu")
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)

    driver.get("http://localhost/cake-main/history_order.php")

    time.sleep(3)

    try:
        login_alert = driver.find_element(By.CLASS_NAME, "alert-danger")
        if login_alert.is_displayed():
            print("Vui lòng đăng nhập để xem lịch sử mua hàng.")
            login_button = driver.find_element(By.LINK_TEXT, "Đăng nhập")
            login_button.click()
            print("Đã click vào nút Đăng nhập.")
            time.sleep(2)
            
            # Điền email và mật khẩu
            email_input = driver.find_element(By.XPATH, "//input[@name='Email']")
            email_input.send_keys("tannhut2111@gmail.com")
            password_input = driver.find_element(By.NAME, "Password")
            password_input.send_keys("12345")
            
            # Bấm nút "Login"
            submit_button = driver.find_element(By.XPATH, "//button[@type='submit' and text()='Login']")
            submit_button.click()
            time.sleep(2)

            # Wait for the "Lịch sử" link to be clickable
            history_link = WebDriverWait(driver, 10).until(
                EC.element_to_be_clickable((By.LINK_TEXT, "Lịch sử"))
            )

            # Scroll to the element if needed and click it
            driver.execute_script("arguments[0].scrollIntoView();", history_link)
            history_link.click()

            time.sleep(10)
        
        # If already logged in, process orders
        orders = driver.find_elements(By.CSS_SELECTOR, "table.table tbody tr")

        for order in orders:
            product_name = order.find_element(By.CSS_SELECTOR, "td:nth-child(2)").text
            order_date = order.find_element(By.CSS_SELECTOR, "td:nth-child(4)").text
            quantity = order.find_element(By.CSS_SELECTOR, "td:nth-child(5)").text
            price = order.find_element(By.CSS_SELECTOR, "td:nth-child(6)").text
            total_price = order.find_element(By.CSS_SELECTOR, "td:nth-child(7)").text
            status = order.find_element(By.CSS_SELECTOR, "td:nth-child(8)").text

            print(f"Sản phẩm: {product_name}, Ngày đặt hàng: {order_date}, Số lượng: {quantity}, Giá: {price}, Thành tiền: {total_price}, Trạng thái: {status}")

    except Exception as e:
        None

    driver.quit()
fetch_order_history()
