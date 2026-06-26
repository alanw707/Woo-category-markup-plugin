const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch({ headless: true });
  const p = await b.newPage({ viewport: { width: 390, height: 900 } });
  await p.goto('https://shop.3wdistributing.com/', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await p.waitForLoadState('load', { timeout: 30000 }).catch(() => {});
  await p.waitForTimeout(3500);
  const d = await p.evaluate(() => ({
    script: !!document.querySelector('#threew-storefront-polish-hotfix-js'),
    items: Array.from(document.querySelectorAll('.home-banner .owl-item')).slice(0, 12).map(el => {
      const r = el.getBoundingClientRect();
      return {
        cls: String(el.className),
        rect: { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) },
        text: (el.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 80),
      };
    }),
  }));
  console.log(JSON.stringify(d, null, 2));
  await b.close();
})();
