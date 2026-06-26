const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  await page.goto('https://shop.3wdistributing.com/?product_cat=ast-suspension', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(5000);
  const data = await page.evaluate(() => {
    const rect = (el) => {
      if (!el) return null;
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height), display: s.display, outline: s.outline, borderTop: s.borderTop, boxShadow: s.boxShadow };
    };
    const searchInput = Array.from(document.querySelectorAll('.header-center .searchform-popup.advanced-search-layout input[name="s"], .threew-mobile-header-search input[name="s"]')).find(el => el.getBoundingClientRect().width > 0 && getComputedStyle(el).display !== 'none');
    const searchWrap = searchInput ? searchInput.closest('.searchform-popup.advanced-search-layout, .threew-mobile-header-search, .searchform.search-layout-advanced') : null;
    const cart = document.querySelector('#mini-cart .cart-head, .mini-cart .cart-head, .header-right .mini-cart, .header-main .mini-cart');
    const menu = document.querySelector('.mobile-toggle, .threew-mobile-menu-proxy, .menu-toggle');
    return {
      bodyClass: document.body.className,
      searchInput: rect(searchInput),
      searchWrap: rect(searchWrap),
      cart: rect(cart),
      menu: rect(menu),
      menuTag: menu ? { tag: menu.tagName, cls: menu.className } : null,
      cartTag: cart ? { tag: cart.tagName, cls: cart.className } : null,
    };
  });
  console.log(JSON.stringify(data, null, 2));
  await browser.close();
})();
