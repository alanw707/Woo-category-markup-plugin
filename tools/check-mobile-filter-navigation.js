const { chromium } = require('playwright');

(async () => {
  const start = process.argv[2] || 'https://shop.3wdistributing.com/product-category/ast-suspension/?threew-check=filter-nav';
  const targetPattern = process.argv[3] || 'startech';
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 412, height: 915 }, isMobile: true, hasTouch: true });

  await page.goto(start, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(3000);
  await page.click('.porto-product-filters-toggle.sidebar-toggle');
  await page.waitForTimeout(300);

  const clicked = await page.evaluate((pattern) => {
    const link = Array.from(document.querySelectorAll('.sidebar.mobile-sidebar a')).find(a => a.href.includes(pattern));
    if (!link) return false;
    link.click();
    return true;
  }, targetPattern);

  if (!clicked) throw new Error(`No sidebar category link matching ${targetPattern}`);
  await page.waitForLoadState('domcontentloaded', { timeout: 60000 }).catch(() => {});
  await page.waitForTimeout(4000);

  const state = await page.evaluate(() => {
    const box = (sel) => {
      const el = document.querySelector(sel);
      if (!el) return null;
      const r = el.getBoundingClientRect();
      return { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height), text: (el.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 80) };
    };
    return {
      url: location.href,
      body: document.body.className,
      shell: box('.page-wrapper'),
      sidebar: box('.sidebar.mobile-sidebar'),
      hasOpenClass: document.body.classList.contains('threew-mobile-filters-open'),
    };
  });

  if (!state.url.includes(targetPattern) || !state.shell || Math.abs(state.shell.x) > 2 || state.hasOpenClass) {
    console.log('FAIL category navigation left mobile filter/page in pushed-open state');
    console.log(JSON.stringify(state, null, 2));
    await browser.close();
    process.exit(1);
  }

  await page.evaluate(() => document.querySelector('.porto-product-filters-toggle.sidebar-toggle')?.click());
  await page.waitForTimeout(500);

  const reopened = await page.evaluate(() => {
    const box = (sel) => {
      const el = document.querySelector(sel);
      if (!el) return null;
      const r = el.getBoundingClientRect();
      return { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height), text: (el.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 80) };
    };
    return {
      body: document.body.className,
      sidebar: box('.sidebar.mobile-sidebar'),
      hasOpenClass: document.body.classList.contains('threew-mobile-filters-open'),
      overlays: Array.from(document.querySelectorAll('.sidebar-overlay')).map(el => ({ cls: el.className, w: Math.round(el.getBoundingClientRect().width), h: Math.round(el.getBoundingClientRect().height) })),
    };
  });

  if (!reopened.hasOpenClass || !reopened.sidebar || reopened.sidebar.x !== 0) {
    console.log('FAIL category navigation broke next filter open');
    console.log(JSON.stringify({ state, reopened }, null, 2));
    await browser.close();
    process.exit(1);
  }

  console.log('PASS sidebar category navigation returns to normal page state and filter reopens');
  await browser.close();
})().catch(err => { console.error(err.stack || err.message); process.exit(2); });
