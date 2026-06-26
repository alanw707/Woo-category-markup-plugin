const { chromium } = require('playwright');

const url = process.argv[2] || 'https://shop.3wdistributing.com/product-category/mansory/gwagon-w463/mansory-gwagon-w463-wheels/';
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
  checked.searchParams.set('badgestripe', String(Date.now()));
  await page.goto(checked.toString(), { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(2500);

  const addButton = page.locator('a.add_to_cart_button, button.add_to_cart_button').first();
  await addButton.click();
  await page.waitForTimeout(2500);

  await page.evaluate(() => {
    const visible = (els) => els.find((el) => {
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden';
    });
    const cart = visible(Array.from(document.querySelectorAll('#mini-cart, .mini-cart')));
    const badge = cart ? visible(Array.from(cart.querySelectorAll('.cart-items, .cart-icon, .cart-head'))) : null;
    if (badge) badge.click();
  });
  await page.waitForTimeout(700);

  const state = await page.evaluate(() => {
    const box = (el) => {
      if (!el) return null;
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return {
        cls: el.className,
        text: (el.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 120),
        x: Math.round(r.x),
        y: Math.round(r.y),
        w: Math.round(r.width),
        h: Math.round(r.height),
        display: s.display,
        visibility: s.visibility,
        opacity: s.opacity,
        position: s.position,
        bg: s.backgroundColor,
        z: s.zIndex,
      };
    };

    const visibleCart = Array.from(document.querySelectorAll('#mini-cart, .mini-cart')).find((el) => {
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden';
    });

    return {
      cartClasses: visibleCart ? visibleCart.className : null,
      clonePopup: box(document.querySelector('.threew-mobile-cart-popup')),
      originalPopup: box(visibleCart ? visibleCart.querySelector('.cart-popup') : null),
      originalTotalCount: box(visibleCart ? visibleCart.querySelector('.cart-popup .total-count') : null),
      header: box(document.querySelector('#header')),
      bodyClasses: document.body.className,
    };
  });

  await browser.close();

  const cloneVisible = isVisibleBox(state.clonePopup);
  const originalVisible = isVisibleBox(state.originalPopup);
  const stripeVisible = isVisibleBox(state.originalTotalCount) && state.originalTotalCount.y < (state.clonePopup ? state.clonePopup.y : 9999);
  const pass = cloneVisible && !originalVisible && !stripeVisible;

  console.log(JSON.stringify({ url: checked.toString(), cloneVisible, originalVisible, stripeVisible, state, pass }, null, 2));
  if (!pass) process.exit(1);
})();
