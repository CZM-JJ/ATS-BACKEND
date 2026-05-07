# Excel Export & Analytics Features

This document describes the new Excel export and analytics features added to the ATS Backend.

## Features Added

### 1. Excel Export

Export candidate/applicant data to Excel files with filtering support.

#### Endpoints

##### Export Applicants
```
GET /api/export/applicants
```

Query Parameters:
- `status` - Filter by applicant status (applied, screening, interview, offer, hired, rejected)
- `position` - Filter by position applied for
- `source` - Filter by vacancy source
- `date_from` - Filter by start date (YYYY-MM-DD)
- `date_to` - Filter by end date (YYYY-MM-DD)

Example:
```bash
GET /api/export/applicants?status=applied&date_from=2024-01-01&date_to=2024-12-31
```

Returns: Excel file (.xlsx) with columns:
- ID, First Name, Last Name, Email, Contact Number
- Position Applied, Status, Education Level, Course/Degree
- Year Graduated, Total Work Experience, PRC License
- Gender, Civil Status, Birthdate, Age
- Permanent Address, Current Address, Preferred Work Location
- Expected Salary, Vacancy Source, Applied Date

##### Export Preview
```
GET /api/export/applicants/preview
```

Query Parameters: Same as export endpoint

Returns: JSON with preview information
```json
{
  "success": true,
  "applicants_count": 42,
  "filters_applied": {
    "status": "applied"
  },
  "message": "42 applicants will be exported"
}
```

#### Usage Example (JavaScript)
```javascript
// Get preview before exporting
const preview = await fetch('/api/export/applicants/preview?status=hired').then(r => r.json());
console.log(`Exporting ${preview.applicants_count} records`);

// Download Excel file
window.location.href = '/api/export/applicants?status=hired';
```

---

### 2. Analytics

Comprehensive recruitment analytics with multiple metrics.

#### Endpoints

##### Pipeline Metrics
```
GET /api/analytics/pipeline
```

Query Parameters:
- `position` - Filter by position (optional)

Returns: Candidate count by status
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
  }
}
```

##### Candidate Source Analytics
```
GET /api/analytics/sources
```

Returns: Metrics by recruitment source
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
    },
    "Indeed": {
      "total": 28,
      "hired": 2,
      "rejected": 15,
      "hired_rate": 7.14,
      "rejection_rate": 53.57
    }
  }
}
```

##### Hiring Performance
```
GET /api/analytics/performance
```

Query Parameters:
- `position` - Filter by position (optional)

Returns: Overall hiring metrics
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

##### Time-to-Hire
```
GET /api/analytics/time-to-hire
```

Query Parameters:
- `position` - Filter by position (optional)

Returns: Hiring duration statistics
```json
{
  "success": true,
  "data": {
    "average_days": 24.5,
    "fastest_hire_days": 8,
    "slowest_hire_days": 45,
    "total_hired": 5,
    "median_days": 22.0
  }
}
```

##### Comprehensive Dashboard
```
GET /api/analytics/dashboard
```

Query Parameters:
- `position` - Filter by position (optional)

Returns: All analytics combined
```json
{
  "success": true,
  "data": {
    "pipeline": { /* pipeline metrics */ },
    "candidate_sources": { /* source metrics */ },
    "hiring_performance": { /* performance metrics */ },
    "time_to_hire": { /* time metrics */ },
    "generated_at": "2024-05-05 15:02:01"
  }
}
```

##### Analytics by Date Range
```
GET /api/analytics/date-range
```

Query Parameters (Required):
- `start_date` - Start date (YYYY-MM-DD)
- `end_date` - End date (YYYY-MM-DD)
- `position` - Filter by position (optional)

Returns: Analytics for specified date range
```json
{
  "success": true,
  "data": {
    "date_range": "2024-01-01 to 2024-05-05",
    "total_applicants": 23,
    "pipeline": {
      "applied": 10,
      "screening": 5,
      "hired": 3
    },
    "sources": {
      "LinkedIn": { "total": 15, "hired": 2 }
    }
  }
}
```

#### Usage Examples (JavaScript)

```javascript
// Get pipeline metrics
const pipeline = await fetch('/api/analytics/pipeline?position=Developer').then(r => r.json());
console.log(`Applied: ${pipeline.data.applied}, Hired: ${pipeline.data.hired}`);

// Get full dashboard
const dashboard = await fetch('/api/analytics/dashboard').then(r => r.json());
console.log('Analytics Dashboard:', dashboard.data);

// Get date range analytics
const dateRange = await fetch('/api/analytics/date-range?start_date=2024-01-01&end_date=2024-12-31').then(r => r.json());
console.log(`Total applicants in 2024: ${dateRange.data.total_applicants}`);
```

---

## Implementation Details

### Services

#### ExportService (`app/Services/ExportService.php`)
- Handles Excel file generation
- Supports filtering by status, position, source, date range
- Creates formatted Excel files with headers and auto-sized columns
- Files stored in `storage/app/exports/`

#### AnalyticsService (`app/Services/AnalyticsService.php`)
- Calculates pipeline metrics
- Computes source-based analytics with conversion rates
- Generates hiring performance metrics
- Calculates time-to-hire statistics
- Provides date-range filtering

### Controllers

#### ExportController (`app/Http/Controllers/ExportController.php`)
- `exportApplicants()` - Downloads Excel file
- `getExportPreview()` - Returns count and preview info

#### AnalyticsController (`app/Http/Controllers/AnalyticsController.php`)
- `getPipelineMetrics()` - Candidate status breakdown
- `getCandidateSourceAnalytics()` - Source performance
- `getHiringPerformance()` - Overall hiring metrics
- `getTimeToHire()` - Duration statistics
- `getDashboard()` - Combined analytics view
- `getByDateRange()` - Date-filtered analytics

### Permissions

All endpoints require:
- Authentication (JWT via Sanctum)
- `canViewAnalytics` permission

### Routes (routes/api.php)

```php
// Export routes
GET  /api/export/applicants           - Download Excel
GET  /api/export/applicants/preview   - Preview export

// Analytics routes
GET  /api/analytics/pipeline          - Pipeline metrics
GET  /api/analytics/sources           - Source analytics
GET  /api/analytics/performance       - Performance metrics
GET  /api/analytics/time-to-hire      - Time to hire
GET  /api/analytics/dashboard         - Full dashboard
GET  /api/analytics/date-range        - Date range analytics
```

---

## Testing

Tests included in `tests/Feature/ExportTest.php` and `tests/Feature/AnalyticsTest.php`:

```bash
# Run all tests
php artisan test

# Run only export tests
php artisan test tests/Feature/ExportTest.php

# Run only analytics tests
php artisan test tests/Feature/AnalyticsTest.php
```

---

## Dependencies

- **PhpSpreadsheet** ^1.29 - For Excel file generation
- Installed via: `composer require phpoffice/phpspreadsheet`

---

## Future Enhancements

- Analytics caching for performance optimization
- Export to CSV, PDF formats
- Scheduled analytics reports
- Analytics data visualization recommendations
- Export templates customization
- Real-time analytics dashboard updates
