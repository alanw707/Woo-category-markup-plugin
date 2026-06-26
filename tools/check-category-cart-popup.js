const { chromium } = require('playwright');

const url = process.argv[2] || 'https://shop.3wdistributing.com/product-category/brabus/brabus-mercedes/g-wagon/gwagon-w465/';
const ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 390, height: 844 },
    isMobile: true,
    hasTouch: true,
    userAgent: ua,
  });

  const checked = new URL(url);
  checked.searchParams.set('cartcheck', String(Date.now()));
  await page.goto(checked.toString(), { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(2500);

  const before = await page.evaluate(() => {
    const visible = (selector) => Array.from(document.querySelectorAll(selector)).find((el) => {
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden';
    }) || null;
    const cart = visible('#mini-cart, .mini-cart, .cart-toggle');
    if (!cart) return { cart: null, popup: null };
    const r = cart.getBoundingClientRect();
    return { cart: { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) }, popup: !!visible('.threew-mobile-cart-popup') };
  });

  await page.evaluate(() => {
    const cart = Array.from(document.querySelectorAll('#mini-cart, .mini-cart, .cart-toggle')).find((el) => {
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden';
    });
    if (cart) cart.click();
  });
  await page.waitForTimeout(500);

  const after = await page.evaluate(() => {
    const popup = document.querySelector('.threew-mobile-cart-popup');
    const cart = Array.from(document.querySelectorAll('#mini-cart, .mini-cart, .cart-toggle')).find((el) => {
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden';
    });
    const popupRect = popup ? popup.getBoundingClientRect() : null;
    return {
      cartOpen: !!(cart && cart.classList.contains('threew-cart-open')),
      popupVisible: !!(popup && popupRect.width > 0 && popupRect.height > 0),
      popupRect: popupRect ? {
        x: Math.round(popupRect.x),
        y: Math.round(popupRect.y),
        w: Math.round(popupRect.width),
        h: Math.round(popupRect.height),
      } : null,
      bodyClasses: document.body.className,
    };
  });

  await browser.close();

  const pass = !!(before.cart && after.cartOpen && after.popupVisible);
  console.log(JSON.stringify({ url: checked.toString(), before, after, pass }, null, 2));
  if (!pass) process.exit(1);
})();
