const { chromium } = require('playwright');

const url = process.argv[2] || 'https://shop.3wdistributing.com/';
const mobileUserAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

(async () => {
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage({ viewport: { width: 375, height: 812 }, userAgent: mobileUserAgent });
    const checked = new URL(url);
    checked.searchParams.set('threew_header_four_issue_check', String(Date.now()));
    await page.goto(checked.toString(), { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(2500);

    const result = await page.evaluate(() => {
      const visible = (el) => {
        if (!el) return false;
        const r = el.getBoundingClientRect();
        const s = getComputedStyle(el);
        return r.width >= 1 && r.height >= 1 && s.display !== 'none' && s.visibility !== 'hidden' && Number(s.opacity) > 0;
      };
      const firstVisible = (selector) => Array.from(document.querySelectorAll(selector)).find(visible) || null;
      const box = (selector) => {
        const el = firstVisible(selector);
        if (!el) return null;
        const r = el.getBoundingClientRect();
        return { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height), right: Math.round(r.right), bottom: Math.round(r.bottom) };
      };

      const header = box('#header, header');
      const logo = box('.logo img, .header-logo img');
      const search = box('.header-center .searchform-popup, .threew-mobile-header-search');
      const cart = box('#mini-cart, .mini-cart, .cart-toggle');
      const menu = box('.mobile-toggle');
      const hero = box('.home-banner, .porto-ibanner');
      const controls = [logo, search, cart, menu].filter(Boolean);
      const centers = controls.map((b) => b.y + b.h / 2);
      const centerSpread = centers.length ? Math.round(Math.max(...centers) - Math.min(...centers)) : 999;

      return { header, logo, search, cart, menu, hero, centerSpread };
    });

    const checks = {
      searchVisible: !!(result.search && result.search.w >= 150 && result.search.h >= 40),
      horizontalAligned: !!(result.logo && result.search && result.cart && result.menu && result.logo.x >= 24 && result.logo.right < result.search.x && result.search.right < result.cart.x && result.cart.right < result.menu.x && result.menu.right <= 367),
      verticalAligned: result.centerSpread <= 6,
      compactHeader: !!(result.header && result.header.h >= 64 && result.header.h <= 76),
      heroFlush: !!(result.header && result.hero && Math.abs(result.hero.y - result.header.bottom) <= 2 && result.hero.x <= 1 && result.hero.w >= 373),
    };

    if (!Object.values(checks).every(Boolean)) {
      console.log('FAIL home header four issues');
      console.log(JSON.stringify({ result, checks }, null, 2));
      process.exit(1);
    }

    console.log('PASS home header four issues');
    console.log(JSON.stringify({ result, checks }, null, 2));
  } catch (error) {
    console.log('FAIL home header four issues: ' + (error && error.message ? error.message : error));
    process.exit(1);
  } finally {
    await browser.close();
  }
})();
