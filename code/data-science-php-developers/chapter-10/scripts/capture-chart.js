/**
 * Chart Capture Script - Puppeteer-based screenshot capture
 * 
 * Usage: node capture-chart.js <htmlFile> <outputFile> <width> <height>
 * 
 * Requirements:
 * - Node.js 18+
 * - Puppeteer (npm install puppeteer)
 */

const puppeteer = require('puppeteer');
const fs = require('fs');

async function captureChart(htmlFile, outputFile, width, height) {
    let browser;
    
    try {
        // Validate inputs
        if (!fs.existsSync(htmlFile)) {
            throw new Error(`HTML file not found: ${htmlFile}`);
        }
        
        // Launch browser in headless mode
        browser = await puppeteer.launch({
            headless: 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage'
            ]
        });
        
        const page = await browser.newPage();
        
        // Set viewport size
        await page.setViewport({ 
            width: parseInt(width), 
            height: parseInt(height),
            deviceScaleFactor: 2 // Higher DPI for better quality
        });
        
        // Load HTML file
        await page.goto(`file://${htmlFile}`, {
            waitUntil: 'networkidle0',
            timeout: 30000
        });
        
        // Wait for chart to render
        await page.waitForSelector('#chart', { timeout: 10000 });
        
        // Allow extra time for Chart.js animations
        await page.waitForTimeout(1500);
        
        // Take screenshot of the chart container
        await page.screenshot({
            path: outputFile,
            type: 'png',
            clip: {
                x: 20,
                y: 20,
                width: parseInt(width),
                height: parseInt(height)
            }
        });
        
        console.log(`✓ Chart captured: ${outputFile}`);
        
    } catch (error) {
        console.error('Error capturing chart:', error.message);
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

// Parse command line arguments
const args = process.argv.slice(2);

if (args.length !== 4) {
    console.error('Usage: node capture-chart.js <htmlFile> <outputFile> <width> <height>');
    console.error('Example: node capture-chart.js chart.html output.png 800 400');
    process.exit(1);
}

const [htmlFile, outputFile, width, height] = args;

// Validate numeric arguments
if (isNaN(width) || isNaN(height)) {
    console.error('Error: Width and height must be numeric');
    process.exit(1);
}

// Run capture
captureChart(htmlFile, outputFile, width, height)
    .then(() => process.exit(0))
    .catch(err => {
        console.error('Fatal error:', err);
        process.exit(1);
    });
