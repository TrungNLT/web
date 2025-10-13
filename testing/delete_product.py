from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
import time

def delete_product():
    options = Options()
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-gpu")
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)
    driver.maximize_window()

    driver.get("http://localhost/admin/html/product_add.php")

    email_input = driver.find_element(By.ID, "email")
    email_input.send_keys("tannhut2111@gmail.com")
    
    password_input = driver.find_element(By.ID, "password")
    password_input.send_keys("12345")
    
    sign_in_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
    sign_in_button.click()

    print("Đăng nhập thành công!")

    try:
        # Duyệt qua tất cả 5 trang
        for page in range(1, 6):
            print(f"Đang kiểm tra trang {page}...")
            
            # Tìm menu của trang hiện tại
            products_menu = driver.find_element(By.XPATH, f"//a[@href='product_list.php?page={page}']")
            driver.execute_script("arguments[0].scrollIntoView(true);", products_menu)
            WebDriverWait(driver, 10).until(EC.element_to_be_clickable(products_menu))
            driver.execute_script("arguments[0].click();", products_menu)

            WebDriverWait(driver, 10).until(EC.url_contains("product_list.php"))
            time.sleep(2)  # Đợi để trang tải

            # Kiểm tra liên kết xóa sản phẩm
            try:
                delete_link = WebDriverWait(driver, 5).until(
                    EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'product_delete.php?id=69') and @style='color: white']"))
                )
                # Cuộn đến liên kết và click
                driver.execute_script("arguments[0].scrollIntoView(true);", delete_link)
                delete_link.click()

                # Chấp nhận thông báo cảnh báo
                WebDriverWait(driver, 5).until(EC.alert_is_present())
                alert = driver.switch_to.alert
                alert.accept()  # Xác nhận xóa
                time.sleep(5)
                # Chờ alert thông báo "đã xóa thành công" và đóng nó
                WebDriverWait(driver, 5).until(EC.alert_is_present())  # Chờ alert xuất hiện
                success_alert = driver.switch_to.alert
                success_alert.accept()  # Đóng alert thông báo thành công
                time.sleep(5)

                print(f"Sản phẩm đã được xóa thành công trên trang {page}!")
                break  # Nếu tìm thấy sản phẩm và xóa thành công, dừng lại
            except Exception as e:
                print(f"Không tìm thấy sản phẩm cần xóa trên trang {page}.")
                continue

    except Exception as e:
        print("Có lỗi xảy ra:", e)

    finally:
        driver.quit()

delete_product()
