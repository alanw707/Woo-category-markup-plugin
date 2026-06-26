const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  await page.goto('https://shop.3wdistributing.com/?s=wheels', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(5000);
  const data = await page.evaluate(() => {
    const el = Array.from(document.querySelectorAll('input[name="s"]')).find((n) => n.getBoundingClientRect().width > 0);
    if (!el) return null;
    const chain = [];
    let cur = el;
    while (cur && chain.length < 8) {
      chain.push({ tag: cur.tagName, id: cur.id, cls: cur.className });
      cur = cur.parentElement;
    }
    return chain;
  });
  console.log(JSON.stringify(data, null, 2));
  await browser.close();
})();
