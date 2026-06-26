/* Captures a mobile screenshot of the search results page after the
 * design-review fixes, for the verification record. */
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 412, height: 915 },
    deviceScaleFactor: 2,
    isMobile: true,
    hasTouch: true,
  });
  await page.goto('https://shop.3wdistributing.com/?s=wheels', {
    waitUntil: 'domcontentloaded',
    timeout: 60000,
  });
  await page.waitForTimeout(5000);
  await page.screenshot({
    path: 'docs/screenshots/search-wheels-post-design-review/mobile-412.png',
    fullPage: false,
  });
  await browser.close();
  console.log('screenshot saved');
})();
