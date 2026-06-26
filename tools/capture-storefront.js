const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const url = process.argv[2] || 'https://shop.3wdistributing.com/';
const label = process.argv[3] || 'capture';
const outDir = path.join(process.cwd(), 'docs', 'screenshots', label);
fs.mkdirSync(outDir, { recursive: true });

const viewports = [
  { name: 'desktop-1440', width: 1440, height: 1100 },
  { name: 'tablet-768', width: 768, height: 1100 },
  { name: 'mobile-390', width: 390, height: 1200 },
  { name: 'mobile-360', width: 360, height: 1200 },
];

(async () => {
  const browser = await chromium.launch({ headless: true });
  const report = [];

  for (const vp of viewports) {
    const page = await browser.newPage({ viewport: { width: vp.width, height: vp.height }, deviceScaleFactor: 1 });
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForLoadState('load', { timeout: 30000 }).catch(() => {});
    await page.waitForTimeout(3500);

    const screenshot = path.join(outDir, `${vp.name}.png`);
    await page.screenshot({ path: screenshot, fullPage: true });

    const data = await page.evaluate(() => {
      const text = document.body.innerText || '';
      const all = Array.from(document.querySelectorAll('*'));
      const builder = all.filter(el => /Build with Header Builder|Header Builder/i.test(el.textContent || '')).map(el => ({
        tag: el.tagName.toLowerCase(),
        cls: el.className && String(el.className).slice(0, 160),
        text: (el.textContent || '').trim().slice(0, 120),
        rect: (() => { const r = el.getBoundingClientRect(); return { x:r.x, y:r.y, w:r.width, h:r.height }; })(),
      })).slice(0, 8);
      const copyright = all.filter(el => /3WDistributing\.com © All Rights Reserved/i.test(el.textContent || '')).map(el => {
        const r = el.getBoundingClientRect();
        return { tag:el.tagName.toLowerCase(), id:el.id, cls:String(el.className).slice(0,160), rect:{x:r.x,y:r.y,w:r.width,h:r.height}, text:(el.textContent||'').trim().slice(0,120) };
      }).slice(0,10);
      const products = Array.from(document.querySelectorAll('.products .product, ul.products li.product, .product-col')).slice(0, 16).map(el => {
        const r = el.getBoundingClientRect();
        const img = el.querySelector('img');
        const ir = img ? img.getBoundingClientRect() : null;
        return {
          cls: String(el.className).slice(0, 120),
          rect: { x:r.x, y:r.y, w:r.width, h:r.height },
          img: ir ? { w:ir.width, h:ir.height } : null,
          title: (el.querySelector('.woocommerce-loop-product__title, h2, h3, a')?.textContent || '').trim().slice(0, 80),
          price: (el.querySelector('.price')?.textContent || '').trim().slice(0, 80),
          hasButton: !!el.querySelector('a.button, button, .add_to_cart_button'),
          selectors: { id: el.id, cls: String(el.className).slice(0,200), parent: String(el.parentElement?.className || '').slice(0,200) },
          affirmText: (el.textContent || '').includes('affirm') || (el.textContent || '').includes('APR'),
        };
      });
      const chat = Array.from(document.querySelectorAll('[class*="whatsapp"], [id*="whatsapp"], [class*="joinchat"], [id*="joinchat"]')).map(el => {
        const r = el.getBoundingClientRect();
        const s = getComputedStyle(el);
        return { tag: el.tagName.toLowerCase(), id: el.id, cls: String(el.className).slice(0, 160), rect:{x:r.x,y:r.y,w:r.width,h:r.height}, pos:s.position, z:s.zIndex, text:(el.textContent||'').trim().slice(0,80) };
      }).filter(x => x.rect.w && x.rect.h).slice(0, 12);
      const hero = Array.from(document.querySelectorAll('img, section, .vc_row, .banner, .porto-ibanner')).map(el => {
        const r = el.getBoundingClientRect();
        const bg = getComputedStyle(el).backgroundImage;
        const txt = (el.textContent || '').trim();
        return { tag:el.tagName.toLowerCase(), cls:String(el.className).slice(0,120), rect:{x:r.x,y:r.y,w:r.width,h:r.height}, bg:bg !== 'none', text:txt.slice(0,80) };
      }).filter(x => x.rect.w > 250 && x.rect.h > 150 && x.rect.y < 700).slice(0, 12);
      return {
        title: document.title,
        bodyWidth: document.documentElement.scrollWidth,
        viewportWidth: window.innerWidth,
        horizontalOverflow: document.documentElement.scrollWidth > window.innerWidth + 2,
        hasBuilderText: /Build with Header Builder/i.test(text),
        hasEarlyCopyright: /© All Rights Reserved/i.test(text.slice(0, 800)),
        builder,
        copyright,
        products,
        chat,
        hero,
      };
    });

    report.push({ viewport: vp, screenshot, data });
    await page.close();
  }

  fs.writeFileSync(path.join(outDir, 'report.json'), JSON.stringify(report, null, 2));
  console.log(`saved ${label} screenshots/report to ${outDir}`);
  for (const item of report) {
    const d = item.data;
    console.log(`${item.viewport.name}: overflow=${d.horizontalOverflow} builder=${d.hasBuilderText} earlyCopyright=${d.hasEarlyCopyright} products=${d.products.length} chat=${d.chat.length}`);
  }
  await browser.close();
})().catch(err => {
  console.error(err.stack || err.message);
  process.exit(1);
});
