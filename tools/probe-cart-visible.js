const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  await page.goto('https://shop.3wdistributing.com/?product_cat=ast-suspension', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(5000);
  const data = await page.evaluate(() => {
    const rect = (el) => {
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return { tag: el.tagName, cls: el.className, x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height), display: s.display };
    };
    const els = Array.from(document.querySelectorAll('#mini-cart, #mini-cart *, .mini-cart, .mini-cart *'))
      .map(rect)
      .filter(r => r.w > 0 && r.h > 0)
      .slice(0, 40);
    return els;
  });
  console.log(JSON.stringify(data, null, 2));
  await browser.close();
})();
