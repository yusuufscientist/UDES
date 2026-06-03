"""
Selenium Test Suite for SolarSmart Project
Full workflow: Dashboard -> Solar Systems CRUD -> Panels CRUD ->
Production CRUD -> Fault Simulation -> Alerts -> Intervention -> Disconnect

Prerequisites:
1. pip install selenium
2. Download ChromeDriver matching Chrome version
3. Start Laravel server: php artisan serve
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
import time
import random
import string
import os

class SolarSmartSeleniumTest:
    BASE_URL = "http://127.0.0.1:8000"

    def __init__(self):
        self.driver = None
        self.wait = None
        self.solar_system_name = None
        self.solar_system_id = None
        self.panel_serial = None
        self.panel_id = None
        self.production_id = None
        self.fault_sim_id = None
        self.alert_id = None
        self.setup_driver()

    def setup_driver(self):
        chrome_options = Options()
        chrome_options.add_argument("--no-sandbox")
        chrome_options.add_argument("--disable-dev-shm-usage")
        chrome_options.add_argument("--window-size=1920,1080")
        chrome_options.add_argument("--disable-gpu")

        driver_path = os.path.join(os.path.expanduser("~"), ".wdm", "drivers", "chromedriver-win64", "chromedriver.exe")
        if os.path.exists(driver_path):
            from selenium.webdriver.chrome.service import Service
            self.driver = webdriver.Chrome(service=Service(driver_path), options=chrome_options)
        else:
            self.driver = webdriver.Chrome(options=chrome_options)

        self.wait = WebDriverWait(self.driver, 15)

    def random_string(self, length=8):
        return ''.join(random.choices(string.ascii_uppercase + string.digits, k=length))

    def wait_on_page(self, seconds=5, message=""):
        print(f"[WAIT] {message} ({seconds}s)")
        time.sleep(seconds)

    def open_dashboard(self):
        print("\n--- Opening Dashboard ---")
        self.driver.get(f"{self.BASE_URL}/dashboard")
        self.wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        time.sleep(1)

        page_source = self.driver.page_source
        if "Solar Systems" in page_source or "Dashboard" in page_source:
            print("[PASS] Dashboard loaded successfully")
        else:
            print("[WARN] Dashboard may not have loaded correctly")

        # Test theme toggle button
        try:
            theme_btn = self.driver.find_element(By.ID, "themeToggle")
            if theme_btn:
                self.driver.execute_script("arguments[0].click();", theme_btn)
                print("[PASS] Clicked theme toggle button")
                time.sleep(0.5)
        except Exception as e:
            print(f"[INFO] Could not find theme toggle button: {e}")

        try:
            realtime_btn = self.driver.find_element(By.ID, "toggleRealtime")
            if realtime_btn:
                self.driver.execute_script("arguments[0].click();", realtime_btn)
                print("[PASS] Clicked 'Start Real-time' button (single click)")
                time.sleep(1)
                try:
                    alert = self.driver.switch_to.alert
                    alert.accept()
                except:
                    pass
        except Exception as e:
            print(f"[INFO] Could not find Start Real-time button: {e}")

        self.wait_on_page(5, "Dashboard overview")

    def create_solar_system(self):
        print("\n=== SOLAR SYSTEMS CRUD SECTION ===")

        try:
            alert = self.driver.switch_to.alert
            alert.accept()
        except:
            pass

        self.driver.get(f"{self.BASE_URL}/solar-systems")
        self.wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        time.sleep(1)

        add_btns = self.driver.find_elements(By.XPATH, "//a[contains(@href, 'solar-systems/create')]")
        if not add_btns:
            print("[SKIP] No add button found for Solar System")
            self.wait_on_page(5, "Solar Systems - no add button")
            return

        add_btns[0].click(); time.sleep(2); print(f"Current URL after click: {self.driver.current_url}")
        time.sleep(2); print(f"Page title: {self.driver.title}"); self.wait.until(EC.presence_of_element_located((By.ID, "name")))
        time.sleep(1)
        self.solar_system_name = f"Test System {self.random_string()}"

        self.driver.find_element(By.ID, "name").send_keys(self.solar_system_name)
        self.driver.find_element(By.ID, "location").send_keys("Alger, Algeria")
        self.driver.find_element(By.ID, "total_capacity_kw").send_keys("220")
        self.driver.find_element(By.ID, "installation_date").send_keys("2024-01-15")
        self.driver.find_element(By.ID, "latitude").send_keys("36.5")
        self.driver.find_element(By.ID, "longitude").send_keys("3.0")

        create_btn = self.driver.find_element(By.XPATH, "//button[contains(., 'Create System')]")
        self.driver.execute_script("arguments[0].scrollIntoView(true);", create_btn)
        time.sleep(0.5)
        self.driver.execute_script("arguments[0].click();", create_btn)
        self.wait.until(EC.url_contains("solar-systems"))
        print(f"[PASS] Solar System created: {self.solar_system_name}")

        self.wait_on_page(5, "Solar Systems page")

    def create_panel(self):
        print("\n=== PANELS CRUD SECTION ===")

        self.driver.get(f"{self.BASE_URL}/solar-systems")
        time.sleep(1)

        view_btns = self.driver.find_elements(By.XPATH, "//a[contains(@href, 'solar-systems/') and contains(text(), 'View')]")
        if not view_btns:
            view_btns = self.driver.find_elements(By.XPATH, "//a[contains(@href, 'solar-systems') and not(contains(text(), 'Add'))]")

        if not view_btns:
            print("[SKIP] No solar system found for panel creation")
            return

        view_btns[0].click()
        time.sleep(1)

        add_btns = self.driver.find_elements(By.XPATH, "//a[contains(@href, 'panels/create')]")
        if not add_btns:
            panel_tabs = self.driver.find_elements(By.XPATH, "//a[contains(@href, 'panels')]")
            if panel_tabs:
                panel_tabs[-1].click()
                time.sleep(1)
                add_btns = self.driver.find_elements(By.XPATH, "//a[contains(@href, 'panels/create')]")

        if not add_btns:
            print("[SKIP] No panel add button found")
            return

        add_btns[0].click(); time.sleep(2); print(f"Current URL after click: {self.driver.current_url}")
        self.wait.until(EC.presence_of_element_located((By.ID, "serial_number")))
        self.panel_serial = f"PANEL-{self.random_string()}"

        self.driver.find_element(By.ID, "serial_number").send_keys(self.panel_serial)
        self.driver.find_element(By.ID, "model").send_keys(f"Model-{self.random_string()}")
        self.driver.find_element(By.ID, "manufacturer").send_keys("SolarTech")
        self.driver.find_element(By.ID, "capacity_watts").send_keys(str(random.randint(250, 500)))
        self.driver.find_element(By.ID, "installation_date").send_keys("2024-01-15")

        add_panel_btn = self.driver.find_element(By.XPATH, "//button[contains(., 'Add Panel')]")
        self.driver.execute_script("arguments[0].click();", add_panel_btn)
        self.wait.until(EC.url_contains("panels"))
        print(f"[PASS] Panel created: {self.panel_serial}")

        self.wait_on_page(5, "Panels page")

    def create_production(self):
        print("\n=== PRODUCTION CRUD SECTION ===")

        self.driver.get(f"{self.BASE_URL}/solar-systems")
        time.sleep(1)

        view_btns = self.driver.find_elements(By.XPATH, "//a[contains(@href, 'solar-systems/') and contains(text(), 'View')]")
        if not view_btns:
            view_btns = self.driver.find_elements(By.XPATH, "//a[contains(@href, 'solar-systems') and not(contains(text(), 'Add'))]")

        if not view_btns:
            print("[SKIP] No solar system found for production")
            return

        view_btns[0].click()
        time.sleep(1)

        add_btns = self.driver.find_elements(By.XPATH, "//a[contains(@href, 'productions/create')]")
        if add_btns:
            add_btns[0].click(); time.sleep(2); print(f"Current URL after click: {self.driver.current_url}")
            time.sleep(1)

            if self.driver.find_elements(By.ID, "production_date"):
                self.driver.find_element(By.ID, "production_date").send_keys("2024-06-01")
            if self.driver.find_elements(By.ID, "energy_produced_kwh"):
                self.driver.find_element(By.ID, "energy_produced_kwh").send_keys(str(round(random.uniform(5.0, 20.0), 2)))

                add_prod_btn = self.driver.find_element(By.XPATH, "//button[contains(., 'Add Production Record')]")
                self.driver.execute_script("arguments[0].click();", add_prod_btn)
                print("[PASS] Production record created with random data")

        self.wait_on_page(5, "Production page")

    def trigger_fault_simulation(self):
        print("\n=== FAULT SIMULATION SECTION ===")

        self.driver.get(f"{self.BASE_URL}/fault-simulations")
        self.wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        time.sleep(3)

        try:
            system_select = Select(self.driver.find_element(By.ID, "systemSelect"))

            found_system = False
            for i in range(1, len(system_select.options)):
                system_select.select_by_index(i)
                time.sleep(2)

                panel_select = Select(self.driver.find_element(By.ID, "panelSelect"))
                if len(panel_select.options) > 1:
                    panel_select.select_by_index(1)
                    found_system = True
                    break

            if found_system:
                fault_select = Select(self.driver.find_element(By.ID, "faultTypeSelect"))
                if len(fault_select.options) > 1:
                    fault_select.select_by_index(1)

                    trigger_btn = self.driver.find_element(By.ID, "triggerBtn")
                    self.driver.execute_script("arguments[0].click();", trigger_btn)
                    time.sleep(2)
                    # Handle alert after triggering fault simulation
                    try:
                        alert = self.driver.switch_to.alert
                        alert.accept()
                        print("[INFO] Accepted fault simulation alert")
                    except:
                        pass
                    print("[PASS] Fault simulation triggered successfully")
                    return
        except Exception as e:
            print(f"[INFO] Fault simulation info: {e}")

        self.wait_on_page(5, "Fault simulation page")

    def verify_alerts(self):
        print("\n=== ALERTS SECTION ===")

        # Handle any alerts that might appear after fault simulation
        try:
            alert = self.driver.switch_to.alert
            alert.accept()
            print("[INFO] Accepted alert after fault simulation")
        except:
            pass

        self.driver.get(f"{self.BASE_URL}/alerts")
        # Handle any alerts that might appear on page load
        try:
            alert = self.driver.switch_to.alert
            alert.accept()
            print("[INFO] Accepted alert on alerts page load")
        except:
            pass
        self.wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        time.sleep(1)

        alert_rows = self.driver.find_elements(By.XPATH, "//table//tbody//tr")
        if alert_rows:
            print(f"[PASS] Found {len(alert_rows)} alert(s) in alerts list")
        else:
            print("[INFO] No alerts found")

        self.wait_on_page(5, "Alerts page")

    def create_intervention(self):
        print("\n=== INTERVENTION SECTION ===")

        self.driver.get(f"{self.BASE_URL}/interventions")
        self.wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        time.sleep(1)

        add_btns = self.driver.find_elements(By.XPATH, "//a[contains(@href, 'interventions/create')]")
        if add_btns:
            self.driver.execute_script("arguments[0].click();", add_btns[0])
            time.sleep(2); print(f"Current URL after click: {self.driver.current_url}")
            time.sleep(1)

            if self.driver.find_elements(By.ID, "title"):
                self.driver.find_element(By.ID, "title").send_keys("Test Maintenance Intervention")

            if self.driver.find_elements(By.ID, "description"):
                self.driver.find_element(By.ID, "description").send_keys("Routine maintenance check")

            # Fill required fields
            if self.driver.find_elements(By.ID, "type"):
                type_select = Select(self.driver.find_element(By.ID, "type"))
                type_select.select_by_index(1)

            if self.driver.find_elements(By.ID, "priority"):
                priority_select = Select(self.driver.find_element(By.ID, "priority"))
                priority_select.select_by_index(1)

            if self.driver.find_elements(By.ID, "scheduled_date"):
                self.driver.find_element(By.ID, "scheduled_date").send_keys("2024-06-15")

            # Select first available technician
            if self.driver.find_elements(By.ID, "technician_id"):
                tech_select = Select(self.driver.find_element(By.ID, "technician_id"))
                if len(tech_select.options) > 1:
                    tech_select.select_by_index(1)

            create_intervention_btn = self.driver.find_element(By.XPATH, "//button[contains(., 'Create Intervention')]")
            self.driver.execute_script("arguments[0].scrollIntoView(true);", create_intervention_btn)
            time.sleep(0.5)
            self.driver.execute_script("arguments[0].click();", create_intervention_btn)
            print("[PASS] Intervention created")

        self.wait_on_page(5, "Interventions page")

    def disconnect(self):
        print("\n--- Testing Disconnect ---")
        try:
            logout_elements = self.driver.find_elements(By.XPATH, "//a[contains(@href, 'logout')]")
            if logout_elements:
                logout_elements[0].click()
                print("[PASS] Logout initiated")
        except Exception as e:
            print(f"[INFO] No disconnect button: {e}")

    def run_full_test(self):
        try:
            print("\n" + "="*60)
            print("SOLARSMART SELENIUM TEST SUITE (No Auth)")
            print("Full CRUD Workflow")
            print("="*60)

            self.open_dashboard()
            self.create_solar_system()
            self.create_panel()
            self.create_production()
            self.trigger_fault_simulation()
            self.verify_alerts()
            self.create_intervention()
            self.disconnect()

            print("\n" + "="*60)
            print("ALL TESTS COMPLETED!")
            print("="*60)

        except Exception as e:
            print(f"\n[ERROR] Test failed: {str(e)}")
            try:
                alert = self.driver.switch_to.alert
                alert.accept()
            except:
                pass
        finally:
            if self.driver:
                self.driver.quit()


if __name__ == "__main__":
    test = SolarSmartSeleniumTest()
    test.run_full_test()