const { chromium } = require('playwright');

const homeUrl = process.argv[2] || 'https://shop.3wdistributing.com/';
const compareUrl = process.argv[3] || 'https://shop.3wdistributing.com/product-category/brabus/brabus-mercedes/g-wagon/gwagon-w465/';
const userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
const viewport = { width: 375, height: 812 };
const tolerance = 1;

function diffBox(label, a, b) {
  if (!a || !b) return [`${label} missing`];
  const keys = ['x', 'y', 'w', 'h'];
  return keys
    .filter((key) => Math.abs(a[key] - b[key]) > tolerance)
    .map((key) => `${label}.${key}: ${a[key]} vs ${b[key]}`);
}

(async () => {
  const browser = await chromium.launch({ headless: true });

  async function snap(url) {
    const page = await browser.newPage({ viewport, userAgent });
    const target = new URL(url);
    target.searchParams.set('threew_header_match_check', String(Date.now()));
    await page.goto(target.toString(), { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(2500);

    const data = await page.evaluate(() => {
      const visible = (el) => {
        if (!el) return false;
        const r = el.getBoundingClientRect();
        const s = getComputedStyle(el);
        return r.width > 1 && r.height > 1 && s.display !== 'none' && s.visibility !== 'hidden' && Number(s.opacity) > 0;
      };
      const first = (selector) => Array.from(document.querySelectorAll(selector)).find(visible) || null;
      const box = (el) => {
        if (!el) return null;
        const r = el.getBoundingClientRect();
        return {
          x: Math.round(r.x),
          y: Math.round(r.y),
          w: Math.round(r.width),
          h: Math.round(r.height),
        };
      };

      return {
        header: box(first('#header, header')),
        logo: box(first('.logo, .header-logo')),
        search: box(first('.searchform-popup.advanced-search-layout')),
        cart: box(first('#mini-cart, .mini-cart, .cart-toggle')),
        menu: box(first('.mobile-toggle')),
      };
    });

    await page.close();
    return data;
  }

  const home = await snap(homeUrl);
  const compare = await snap(compareUrl);
  await browser.close();

  const failures = [
    ...diffBox('header', home.header, compare.header),
    ...diffBox('logo', home.logo, compare.logo),
    ...diffBox('search', home.search, compare.search),
    ...diffBox('cart', home.cart, compare.cart),
    ...diffBox('menu', home.menu, compare.menu),
  ];

  if (failures.length) {
    console.log('FAIL mobile header spacing match');
    console.log(JSON.stringify({ home, compare, failures }, null, 2));
    process.exit(1);
  }

  console.log('PASS mobile header spacing match');
})();
