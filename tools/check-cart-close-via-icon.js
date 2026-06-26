const { chromium } = require('playwright');

const url = process.argv[2] || 'https://shop.3wdistributing.com/product-category/brabus/brabus-mercedes/g-wagon/gwagon-w465/';
const ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

function isVisibleBox(box) {
  return !!(box && box.w > 0 && box.h > 0 && box.display !== 'none' && box.visibility !== 'hidden' && Number(box.opacity) > 0);
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 390, height: 844 },
    isMobile: true,
    hasTouch: true,
    userAgent: ua,
  });

  const checked = new URL(url);
  checked.searchParams.set('iconclosecheck', String(Date.now()));
  await page.goto(checked.toString(), { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(2500);

  async function clickCart() {
    await page.evaluate(() => {
      const visible = (els) => els.find((el) => {
        const r = el.getBoundingClientRect();
        const s = getComputedStyle(el);
        return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden';
      });
      const cart = visible(Array.from(document.querySelectorAll('#mini-cart, .mini-cart')));
      const hit = cart ? visible(Array.from(cart.querySelectorAll('.cart-head, .cart-icon, .cart-items'))) : null;
      if (hit) hit.click();
    });
  }

  const snapshot = async () => page.evaluate(() => {
    const visible = (els) => els.find((el) => {
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden';
    });
    const box = (el) => {
      if (!el) return null;
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return {
        cls: el.className,
        x: Math.round(r.x),
        y: Math.round(r.y),
        w: Math.round(r.width),
        h: Math.round(r.height),
        display: s.display,
        visibility: s.visibility,
        opacity: s.opacity,
      };
    };
    const cart = visible(Array.from(document.querySelectorAll('#mini-cart, .mini-cart')));
    return {
      cartClass: cart ? cart.className : null,
      clone: box(document.querySelector('.threew-mobile-cart-popup')),
      original: box(cart ? cart.querySelector('.cart-popup') : null),
    };
  });

  const before = await snapshot();
  await clickCart();
  await page.waitForTimeout(600);
  const opened = await snapshot();
  await clickCart();
  await page.waitForTimeout(600);
  const closed = await snapshot();

  await browser.close();

  const pass = !isVisibleBox(before.clone) && isVisibleBox(opened.clone) && !isVisibleBox(closed.clone) && !isVisibleBox(closed.original) && !/\bopen\b/.test(closed.cartClass || '') && !/threew-cart-open/.test(closed.cartClass || '');
  console.log(JSON.stringify({ url: checked.toString(), before, opened, closed, pass }, null, 2));
  if (!pass) process.exit(1);
})();
