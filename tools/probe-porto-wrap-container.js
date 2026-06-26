const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 390, height: 900 } });
  await page.goto(process.argv[2] || 'https://shop.3wdistributing.com/', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForLoadState('load', { timeout: 30000 }).catch(() => {});
  await page.waitForTimeout(3000);
  const data = await page.evaluate(() => Array.from(document.querySelectorAll('.porto-wrap-container.container')).map((el, i) => {
    const r = el.getBoundingClientRect();
    const parent = el.parentElement;
    const gp = parent && parent.parentElement;
    return {
      index: i,
      rect: { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) },
      text: (el.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 240),
      className: el.className,
      parentClass: parent ? parent.className : '',
      grandParentClass: gp ? gp.className : '',
      ancestor: el.closest('[data-id], section, .porto-block, .wpb_row, .vc_row')?.outerHTML.slice(0, 700),
    };
  }));
  console.log(JSON.stringify(data, null, 2));
  await browser.close();
})();
