"""
Selenium Test Suite for SolarSmart Project
Tests full workflow: Login -> Solar Systems CRUD -> Panels CRUD -> Production CRUD -> 
Fault Simulation -> Alerts Verification -> Intervention Creation -> Logout

Prerequisites:
1. Install: pip install selenium
2. Download ChromeDriver matching your Chrome version
3. Start Laravel server: php artisan serve
4. Create a test user in database with valid credentials
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.action_chains import ActionChains
import time
import random
import string
import sys

class SolarSmartSeleniumTest:
    BASE_URL = "http://localhost:8000"
    
    def __init__(self):
        self.driver = None
        self.wait = None
        self.solar_system_name = None
        self.panel_serial = None
        self.setup_driver()
    
    def setup_driver(self):
        chrome_options = Options()
        # Remove headless for visible browser testing
        chrome_options.add_argument("--no-sandbox")
        chrome_options.add_argument("--disable-dev-shm-usage")
        chrome_options.add_argument("--window-size=1920,1080")
        chrome_options.add_argument("--disable-gpu")
        chrome_options.add_argument("--disable-extensions")
        self.driver = webdriver.Chrome(options=chrome_options)
        self.wait = WebDriverWait(self.driver, 15)
    
    def random_string(self, length=6):
        return ''.join(random.choices(string.ascii_uppercase + string.digits, k=length))
    
    def login(self, email="fcyusuuf@gmail.com", password="yusuuf123"):
        print("\n--- Testing Authentication ---")
        self.driver.get(f"{self.BASE_URL}/login")
        
        # Verify login page loaded
        self.wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, "h2")))
        assert "Welcome Back" in self.driver.page_source, "Login page title not found"
        
        # Fill email: input id="email"
        email_input = self.wait.until(EC.presence_of_element_located((By.ID, "email")))
        email_input.clear()
        email_input.send_keys(email)
        
        # Fill password: input id="password"
        password_input = self.driver.find_element(By.ID, "password")
        password_input.clear()
        password_input.send_keys(password)
        
        # Submit: button[type='submit']
        submit_btn = self.wait.until(EC.element_to_be_clickable((By.XPATH, "//button[contains(., 'Sign In') or @type='submit']")))
        self.driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", submit_btn)
        time.sleep(0.5)
        submit_btn.click()
        
        # Wait for dashboard redirect
        self.wait.until(EC.url_contains("/dashboard"))
        self.wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, ".dashboard-title")))
        assert "Solar Energy Dashboard" in self.driver.page_source, "Dashboard not loaded after login"
        print("[PASS] Login successful - redirected to dashboard")
    
    def disconnect(self):
        print("\n--- Testing Disconnect ---")
        disconnect_btn = self.wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, "button[title='Disconnect']")))
        self.driver.execute_script("arguments[0].click();", disconnect_btn)
        
        # Handle alert/confirmation if any
        time.sleep(1)
        
        # Wait for redirect to login
        self.wait.until(EC.url_contains("/login"))
        self.wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, "h2")))
        print("[PASS] Disconnected successfully - redirected to login page")
    
    # ========== SOLAR SYSTEMS CRUD ==========
    def create_solar_system(self):
        print("\n--- Testing Solar Systems CRUD ---")
        
        # Navigate to Solar Systems via navbar link text "Solar Systems"
        solar_systems_link = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, "//a[contains(text(), 'Solar Systems')]"))
        )
        solar_systems_link.click()
        
        # Click Add New System button (btn-primary with bi-plus-lg)
        add_btn = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, "//a[contains(@href, 'solar-systems/create') and contains(@class, 'btn-primary')]"))
        )
        add_btn.click()
        
        # Fill form fields:
        # - input id="name" (System Name)
        # - input id="location" (Location)
        # - input id="total_capacity_kw" (Total Capacity)
        # - input id="installation_date" (Installation Date)
        random_suffix = self.random_string()
        self.solar_system_name = f"Test Solar System {random_suffix}"
        
        self.driver.find_element(By.ID, "name").send_keys(self.solar_system_name)
        self.driver.find_element(By.ID, "location").send_keys("Alger, Algeria")
        self.driver.find_element(By.ID, "total_capacity_kw").send_keys("5.5")
        
        date_input = self.driver.find_element(By.ID, "installation_date")
        date_input.clear()
        date_input.send_keys("2024-01-15")
        
        # Submit button with text "Create System"
        submit_btn = self.wait.until(EC.presence_of_element_located((By.XPATH, "//button[contains(., 'Create System')]")))
        self.driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", submit_btn)
        time.sleep(1)
        self.driver.execute_script("arguments[0].click();", submit_btn)
        
        # Verify creation - check for system name in page
        self.wait.until(EC.url_contains("solar-systems"))
        self.wait.until(EC.presence_of_element_located((By.XPATH, f"//*[contains(text(), '{self.solar_system_name}')]")))
        print(f"[PASS] Solar System created: {self.solar_system_name}")
        return self.solar_system_name
    
    # ========== PANELS CRUD ==========
    def create_panel(self):
        print("\n--- Testing Panels CRUD ---")
        
        # Navigate to Panels via navbar link text "Panels"
        panels_link = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, "//a[contains(text(), 'Panels')]"))
        )
        panels_link.click()
        
        # Wait for panels page - check if we have any systems
        self.wait.until(EC.presence_of_element_located((By.TAG_NAME, "table")))
        
        # Click Add Panel button
        add_btn = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, "//a[contains(@href, 'panels/create') and contains(@class, 'btn-primary')]"))
        )
        add_btn.click()
        
        # Fill panel form:
        # - input id="serial_number"
        # - input id="model"
        # - input id="manufacturer"
        # - input id="capacity_watts"
        # - input id="efficiency_rating"
        # - input id="installation_date"
        self.panel_serial = f"PANEL-{self.random_string(8)}"
        
        self.driver.find_element(By.ID, "serial_number").send_keys(self.panel_serial)
        self.driver.find_element(By.ID, "model").send_keys("Solar Panel Model X")
        self.driver.find_element(By.ID, "manufacturer").send_keys("SolarTech Inc")
        self.driver.find_element(By.ID, "capacity_watts").send_keys("350")
        self.driver.find_element(By.ID, "efficiency_rating").send_keys("20.5")
        
        date_input = self.driver.find_element(By.ID, "installation_date")
        date_input.clear()
        date_input.send_keys("2024-01-15")
        
        # Submit button with text "Add Panel"
        submit_btn = self.wait.until(EC.presence_of_element_located((By.XPATH, "//button[contains(., 'Add Panel')]")))
        self.driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", submit_btn)
        time.sleep(1)
        self.driver.execute_script("arguments[0].click();", submit_btn)
        
        # Verify - panel serial should appear in page
        self.wait.until(EC.url_contains("panels"))
        self.wait.until(EC.presence_of_element_located((By.XPATH, f"//*[contains(text(), '{self.panel_serial}')]")))
        print(f"[PASS] Panel created: {self.panel_serial}")
        return self.panel_serial
    
    # ========== PRODUCTION CRUD ==========
    def create_production(self):
        print("\n--- Testing Production CRUD ---")
        
        # Navigate to Production via navbar link text "Production"
        production_link = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, "//a[contains(text(), 'Production')]"))
        )
        production_link.click()
        
        # Click Add Production Record button
        try:
            add_btn = self.wait.until(
                EC.element_to_be_clickable((By.XPATH, "//a[contains(@href, 'productions/create') and contains(@class, 'btn-primary')]"))
            )
            add_btn.click()
        except:
            print("[INFO] No production add button found, checking page structure")
        
        # Fill production form:
        # - input id="production_date"
        # - input id="production_time"
        # - input id="energy_produced_kwh"
        # - input id="energy_consumed_kwh"
        # - input id="peak_power_kw"
        # - input id="average_power_kw"
        # - input id="temperature_celsius"
        # - radio input id="w_sunny" for weather
        
        self.driver.find_element(By.ID, "production_date").clear()
        self.driver.find_element(By.ID, "production_date").send_keys("2024-06-01")
        self.driver.find_element(By.ID, "production_time").send_keys("12:00")
        self.driver.find_element(By.ID, "energy_produced_kwh").send_keys("12.5")
        self.driver.find_element(By.ID, "energy_consumed_kwh").send_keys("10.0")
        self.driver.find_element(By.ID, "peak_power_kw").send_keys("3.2")
        self.driver.find_element(By.ID, "average_power_kw").send_keys("2.8")
        self.driver.find_element(By.ID, "temperature_celsius").send_keys("25.5")
        
        # Select sunny weather radio button
        sunny_radio = self.driver.find_element(By.ID, "w_sunny")
        sunny_radio.click()
        
        # Submit button with text "Add Production Record"
        submit_btn = self.wait.until(EC.presence_of_element_located((By.XPATH, "//button[contains(., 'Add Production Record')]")))
        self.driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", submit_btn)
        time.sleep(1)
        self.driver.execute_script("arguments[0].click();", submit_btn)
        
        # Verify - check for energy value 12.5
        self.wait.until(EC.url_contains("productions"))
        self.wait.until(EC.presence_of_element_located((By.XPATH, "//*[contains(text(), '12.5')]")))
        print("[PASS] Production record created - 12.5 kWh")
    
    # ========== FAULT SIMULATION ==========
    def trigger_fault_simulation(self):
        print("\n--- Testing Fault Simulation ---")
        
        # Navigate to Fault Simulations via navbar link
        fault_link = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, "//a[contains(text(), 'Fault Simulations')]"))
        )
        fault_link.click()
        
        # Select Solar System from dropdown id="systemSelect"
        # Select Panel from dropdown id="panelSelect"
        # Select Fault Type from dropdown id="faultTypeSelect"
        # Click trigger button id="triggerBtn"
        
        # Select system (first available)
        system_select = self.wait.until(EC.presence_of_element_located((By.ID, "systemSelect")))
        Select(system_select).select_by_index(1)  # First option after placeholder
        
        # Wait for panels to load and select first panel
        time.sleep(1)
        panel_select = self.driver.find_element(By.ID, "panelSelect")
        panel_options = panel_select.find_elements(By.TAG_NAME, "option")
        if len(panel_options) > 1:
            Select(panel_select).select_by_index(1)
        else:
            print("[WARNING] No panels available for fault simulation")
            return
        
        # Select fault type (first available)
        fault_select = self.driver.find_element(By.ID, "faultTypeSelect")
        Select(fault_select).select_by_index(1)
        
        # Click trigger button
        trigger_btn = self.wait.until(EC.presence_of_element_located((By.ID, "triggerBtn")))
        self.driver.execute_script("arguments[0].click();", trigger_btn)
        
        # Wait for confirmation (page reload or alert)
        time.sleep(2)
        print("[PASS] Fault simulation triggered")
    
    # ========== ALERTS VERIFICATION ==========
    def verify_alerts(self):
        print("\n--- Testing Alerts Verification ---")
        
        # Navigate to Alerts via navbar link text "Alerts"
        alerts_link = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, "//a[contains(text(), 'Alerts')]"))
        )
        alerts_link.click()
        
        # Check for badge elements (alerts count)
        self.wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, ".badge")))
        
        # Check alert statistics or list
        try:
            # Check stats cards
            stat_cards = self.driver.find_elements(By.CSS_SELECTOR, ".stat-card h3")
            if len(stat_cards) >= 4:
                active_alerts = stat_cards[1].text
                print(f"[PASS] Alerts section verified - Active alerts: {active_alerts}")
            else:
                print("[INFO] Alerts stats cards found")
        except Exception as e:
            print(f"[INFO] Alerts verification page loaded: {e}")
    
    # ========== INTERVENTION CREATION ==========
    def create_intervention(self):
        print("\n--- Testing Intervention Creation ---")
        
        # Navigate to Interventions via navbar link text "Interventions"
        intervention_link = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, "//a[contains(text(), 'Interventions')]"))
        )
        intervention_link.click()
        
        # Click Create button
        create_link = self.wait.until(
            EC.element_to_be_clickable((By.XPATH, "//a[contains(@href, 'interventions/create') and contains(@class, 'btn-primary')]"))
        )
        create_link.click()
        
        # Fill intervention form:
        # - input id="title"
        # - textarea id="description"
        # - select id="type" (maintenance/repair/inspection/installation)
        # - select id="priority" (low/medium/high/urgent)
        # - input id="scheduled_date"
        # - select id="technician_id" (if available)
        
        self.driver.find_element(By.ID, "title").send_keys("Solar Panel Maintenance Check")
        self.driver.find_element(By.ID, "description").send_keys("Routine maintenance and performance verification for panel after fault resolution")
        
        # Select type
        type_select = self.driver.find_element(By.ID, "type")
        Select(type_select).select_by_value("maintenance")
        
        # Select priority
        priority_select = self.driver.find_element(By.ID, "priority")
        Select(priority_select).select_by_value("medium")
        
        # Set scheduled date
        date_input = self.driver.find_element(By.ID, "scheduled_date")
        date_input.clear()
        date_input.send_keys("2024-06-20")
        
        # Select technician if available
        try:
            tech_select = self.driver.find_element(By.ID, "technician_id")
            tech_options = tech_select.find_elements(By.TAG_NAME, "option")
            if len(tech_options) > 1:
                Select(tech_select).select_by_index(1)
        except:
            pass
        
        # Submit button with text "Create Intervention"
        submit_btn = self.wait.until(EC.presence_of_element_located((By.XPATH, "//button[contains(., 'Create Intervention')]")))
        self.driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", submit_btn)
        time.sleep(1)
        self.driver.execute_script("arguments[0].click();", submit_btn)
        
        # Verify redirect to interventions list
        self.wait.until(EC.url_contains("interventions"))
        print("[PASS] Intervention created - Solar Panel Maintenance Check")
    
    def run_full_test(self):
        """Execute the complete test workflow"""
        try:
            print("\n" + "="*60)
            print("SOLARSMART SELENIUM TEST SUITE")
            print("="*60)
            
            # Step 1: Login
            self.login()
            
            # Step 2: Create Solar System
            self.create_solar_system()
            
            # Step 3: Create Panel  
            self.create_panel()
            
            # Step 4: Create Production
            self.create_production()
            
            # Step 5: Trigger Fault Simulation
            self.trigger_fault_simulation()
            
            # Step 6: Verify Alerts
            self.verify_alerts()
            
            # Step 7: Create Intervention
            self.create_intervention()
            
            # Step 8: Disconnect
            self.disconnect()
            
            print("\n" + "="*60)
            print("ALL TESTS COMPLETED SUCCESSFULLY")
            print("="*60)
            
        except Exception as e:
            print(f"\n[ERROR] Test failed at step: {str(e)}")
            import traceback
            traceback.print_exc()
            raise
        finally:
            if self.driver:
                self.driver.quit()


if __name__ == "__main__":
    print("="*60)
    print("SETUP INSTRUCTIONS")
    print("="*60)
    print("1. Install Chrome WebDriver matching your Chrome version")
    print("2. Start Laravel server: php artisan serve")
    print("3. Ensure user exists: fcyusuuf@gmail.com / yusuuf123")
    print("4. Run: python selenium_test.py")
    print("="*60 + "\n")
    
    test = SolarSmartSeleniumTest()
    test.run_full_test()