const { chromium } = require('playwright');

(async () => {
  const url = process.argv[2] || 'https://shop.3wdistributing.com/product-category/brabus/mercedes/?threew-check=mobile-filter';
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 353, height: 812 }, isMobile: true, hasTouch: true });
  await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(4000);

  const before = await page.evaluate(() => {
    const rect = (el) => { const r = el.getBoundingClientRect(); return { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) }; };
    const visible = (el) => { const r = el.getBoundingClientRect(); const s = getComputedStyle(el); return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden'; };
    const products = document.querySelector('.products, ul.products');
    const widgets = Array.from(document.querySelectorAll('#sidebar, .sidebar, .left-sidebar, .widget-area, .shop-sidebar, .porto-products-filter, .porto-products-filter-body'))
      .filter(visible)
      .map(el => ({ selector: el.id ? `#${el.id}` : `.${String(el.className).trim().split(/\s+/)[0]}`, rect: rect(el), text: (el.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 80) }));
    return { productBottom: products ? Math.round(products.getBoundingClientRect().bottom) : null, widgets };
  });

  const leaking = before.widgets.filter(w => before.productBottom !== null && w.rect.y > before.productBottom - 20 && /Categories|Filter|Brabus|Mercedes/i.test(w.text));
  if (leaking.length) {
    console.log('FAIL mobile filter widgets visible below products while closed');
    console.log(JSON.stringify({ url, productBottom: before.productBottom, leaking }, null, 2));
    await browser.close();
    process.exit(1);
  }

  await page.click('.porto-product-filters-toggle.sidebar-toggle');
  await page.waitForTimeout(400);
  const open = await page.$eval('.porto-woo-category-sidebar.mobile-sidebar', el => {
    const r = el.getBoundingClientRect();
    return { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height), text: (el.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 80) };
  });

  const shell = await page.evaluate(() => {
    const r = document.querySelector('.page-wrapper')?.getBoundingClientRect();
    return r ? { x: Math.round(r.x), w: Math.round(r.width) } : null;
  });

  if (open.x !== 0 || !/Categories|Brabus|Mercedes/i.test(open.text) || (shell && Math.abs(shell.x) > 2)) {
    console.log('FAIL mobile filter button did not open populated sidebar');
    console.log(JSON.stringify({ url, open, shell }, null, 2));
    await browser.close();
    process.exit(1);
  }

  console.log('PASS mobile filter sidebar hidden while closed and populated when opened');
  await browser.close();
})().catch(err => { console.error(err.stack || err.message); process.exit(2); });
