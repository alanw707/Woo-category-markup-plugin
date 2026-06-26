const fs = require('fs');
const { chromium } = require('playwright');

const HOME_URL = 'https://shop.3wdistributing.com/';
const CATEGORY_URL = 'https://shop.3wdistributing.com/product-category/brabus/brabus-mercedes/g-wagon/gwagon-w465/';
const UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
const PLUGIN_PATH = 'storefront-polish-hotfix/threew-storefront-polish-hotfix.php';

function withBust(url) {
  const u = new URL(url);
  u.searchParams.set('ninecheck', String(Date.now()));
  return u.toString();
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 375, height: 812 },
    isMobile: true,
    hasTouch: true,
    userAgent: UA,
  });

  const items = [];

  await page.goto(withBust(HOME_URL), { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(2500);

  const home = await page.evaluate(() => {
    const visible = (selector) => Array.from(document.querySelectorAll(selector)).find((el) => {
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden' && Number(s.opacity) > 0;
    }) || null;
    const box = (el) => {
      if (!el) return null;
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return {
        x: Math.round(r.x),
        y: Math.round(r.y),
        w: Math.round(r.width),
        h: Math.round(r.height),
        right: Math.round(r.right),
        bottom: Math.round(r.bottom),
        borderRadius: s.borderRadius,
        border: s.border,
        shadow: s.boxShadow,
      };
    };
    const search = visible('.header-center .searchform-popup.advanced-search-layout');
    const cart = visible('#mini-cart, .mini-cart, .cart-toggle');
    const menu = visible('.mobile-toggle');
    const logo = visible('.logo img, .header-logo img');
    const cartCenter = cart ? {
      x: Math.round(cart.getBoundingClientRect().x + (cart.getBoundingClientRect().width / 2)),
      y: Math.round(cart.getBoundingClientRect().y + (cart.getBoundingClientRect().height / 2)),
    } : null;
    const cartTop = cartCenter ? document.elementFromPoint(cartCenter.x, cartCenter.y) : null;
    return {
      logo: box(logo),
      search: box(search),
      cart: box(cart),
      menu: box(menu),
      cartTopTag: cartTop ? `${cartTop.tagName}.${String(cartTop.className).replace(/\s+/g, '.')}` : null,
      cartTopInsideCart: !!(cart && cartTop && (cartTop === cart || cart.contains(cartTop))),
    };
  });

  items.push({
    id: 1,
    name: 'home header row alignment',
    pass: !!(home.logo && home.search && home.cart && home.menu && home.logo.y === 14 && home.search.y === 14 && home.cart.y === 14 && home.menu.y === 14),
    evidence: home,
  });

  items.push({
    id: 2,
    name: 'home search containment and cart hitbox',
    pass: !!(home.search && home.cart && home.menu && home.search.right <= home.cart.x - 10 && home.cartTopInsideCart),
    evidence: home,
  });

  await page.evaluate(() => {
    const cart = Array.from(document.querySelectorAll('#mini-cart, .mini-cart')).find((el) => {
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden';
    });
    if (cart) cart.click();
  });
  await page.waitForTimeout(400);

  const cartPopup = await page.evaluate(() => {
    const popup = document.querySelector('.threew-mobile-cart-popup');
    if (!popup) return null;
    const r = popup.getBoundingClientRect();
    const s = getComputedStyle(popup);
    return {
      x: Math.round(r.x),
      y: Math.round(r.y),
      w: Math.round(r.width),
      h: Math.round(r.height),
      borderRadius: s.borderRadius,
      border: s.border,
      shadow: s.boxShadow,
    };
  });

  items.push({
    id: 3,
    name: 'home mini-cart panel presentation',
    pass: !!(cartPopup && cartPopup.x === 12 && cartPopup.y === 80 && cartPopup.w >= 340 && cartPopup.h >= 120 && cartPopup.borderRadius === '18px'),
    evidence: cartPopup,
  });

  await page.mouse.click(20, 260);
  await page.waitForTimeout(250);
  const cartDismissed = await page.evaluate(() => !document.querySelector('.threew-mobile-cart-popup'));
  items.push({
    id: 4,
    name: 'home mini-cart dismiss behavior',
    pass: cartDismissed,
    evidence: { cartDismissed },
  });

  await page.goto(withBust(CATEGORY_URL), { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(2500);

  const category = await page.evaluate(() => {
    const box = (selector) => {
      const el = document.querySelector(selector);
      if (!el) return null;
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return {
        text: el.textContent.replace(/\s+/g, ' ').trim().slice(0, 120),
        x: Math.round(r.x),
        y: Math.round(r.y),
        w: Math.round(r.width),
        h: Math.round(r.height),
        display: s.display,
        borderRadius: s.borderRadius,
        border: s.border,
        boxShadow: s.boxShadow,
      };
    };
    return {
      headerShell: box('.page-top.page-header-6'),
      compact: box('.threew-mobile-archive-header'),
      title: box('.threew-mobile-archive-title'),
      breadcrumb: box('.breadcrumb'),
      toolbar: box('.shop-loop-before'),
      filter: box('.shop-loop-before .porto-product-filters-toggle'),
      sort: box('.shop-loop-before .orderby'),
      count: box('.shop-loop-before .count'),
      card: box('ul.products li.product'),
      chat: box('.qlwapp__button'),
    };
  });

  items.push({
    id: 5,
    name: 'category mobile archive header removed',
    pass: !!(
      category.headerShell && category.headerShell.display === 'none' && category.headerShell.h === 0 &&
      category.compact && category.compact.h === 0 &&
      category.title && category.title.h === 0
    ),
    evidence: category,
  });

  items.push({
    id: 6,
    name: 'category legacy breadcrumb removed',
    pass: !!(category.breadcrumb && category.breadcrumb.display === 'none' && category.breadcrumb.h === 0),
    evidence: category,
  });

  items.push({
    id: 7,
    name: 'category toolbar compact controls',
    pass: !!(category.toolbar && category.toolbar.display === 'grid' && category.filter && category.filter.h >= 44 && category.sort && category.sort.h >= 44 && category.count && category.count.h >= 44),
    evidence: category,
  });

  items.push({
    id: 8,
    name: 'category product card shell',
    pass: !!(category.card && category.card.borderRadius === '20px' && /solid/.test(category.card.border) && category.card.boxShadow !== 'none'),
    evidence: category,
  });

  const plugin = fs.readFileSync(PLUGIN_PATH, 'utf8');
  const stateChecks = {
    filterFocus: plugin.includes('.shop-loop-before .porto-product-filters-toggle:focus-visible'),
    sortFocus: plugin.includes('.shop-loop-before .orderby:focus-visible'),
    countFocus: plugin.includes('.shop-loop-before .count:focus-visible'),
    cartCtaFocus: plugin.includes('.threew-mobile-cart-popup .total-count a:focus-visible'),
    chat44: !!(category.chat && category.chat.w === 44 && category.chat.h === 44),
  };
  items.push({
    id: 9,
    name: 'state consistency and safe-area chat sizing',
    pass: Object.values(stateChecks).every(Boolean),
    evidence: stateChecks,
  });

  await browser.close();

  const pass = items.every((item) => item.pass);
  console.log(JSON.stringify({ pass, items }, null, 2));
  if (!pass) process.exit(1);
})();
