const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  await page.goto('https://shop.3wdistributing.com/?product_cat=ast-suspension', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(5000);

  const input = page.locator('.header-center .searchform-popup.advanced-search-layout input[name="s"]:visible').first();
  await input.evaluate((el) => el.focus());
  await page.waitForTimeout(300);

  const data = await page.evaluate(() => {
    const rect = (el) => {
      if (!el) return null;
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return {
        x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height),
        borderTop: s.borderTop, outline: s.outline, boxShadow: s.boxShadow,
      };
    };
    const visible = (sel) => Array.from(document.querySelectorAll(sel)).find((el) => {
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden';
    }) || null;

    const input = visible('.header-center .searchform-popup.advanced-search-layout input[name="s"]');
    const wrap = input ? input.closest('.searchform-popup.advanced-search-layout') : null;
    const cart = visible('#mini-cart .cart-head, .mini-cart .cart-head');
    const menu = visible('.mobile-toggle, .threew-mobile-menu-proxy, .menu-toggle');

    return { input: rect(input), wrap: rect(wrap), cart: rect(cart), menu: rect(menu) };
  });

  console.log(JSON.stringify(data, null, 2));

  const noInputOutline = data.input && data.input.outline.startsWith('rgb(119, 119, 119) none') || data.input && data.input.outline.startsWith('0px');
  const noWrapBorder = data.wrap && data.wrap.borderTop.startsWith('0px');
  const alignedTop = data.wrap && data.cart && data.menu && Math.abs(data.wrap.y - data.cart.y) <= 1 && Math.abs(data.wrap.y - data.menu.y) <= 1;
  const alignedHeight = data.wrap && data.cart && data.menu && Math.abs(data.wrap.h - data.cart.h) <= 1 && Math.abs(data.wrap.h - data.menu.h) <= 1;

  if (!(noInputOutline && noWrapBorder && alignedTop && alignedHeight)) {
    console.log('FAIL focus/align');
    process.exit(1);
  }

  console.log('PASS mobile search focus ring removed and wrapper aligned with cart/menu');
  await browser.close();
})();
