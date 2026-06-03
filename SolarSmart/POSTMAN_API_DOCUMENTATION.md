# SolarSmart API Documentation

**Base URL:** `http://127.0.0.1:8000`

## Authentication Endpoints

| Method | Full URL |
|--------|---------|
| POST | `http://127.0.0.1:8000/login` |
| POST | `http://127.0.0.1:8000/register` |
| POST | `http://127.0.0.1:8000/logout` |

**Login Request Body (form-data):**
- email: string (required)
- password: string (required)

**Register Request Body (form-data):**
- name: string (required)
- email: string (required)
- password: string (required)
- password_confirmation: string (required)

---

## Solar System Endpoints

| Method | Full URL |
|--------|---------|
| GET | `http://127.0.0.1:8000/api/solar-systems` |
| POST | `http://127.0.0.1:8000/api/solar-systems` |
| GET | `http://127.0.0.1:8000/api/solar-systems/{id}` |
| PUT | `http://127.0.0.1:8000/api/solar-systems/{id}` |
| DELETE | `http://127.0.0.1:8000/api/solar-systems/{id}` |
| GET | `http://127.0.0.1:8000/api/solar-systems/{id}/production-summary` |
| GET | `http://127.0.0.1:8000/api/solar-systems/{id}/production-trend` |

**Create Solar System Body (JSON):**
```json
{
  "name": "Solar System A",
  "location": "Montreal",
  "latitude": 45.5,
  "longitude": -73.5,
  "total_capacity_kw": 50,
  "installation_date": "2024-01-15",
  "description": "Main solar installation"
}
```

**Update Solar System Body (JSON):**
```json
{
  "name": "Updated Name",
  "location": "New Location",
  "total_capacity_kw": 60,
  "status": "active"
}
```

---

## Production Endpoints

| Method | Full URL |
|--------|---------|
| GET | `http://127.0.0.1:8000/api/solar-systems/{id}/productions` |
| POST | `http://127.0.0.1:8000/api/solar-systems/{id}/productions` |
| GET | `http://127.0.0.1:8000/api/solar-systems/{id}/productions/{production_id}` |
| PUT | `http://127.0.0.1:8000/api/solar-systems/{id}/productions/{production_id}` |
| DELETE | `http://127.0.0.1:8000/api/solar-systems/{id}/productions/{production_id}` |
| GET | `http://127.0.0.1:8000/api/solar-systems/{id}/productions/statistics/summary` |
| GET | `http://127.0.0.1:8000/api/solar-systems/{id}/productions/chart/data` |

**Create Production Body (JSON):**
```json
{
  "panel_id": 1,
  "production_date": "2024-01-15",
  "production_time": "12:00:00",
  "energy_produced_kwh": 4.5,
  "energy_consumed_kwh": 3.2,
  "peak_power_kw": 5.0,
  "average_power_kw": 4.2,
  "irradiance_wm2": 850,
  "temperature_celsius": 28,
  "weather_condition": "sunny"
}
```

---

## Alert Endpoints

| Method | Full URL |
|--------|---------|
| GET | `http://127.0.0.1:8000/api/alerts` |
| POST | `http://127.0.0.1:8000/api/solar-systems/{id}/alerts` |
| GET | `http://127.0.0.1:8000/api/alerts/{id}` |
| PUT | `http://127.0.0.1:8000/api/alerts/{id}` |
| DELETE | `http://127.0.0.1:8000/api/alerts/{id}` |
| POST | `http://127.0.0.1:8000/api/alerts/{id}/acknowledge` |
| POST | `http://127.0.0.1:8000/api/alerts/{id}/resolve` |
| GET | `http://127.0.0.1:8000/api/alerts/active/count` |
| GET | `http://127.0.0.1:8000/api/alerts/summary` |

**Create Alert Body (JSON):**
```json
{
  "panel_id": 1,
  "title": "Low Production Detected",
  "message": "Panel production below threshold",
  "type": "low_production",
  "severity": "high"
}
```

---

## Real-time Endpoints

| Method | Full URL |
|--------|---------|
| GET | `http://127.0.0.1:8000/api/realtime/generate` |
| GET | `http://127.0.0.1:8000/api/realtime/production` |
| GET | `http://127.0.0.1:8000/api/realtime/panels` |
| GET | `http://127.0.0.1:8000/api/realtime/status` |
| POST | `http://127.0.0.1:8000/api/realtime/simulate` |

---

## Weather Endpoint

| Method | Full URL |
|--------|---------|
| GET | `http://127.0.0.1:8000/api/weather` |

---

## Fault Simulation Endpoints

| Method | Full URL |
|--------|---------|
| GET | `http://127.0.0.1:8000/api/fault-simulations/fault-types` |
| GET | `http://127.0.0.1:8000/api/fault-simulations` |
| POST | `http://127.0.0.1:8000/api/fault-simulations` |
| GET | `http://127.0.0.1:8000/api/fault-simulations/{id}` |
| POST | `http://127.0.0.1:8000/api/fault-simulations/{id}/resolve` |
| GET | `http://127.0.0.1:8000/api/solar-systems/{id}/fault-simulations/statistics` |

**Create Fault Simulation Body (JSON):**
```json
{
  "panel_id": 1,
  "fault_type": "panel_crack"
}
```

**Available fault_type values:**
- inverter_failure
- panel_crack
- wiring_fault
- sensor_malfunction
- hot_spot
- delamination
- connection_failure
- soiling_severe
- shading_issue
- ground_fault

---

## Utility Endpoints

| Method | Full URL |
|--------|---------|
| GET | `http://127.0.0.1:8000/generate-demo-data` |
| GET | `http://127.0.0.1:8000/dashboard` |

