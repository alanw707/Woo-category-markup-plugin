const { chromium } = require('playwright');

const urls = process.argv.slice(2);
const targets = urls.length ? urls : [
  'https://shop.3wdistributing.com/',
  'https://shop.3wdistributing.com/product-category/brabus/brabus-mercedes/g-wagon/gwagon-w465/',
  'https://shop.3wdistributing.com/product/brabus-carbon-fiber-shifter-paddles-for-amg-steering-wheel/',
  'https://shop.3wdistributing.com/?s=brabus',
];

const mobileUserAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const failures = [];

  for (const url of targets) {
    const page = await browser.newPage({ viewport: { width: 375, height: 812 }, userAgent: mobileUserAgent });
    const checkedUrl = new URL(url);
    checkedUrl.searchParams.set('threew_header_check', String(Date.now()));
    await page.goto(checkedUrl.toString(), { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(2500);

    const result = await page.evaluate((pageUrl) => {
      const visible = (el) => {
        if (!el) return false;
        const r = el.getBoundingClientRect();
        const s = getComputedStyle(el);
        return r.width >= 1 && r.height >= 1 && s.display !== 'none' && s.visibility !== 'hidden' && Number(s.opacity) > 0;
      };
      const box = (el) => {
        if (!el) return null;
        const r = el.getBoundingClientRect();
        return { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height), right: Math.round(r.right) };
      };
      const firstVisible = (selector) => Array.from(document.querySelectorAll(selector)).find(visible) || null;

      const toggle = firstVisible('.mobile-toggle');
      const cart = firstVisible('#mini-cart, .mini-cart, .cart-toggle, #mini-cart > a');
      const cartIcon = firstVisible('#mini-cart i, .mini-cart i, .cart-toggle i, #mini-cart .cart-icon');
      const search = firstVisible('.threew-mobile-header-search');
      const header = firstVisible('#header, header');
      const toggleBox = box(toggle);
      const cartBox = box(cart);
      const searchBox = box(search);
      const headerBox = box(header);
      const style = toggle ? getComputedStyle(toggle) : null;
      const before = toggle ? getComputedStyle(toggle, '::before') : null;
      const centerTop = toggleBox ? document.elementFromPoint(toggleBox.x + Math.round(toggleBox.w / 2), toggleBox.y + Math.round(toggleBox.h / 2)) : null;
      const cartIconBox = box(cartIcon);
      const cartHitBox = cartIconBox || cartBox;
      const cartCenterTop = cartHitBox ? document.elementFromPoint(cartHitBox.x + Math.round(cartHitBox.w / 2), cartHitBox.y + Math.round(cartHitBox.h / 2)) : null;
      const bgBars = !!style && style.backgroundImage.includes('linear-gradient');
      const beforeGlyph = !!before && !['none', 'normal', '""'].includes(before.content);
      const childGlyph = !!toggle && Array.from(toggle.querySelectorAll('i, span')).some(visible);
      const glyphSources = [bgBars, beforeGlyph, childGlyph].filter(Boolean).length;

      return {
        url: pageUrl,
        bodyClasses: document.body.className,
        toggle: toggleBox,
        cart: cartBox,
        cartIcon: cartIconBox,
        search: searchBox,
        header: headerBox,
        toggleTopmost: !!(toggle && centerTop && (centerTop === toggle || toggle.contains(centerTop))),
        cartTopmost: !!(cart && cartCenterTop && (cartCenterTop === cart || cart.contains(cartCenterTop))),
        centerTop: centerTop ? `${centerTop.tagName}.${String(centerTop.className).replace(/\s+/g, '.')}` : null,
        cartCenterTop: cartCenterTop ? `${cartCenterTop.tagName}.${String(cartCenterTop.className).replace(/\s+/g, '.')}` : null,
        bgBars,
        beforeGlyph,
        childGlyph,
        glyphSources,
        cartGap: cartBox && toggleBox ? toggleBox.x - cartBox.right : null,
        searchGap: searchBox && cartBox ? cartBox.x - searchBox.right : null,
        centerDelta: searchBox && toggleBox ? Math.abs((toggleBox.y + toggleBox.h / 2) - (searchBox.y + searchBox.h / 2)) : null,
        headerHeight: headerBox ? headerBox.h : null,
        menuRightInset: toggleBox ? Math.round(window.innerWidth - toggleBox.right) : null,
      };
    }, url);

    const ok = result.headerHeight >= 80 && result.headerHeight <= 96 &&
      result.toggle && result.toggle.w >= 44 && result.toggle.h >= 44 && result.menuRightInset >= 0 && result.menuRightInset <= 12 &&
      result.cart && result.cart.w >= 44 && result.cart.h >= 44 && result.cartIcon && result.cartIcon.w >= 16 && result.cartIcon.h >= 16 &&
      result.search && result.search.w >= 150 && result.search.h >= 44 &&
      result.toggleTopmost && result.cartTopmost && result.bgBars && result.glyphSources === 1 &&
      (result.centerDelta === null || result.centerDelta <= 4) &&
      result.cartGap >= 8 && result.cartGap <= 24 &&
      result.searchGap >= 8 && result.searchGap <= 24;

    if (!ok) failures.push(result);
    await page.close();
  }

  await browser.close();

  if (failures.length) {
    console.log('FAIL mobile header standard');
    console.log(JSON.stringify(failures, null, 2));
    process.exit(1);
  }

  console.log('PASS mobile header standard');
})();
