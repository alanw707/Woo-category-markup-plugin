const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  await page.goto('https://shop.3wdistributing.com/?s=wheels', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(5000);
  const data = await page.evaluate(() => {
    const els = Array.from(document.querySelectorAll('header input[name="s"], #header input[name="s"], .threew-mobile-header-search input[name="s"], .searchform-fields'));
    return els.map((el) => {
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return {
        tag: el.tagName,
        cls: el.className,
        parentCls: el.parentElement ? el.parentElement.className : '',
        visible: r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden',
        x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height),
        borderTop: s.borderTop,
        borderRadius: s.borderRadius,
        outline: s.outline,
        boxShadow: s.boxShadow,
        type: el.getAttribute('type'),
        placeholder: el.getAttribute('placeholder'),
      };
    });
  });
  console.log(JSON.stringify(data, null, 2));
  await browser.close();
})();
