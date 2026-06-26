const { chromium } = require('playwright');

const urls = process.argv.slice(2);
const targets = urls.length ? urls : [
  'https://shop.3wdistributing.com/shop/',
  'https://shop.3wdistributing.com/product-category/brabus/',
  'https://shop.3wdistributing.com/product-category/brabus/brabus-mercedes/g-wagon/gwagon-w465/',
  'https://shop.3wdistributing.com/?s=brabus',
  'https://shop.3wdistributing.com/?s=sale',
  'https://shop.3wdistributing.com/?s=new%20arrivals',
];

(async () => {
  const browser = await chromium.launch({ headless: true });

  for (const url of targets) {
    const page = await browser.newPage({ viewport: { width: 412, height: 915 } });
    const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(6000);

    const result = await page.evaluate(() => {
      const cards = Array.from(document.querySelectorAll('li.product, .product-col'))
        .filter((el) => {
          const box = el.getBoundingClientRect();
          return box.width > 40 && box.height > 40;
        });
      const named = cards.filter((el) => el.innerText.replace(/\s+/g, ' ').trim().length > 8);
      const loaders = Array.from(document.querySelectorAll('.porto-loading-icon,.loading-overlay'))
        .filter((el) => {
          const box = el.getBoundingClientRect();
          const style = getComputedStyle(el);
          return box.width > 20 && box.height > 20 && style.display !== 'none' && style.visibility !== 'hidden';
        });

      return {
        finalUrl: location.href,
        cards: cards.length,
        named: named.length,
        blank: document.body.innerText.trim().length < 100,
        loaders: loaders.length,
      };
    });

    const ok = response.status() < 400 && !result.blank && result.named > 0 && result.loaders === 0;
    if (!ok) {
      console.log(`FAIL ${url}`);
      console.log(JSON.stringify({ status: response.status(), ...result }, null, 2));
      await browser.close();
      process.exit(1);
    }

    console.log(`PASS ${url} -> ${result.finalUrl}`);
    await page.close();
  }

  await browser.close();
})();
