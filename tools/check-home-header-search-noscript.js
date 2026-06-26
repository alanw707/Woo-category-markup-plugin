const { chromium } = require('playwright');

const url = process.argv[2] || 'https://shop.3wdistributing.com/';
const mobileUserAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

(async () => {
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage({
      viewport: { width: 391, height: 586 },
      userAgent: mobileUserAgent,
      javaScriptEnabled: false,
    });

    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });

    const result = await page.evaluate(() => {
      const visible = (el) => {
        if (!el) return false;
        const rect = el.getBoundingClientRect();
        const style = getComputedStyle(el);
        return rect.width >= 150 && rect.height >= 40 && style.display !== 'none' && style.visibility !== 'hidden' && Number(style.opacity) > 0;
      };
      const selector = '.header-center .searchform-popup, .threew-mobile-header-search';
      const el = Array.from(document.querySelectorAll(selector)).find(visible);
      if (!el) return { visible: false, count: document.querySelectorAll(selector).length };
      const rect = el.getBoundingClientRect();
      return { visible: true, count: document.querySelectorAll('.threew-mobile-header-search').length, box: { x: Math.round(rect.x), y: Math.round(rect.y), w: Math.round(rect.width), h: Math.round(rect.height) } };
    });

    if (!result.visible) {
      console.log('FAIL home mobile search visible without JS');
      console.log(JSON.stringify(result, null, 2));
      process.exit(1);
    }

    console.log('PASS home mobile search visible without JS');
    console.log(JSON.stringify(result, null, 2));
  } catch (error) {
    console.log('FAIL home mobile search visible without JS: ' + (error && error.message ? error.message : error));
    process.exit(1);
  } finally {
    await browser.close();
  }
})();
