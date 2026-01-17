# Chapter 10: Data Visualization and Reporting - Code Examples

Complete, production-ready examples for data visualization and reporting in PHP.

## Features

- ✅ **Secure by Design** - All user input escaped, XSS protection built-in
- ✅ **Accessible** - WCAG 2.1 compliant with ARIA labels and screen reader support
- ✅ **Responsive** - Mobile-first design with tablet/desktop breakpoints
- ✅ **Dark Mode** - Automatic theme detection and support
- ✅ **Colorblind-Safe** - Wong 2011 color palette for accessibility
- ✅ **Interactive** - Chart downloads, AJAX updates, live dashboards
- ✅ **Performance** - LTTB downsampling for large datasets
- ✅ **Professional** - PDF reports, multiple export formats

## Requirements

### Minimum Requirements

- **PHP 8.4+**
- **Composer** (for dependency management)
- Modern web browser (Chrome 120+, Firefox 121+, Safari 17+, Edge 120+)

### Optional Requirements

- **Node.js 18+** (for chart-to-image export)
- **Puppeteer** (`npm install puppeteer`) (for screenshot capture)

## Installation

```bash
# Navigate to chapter directory
cd code/data-science-php-developers/chapter-10

# Install PHP dependencies
composer install

# Optional: Install Node.js dependencies for chart export
npm install puppeteer
```

## Quick Start

### Generate All Examples

```bash
# Run all examples at once
composer examples

# Or run individually:
php examples/chart-examples.php
php examples/dashboard-example.php
php examples/pdf-report-example.php
php examples/export-example.php
```

### View in Browser

```bash
# Start PHP development server
php -S localhost:8000 -t output

# Open in browser
open http://localhost:8000
```

## Project Structure

```
chapter-10/
├── src/Visualization/          # Core classes
│   ├── ChartBuilder.php        # Chart.js configuration generator
│   ├── DashboardGenerator.php  # Interactive dashboard builder
│   ├── PDFReportGenerator.php  # PDF report generator
│   ├── DataExporter.php        # Multi-format data export
│   ├── ChartExporter.php       # Chart-to-image converter
│   └── LiveDashboard.php       # Real-time AJAX dashboard
├── templates/                  # HTML templates
│   └── chart-template.php      # Accessible, responsive chart template
├── examples/                   # Runnable examples
│   ├── chart-examples.php      # Various chart types
│   ├── dashboard-example.php   # Complete dashboard
│   ├── pdf-report-example.php  # PDF generation
│   ├── export-example.php      # Data export formats
│   ├── live-dashboard-example.php  # AJAX updates
│   └── chart-export-example.php    # Chart-to-image
├── public/api/                 # API endpoints
│   └── dashboard-data.php      # Live dashboard data
├── scripts/                    # Utility scripts
│   └── capture-chart.js        # Node.js screenshot capture
├── output/                     # Generated files
├── temp/                       # Temporary files
└── composer.json              # Dependencies
```

## Classes

### ChartBuilder

Generate Chart.js configurations from PHP with security and accessibility.

**Key Methods:**
- `lineChart()` - Line charts for trends
- `barChart()` - Bar charts for comparisons
- `pieChart()` - Pie charts for proportions
- `scatterPlot()` - Scatter plots for correlations
- `modelPerformanceChart()` - ML accuracy visualization
- `abTestChart()` - A/B test results with significance
- `makeColorblindSafe()` - Apply accessible color palette
- `makeAccessible()` - Add ARIA labels
- `downsampleData()` - LTTB algorithm for large datasets
- `toJson()` - Export to Chart.js JSON (secure)

**Example:**
```php
$chartBuilder = new ChartBuilder();

$chart = $chartBuilder->lineChart(
    labels: ['Jan', 'Feb', 'Mar'],
    datasets: [[
        'label' => 'Sales',
        'data' => [100, 150, 120],
    ]]
);

// Make accessible
$chart = $chartBuilder->makeColorblindSafe($chart);
$chart = $chartBuilder->makeAccessible($chart, 'Sales trend for Q1');

echo $chartBuilder->toJson($chart);
```

### DashboardGenerator

Build interactive dashboards with multiple widgets.

**Key Methods:**
- `addChart()` - Add chart widget
- `addStatCard()` - Add KPI card
- `addTable()` - Add data table
- `addFilter()` - Add interactive filter
- `generate()` - Generate complete HTML

**Example:**
```php
$dashboard = new DashboardGenerator();

$dashboard
    ->addStatCard('Total Users', '12,847', '+8.5%', '👥')
    ->addChart('revenue', 'Revenue Trend', $chartConfig)
    ->addTable('Top Products', $headers, $rows);

$html = $dashboard->generate('Business Dashboard', refreshInterval: 30);
file_put_contents('dashboard.html', $html);
```

### PDFReportGenerator

Create professional PDF reports with dompdf.

**Key Methods:**
- `startReport()` - Initialize report
- `addSection()` - Add section heading
- `addText()` - Add paragraph (secure)
- `addStats()` - Add stat cards
- `addTable()` - Add data table
- `addInsight()` - Add insight box (green)
- `addWarning()` - Add warning box (orange)
- `addChartImage()` - Embed chart image (base64)
- `addPageBreak()` - Insert page break
- `savePDF()` - Save to file

**Example:**
```php
$report = new PDFReportGenerator();

$report
    ->startReport('Monthly Report', 'January 2026')
    ->addStats([
        ['label' => 'Revenue', 'value' => '$127,450'],
        ['label' => 'Users', 'value' => '12,847'],
    ])
    ->addTable(['Metric', 'Value'], $rows)
    ->addInsight('Revenue grew 13.5% this month')
    ->savePDF('report.pdf');
```

### DataExporter

Export data in multiple formats with validation.

**Key Methods:**
- `toCSV()` - Standard CSV export
- `toExcelCSV()` - Excel-compatible CSV (UTF-8 BOM)
- `toJSON()` - JSON export with validation
- `toHTML()` - Secure HTML table
- `streamCSV()` - Stream to browser
- `streamLargeCSV()` - Memory-efficient export for large datasets

**Example:**
```php
$exporter = new DataExporter();

// Export to multiple formats
$exporter->toCSV($headers, $rows, 'data.csv');
$exporter->toExcelCSV($headers, $rows, 'data-excel.csv');
$exporter->toJSON($data, 'data.json');

$html = $exporter->toHTML($headers, $rows, 'Report');
file_put_contents('data.html', $html);
```

### ChartExporter (Optional - Requires Node.js)

Convert Chart.js charts to static PNG images.

**Key Methods:**
- `chartToPng()` - Convert chart to base64 PNG
- `exportToFile()` - Save chart as PNG file
- `cleanup()` - Clean temp files

**Example:**
```php
$exporter = new ChartExporter();

// Export to file
$exporter->exportToFile($chartConfig, 'chart.png', 800, 400);

// Get base64 for PDF embedding
$base64 = $exporter->chartToPng($chartConfig, 800, 400);
$report->addChartImage($base64, 'Figure 1: Sales Trend');
```

### LiveDashboard

Real-time dashboard with AJAX updates.

**Key Methods:**
- `generateLive()` - Generate dashboard with AJAX
- `addLiveStatCard()` - Add stat card with ID for updates

**Example:**
```php
$dashboard = new LiveDashboard();

$dashboard
    ->addLiveStatCard('total-users', 'Users', '12,847', '+8.5%')
    ->addChart('revenue', 'Revenue', $chartConfig);

$html = $dashboard->generateLive(
    'Real-Time Dashboard',
    '/api/dashboard-data.php',
    updateInterval: 30
);
```

## Examples

### 1. Chart Examples

Demonstrates various chart types with accessibility features.

```bash
php examples/chart-examples.php
```

**Generates:**
- `output/model-performance.html` - ML accuracy trend
- `output/ab-test-results.html` - A/B test with significance
- `output/sales-trend-downsampled.html` - Large dataset (365→100 points)

**Features:**
- Colorblind-safe palettes
- ARIA labels and descriptions
- Download as PNG/SVG
- Dark mode support
- Responsive design

### 2. Dashboard Example

Complete business intelligence dashboard.

```bash
php examples/dashboard-example.php
open output/dashboard.html
```

**Features:**
- 4 stat cards with trends
- 3 interactive charts
- Data table
- Interactive filter dropdown
- Auto-refresh every 30 seconds
- Mobile-responsive layout

### 3. PDF Report Example

Professional multi-page PDF report.

```bash
php examples/pdf-report-example.php
open output/monthly-report.pdf
```

**Features:**
- Executive summary
- Stat cards
- Data tables
- Insight/warning boxes
- Page breaks
- Professional styling

### 4. Data Export Example

Export data in multiple formats.

```bash
php examples/export-example.php
```

**Generates:**
- `output/products.csv` - Standard CSV
- `output/products-excel.csv` - Excel-compatible
- `output/products.json` - JSON with metadata
- `output/products.html` - HTML table
- `output/products-large.csv` - 10,000 records (streaming)

### 5. Live Dashboard Example

Real-time dashboard with AJAX updates.

```bash
php examples/live-dashboard-example.php

# Start server
cd output
php -S localhost:8000

# Open http://localhost:8000/live-dashboard.html
```

**Features:**
- Updates every 30 seconds without page reload
- Manual refresh button
- Visual update indicators
- Error handling
- XSS protection

### 6. Chart Export Example (Optional)

Export charts as PNG images for PDF embedding.

**Requirements:** Node.js + Puppeteer

```bash
# Install Puppeteer first
npm install puppeteer

# Run example
php examples/chart-export-example.php
```

**Generates:**
- `output/chart-export.png` - Standalone image
- `output/report-with-chart.pdf` - PDF with embedded chart

## Security Features

All examples include production-ready security:

### XSS Protection

```php
// All user input is escaped
$dashboard->addStatCard(
    title: '<script>alert("xss")</script>',  // Safely escaped
    value: $userInput  // Safely escaped
);
```

### Input Validation

```php
// Empty arrays rejected
$exporter->toCSV([], $rows, 'file.csv');  // Throws exception

// File operations validated
$report->savePDF('report.pdf');  // Creates directory if needed
```

### Secure JSON Encoding

```php
// JavaScript callbacks validated
$chartBuilder->toJson($config);  // Validates JS function format
```

### Safe HTML in PDFs

```php
// HTML sanitization
$report->addText($userInput, allowHTML: false);  // Escapes all HTML
$report->addText($safeHTML, allowHTML: true);   // Strips dangerous tags
```

## Accessibility Features

### ARIA Labels

```php
$chart = $chartBuilder->makeAccessible(
    $chart,
    'Sales trend showing 25% growth over Q1'
);
```

Generates:
```html
<canvas id="chart" 
        role="img"
        aria-label="Sales trend showing 25% growth over Q1">
</canvas>
```

### Screen Reader Support

Templates include hidden descriptions:

```html
<div class="sr-only">
    Detailed description of chart for screen readers
</div>
```

### Colorblind-Safe Palettes

```php
// Apply Wong 2011 palette (tested for all color blindness types)
$chart = $chartBuilder->makeColorblindSafe($chart);
```

### Keyboard Navigation

- Tab through charts and controls
- Ctrl+S to download chart
- Enter to activate buttons

## Responsive Design

All templates include three breakpoints:

### Desktop (>768px)
- Full layout
- Larger charts (400px height)
- Multi-column grids

### Tablet (480-768px)
- Adjusted grid columns
- Medium charts (300px height)
- Comfortable spacing

### Mobile (<480px)
- Single column layout
- Compact charts (250px height)
- Touch-friendly buttons

Test responsiveness:
```bash
# Resize browser window
# Or use browser dev tools device emulation
```

## Dark Mode

Automatic detection via CSS:

```css
@media (prefers-color-scheme: dark) {
    body {
        background: #1a1a1a;
        color: #e0e0e0;
    }
}
```

Test dark mode:
- macOS: System Preferences → General → Appearance → Dark
- Windows: Settings → Personalization → Colors → Dark
- Linux: Varies by desktop environment

## Performance

### Large Dataset Handling

Use LTTB (Largest Triangle Three Buckets) downsampling:

```php
// Reduce 10,000 points to 500 while preserving visual shape
$downsampled = $chartBuilder->downsampleData($largeDataset, 500);
```

### Streaming Exports

Memory-efficient export for large datasets:

```php
function generateRecords(): \Generator {
    for ($i = 0; $i < 1000000; $i++) {
        yield [$i, rand(), rand()];
    }
}

$exporter->streamLargeCSV(generateRecords(), $headers, 'large.csv');
// Peak memory: ~2MB regardless of dataset size
```

## Browser Compatibility

Tested and working:

| Browser | Version | Status |
|---------|---------|--------|
| Chrome  | 120+    | ✅ Full support |
| Firefox | 121+    | ✅ Full support |
| Safari  | 17+     | ✅ Full support |
| Edge    | 120+    | ✅ Full support |
| Mobile Safari | iOS 16+ | ✅ Full support |
| Chrome Mobile | Android 12+ | ✅ Full support |

## Common Issues

### Charts not displaying

**Problem:** Blank chart area

**Solutions:**
1. Check browser console for errors
2. Verify Chart.js CDN is accessible
3. Ensure JavaScript is enabled
4. Check for syntax errors in chart config

```bash
# Test CDN
curl -I https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js
```

### PDF generation fails

**Problem:** `Class 'Dompdf\Dompdf' not found`

**Solution:**
```bash
composer install
```

### Excel shows garbled text

**Problem:** Special characters display incorrectly

**Solution:** Use `toExcelCSV()` instead of `toCSV()`
```php
$exporter->toExcelCSV($headers, $rows, 'file.csv');  // Adds UTF-8 BOM
```

### Chart export fails

**Problem:** `Node.js is required for chart export`

**Solutions:**
1. Install Node.js 18+: https://nodejs.org/
2. Install Puppeteer: `npm install puppeteer`
3. Verify installation: `node --version`

### Mobile layout broken

**Problem:** Charts overlap on small screens

**Solution:** Template already includes responsive CSS. If custom styling:
```css
@media (max-width: 480px) {
    .chart-container {
        height: 250px;  /* Smaller on mobile */
    }
}
```

### Memory errors with large datasets

**Problem:** `Allowed memory size exhausted`

**Solutions:**
1. Use downsampling: `$chartBuilder->downsampleData($data, 500)`
2. Use streaming export: `$exporter->streamLargeCSV()`
3. Increase PHP memory: `ini_set('memory_limit', '512M')`

## Testing

Run the test suite:

```bash
composer test
```

Manual testing checklist:
- [ ] All charts render in browser
- [ ] PDFs generate without errors
- [ ] Exports open correctly (CSV in Excel, JSON in editor)
- [ ] Responsive design works (resize browser)
- [ ] Dark mode applies correctly
- [ ] Charts downloadable as PNG/SVG
- [ ] Accessibility tested with screen reader
- [ ] Mobile testing on actual devices

## Advanced Usage

### Custom Color Palettes

```php
// Use your own colors
$chart['data']['datasets'][0]['backgroundColor'] = 'rgba(255, 99, 132, 0.2)';
$chart['data']['datasets'][0]['borderColor'] = 'rgb(255, 99, 132)';
```

### Chart Animations

```php
$options = [
    'animation' => [
        'duration' => 2000,
        'easing' => 'easeInOutQuart',
    ],
];
```

### Interactive Chart Events

Add to chart template:
```javascript
myChart.options.onClick = (event, elements) => {
    if (elements.length > 0) {
        const dataIndex = elements[0].index;
        console.log('Clicked:', myChart.data.labels[dataIndex]);
    }
};
```

### Custom PDF Fonts

```php
$options = new \Dompdf\Options();
$options->set('fontDir', '/path/to/fonts');
$options->set('defaultFont', 'YourFont');
```

## Contributing

Found a bug or have a suggestion? Please file an issue with:
1. PHP version (`php --version`)
2. Browser and version
3. Steps to reproduce
4. Expected vs actual behavior
5. Error messages (if any)

## License

MIT License - See LICENSE file for details

## Further Reading

- [Chart.js Documentation](https://www.chartjs.org/docs/latest/)
- [dompdf Documentation](https://github.com/dompdf/dompdf)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [Wong 2011 Color Palette](https://www.nature.com/articles/nmeth.1618)
- [LTTB Algorithm](https://github.com/sveinn-steinarsson/flot-downsample)

## Support

For questions about this code:
- Read the chapter tutorial
- Check this README
- Review example files
- Test in browser console

For Chart.js issues: https://github.com/chartjs/Chart.js/issues
For dompdf issues: https://github.com/dompdf/dompdf/issues
