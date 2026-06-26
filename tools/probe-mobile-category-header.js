const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 375, height: 812 },
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
  });

  await page.goto('https://shop.3wdistributing.com/product-category/brabus/brabus-mercedes/g-wagon/gwagon-w465/?vcheck=123', { waitUntil: 'networkidle', timeout: 60000 });

  const data = await page.evaluate(() => {
    const box = (selector) => {
      const candidates = Array.from(document.querySelectorAll(selector))
        .filter((el) => {
          const r = el.getBoundingClientRect();
          const s = getComputedStyle(el);
          return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden' && Number(s.opacity) > 0;
        });
      const el = candidates.sort((a, b) => {
        const ar = a.getBoundingClientRect();
        const br = b.getBoundingClientRect();
        return (br.width * br.height) - (ar.width * ar.height);
      })[0];
      if (!el) return null;
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return {
        x: Math.round(r.x),
        y: Math.round(r.y),
        w: Math.round(r.width),
        h: Math.round(r.height),
        display: s.display,
        opacity: Number(s.opacity),
        color: s.color,
        shadow: s.boxShadow,
      };
    };

    return {
      header: box('#header, header'),
      logo: box('.logo img, .header-logo img'),
      search: box('.header-center .searchform-popup.advanced-search-layout, .threew-mobile-header-search'),
      input: box('.header-center .searchform-popup.advanced-search-layout input[type="search"], .header-center .searchform-popup.advanced-search-layout input[type="text"], .threew-mobile-header-search input[type="search"], .threew-mobile-header-search input[type="text"]'),
      button: box('.header-center .searchform-popup.advanced-search-layout button[type="submit"], .threew-mobile-header-search button[type="submit"]'),
      cart: box('#mini-cart, .mini-cart, #mini-cart > a, .mini-cart > a, .cart-toggle'),
      menu: box('.mobile-toggle'),
    };
  });

  await browser.close();

  const ok = {
    headerCompact: data.header && data.header.h <= 90,
    logoSmall: data.logo && data.logo.w <= 48,
    searchUsable: data.search && data.input && data.search.w >= 150 && data.input.h >= 20 && data.button && data.button.w >= 44 && data.button.h >= 44,
    tapTargets: data.cart && data.menu && data.cart.w >= 44 && data.cart.h >= 44 && data.menu.w >= 44 && data.menu.h >= 44,
    separated: data.header && data.header.shadow !== 'none',
    noOverlap: data.search && data.menu && data.search.x + data.search.w <= data.menu.x - 8,
  };

  console.log(JSON.stringify({ data, ok, pass: Object.values(ok).every(Boolean) }, null, 2));
  if (!Object.values(ok).every(Boolean)) process.exit(1);
})();
