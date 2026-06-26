const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  await page.goto('https://shop.3wdistributing.com/?s=wheels', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(5000);
  const html = await page.evaluate(() => {
    const slb = document.querySelector('.shop-loop-before');
    return slb ? slb.outerHTML : 'NONE';
  });
  console.log(html.slice(0, 2500));
  await browser.close();
})();
