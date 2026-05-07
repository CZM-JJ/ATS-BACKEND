# Excel Export & Analytics - API Quick Reference

## Base URL
```
https://your-ats-api.com/api
```

## Authentication
All endpoints require Bearer token authentication:
```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

## Export Endpoints

### 1. Preview Export
```http
GET /export/applicants/preview?status=applied&position=Developer&source=LinkedIn&date_from=2024-01-01&date_to=2024-12-31
```
**Response:**
```json
{
  "success": true,
  "applicants_count": 42,
  "filters_applied": {
    "status": "applied",
    "position": "Developer"
  },
  "message": "42 applicants will be exported"
}
```

### 2. Download Excel File
```http
GET /export/applicants?status=hired&date_from=2024-01-01
```
**Response:** Binary Excel file (.xlsx)

---

## Analytics Endpoints

### 1. Pipeline Metrics
```http
GET /analytics/pipeline?position=Developer
```
**Response:**
```json
{
  "success": true,
  "data": {
    "applied": 45,
    "screening": 12,
    "interview": 8,
    "offer": 2,
    "hired": 1,
    "rejected": 22,
    "total": 90
  },
  "position_filter": "Developer"
}
```

### 2. Source Analytics
```http
GET /analytics/sources
```
**Response:**
```json
{
  "success": true,
  "data": {
    "LinkedIn": {
      "total": 35,
      "hired": 3,
      "rejected": 12,
      "hired_rate": 8.57,
      "rejection_rate": 34.29
    }
  }
}
```

### 3. Hiring Performance
```http
GET /analytics/performance?position=Manager
```
**Response:**
```json
{
  "success": true,
  "data": {
    "total_applicants": 90,
    "hired": 5,
    "rejected": 27,
    "in_pipeline": 58,
    "hire_rate": 5.56,
    "rejection_rate": 30.0,
    "conversion_rate": 15.63
  }
}
```

### 4. Time-to-Hire
```http
GET /analytics/time-to-hire
```
**Response:**
```json
{
  "success": true,
  "data": {
    "average_days": 24.5,
    "fastest_hire_days": 8,
    "slowest_hire_days": 45,
    "median_days": 22.0,
    "total_hired": 5
  }
}
```

### 5. Full Dashboard
```http
GET /analytics/dashboard?position=Developer
```
**Response:** Combines all metrics above with `generated_at` timestamp

### 6. Date Range Analytics
```http
GET /analytics/date-range?start_date=2024-01-01&end_date=2024-12-31&position=Developer
```
**Required Parameters:**
- `start_date` (YYYY-MM-DD)
- `end_date` (YYYY-MM-DD)

**Response:**
```json
{
  "success": true,
  "data": {
    "date_range": "2024-01-01 to 2024-12-31",
    "total_applicants": 23,
    "pipeline": {...},
    "sources": {...}
  }
}
```

---

## Query Parameters

### Common Filters
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `position` | string | Position name | Developer |
| `status` | string | Applicant status | applied, hired, rejected |
| `source` | string | Vacancy source | LinkedIn, Indeed |
| `date_from` | date | Start date | 2024-01-01 |
| `date_to` | date | End date | 2024-12-31 |

### Allowed Status Values
- `applied` - Initial application
- `screening` - CV screening
- `interview` - Interview stage
- `offer` - Offer made
- `hired` - Position accepted
- `rejected` - Application rejected

---

## Error Handling

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "start_date": ["The start date field is required."]
  }
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Failed to fetch pipeline metrics: [error details]"
}
```

---

## Usage Examples

### cURL
```bash
# Get pipeline metrics
curl -X GET "https://api.example.com/api/analytics/pipeline?position=Developer" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Download Excel export
curl -X GET "https://api.example.com/api/export/applicants?status=hired" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o applicants_hired.xlsx
```

### JavaScript/Fetch
```javascript
// Get analytics dashboard
const response = await fetch('/api/analytics/dashboard?position=Developer', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});
const data = await response.json();
console.log(data.data);

// Download export
const exportUrl = new URL('/api/export/applicants', window.location.origin);
exportUrl.searchParams.append('status', 'hired');
exportUrl.searchParams.append('date_from', '2024-01-01');
window.location.href = exportUrl.toString();
```

### Python/Requests
```python
import requests

headers = {'Authorization': f'Bearer {token}'}

# Get analytics
response = requests.get(
    'https://api.example.com/api/analytics/pipeline',
    params={'position': 'Developer'},
    headers=headers
)
data = response.json()
print(data['data'])

# Download export
response = requests.get(
    'https://api.example.com/api/export/applicants',
    params={'status': 'hired'},
    headers=headers
)
with open('applicants.xlsx', 'wb') as f:
    f.write(response.content)
```

---

## Permissions Required
- `canViewAnalytics` - Required for all export and analytics endpoints

## Rate Limiting
- Standard API rate limits apply
- Export endpoint has file generation overhead - allow 5-10 seconds for large exports
