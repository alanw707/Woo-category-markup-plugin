const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 390, height: 844 },
    isMobile: true,
    hasTouch: true,
  });

  await page.goto('https://shop.3wdistributing.com/?floating-cart-check=' + Date.now(), {
    waitUntil: 'domcontentloaded',
    timeout: 60000,
  });
  await page.waitForLoadState('load', { timeout: 30000 }).catch(() => {});
  await page.waitForTimeout(3000);

  const result = await page.evaluate(() => {
    const rect = (selector) => {
      const el = document.querySelector(selector);
      if (!el) return null;
      const r = el.getBoundingClientRect();
      return {
        x: Math.round(r.x),
        y: Math.round(r.y),
        w: Math.round(r.width),
        h: Math.round(r.height),
        right: Math.round(r.right),
        bottom: Math.round(r.bottom),
      };
    };
    const cart = rect('#moderncart-floating-cart');
    const whatsapp = rect('.qlwapp__container, .qlwapp-toggle, .qlwapp__button');
    const separated = cart && whatsapp && cart.right < whatsapp.x;
    const cartLowerLeft = cart && cart.x <= 32 && cart.bottom >= window.innerHeight - 32;
    return { cart, whatsapp, separated, cartLowerLeft };
  });

  await page.screenshot({ path: 'docs/screenshots/floating-cart-position.png', fullPage: false });
  await browser.close();

  const pass = !!(result.cart && result.whatsapp && result.separated && result.cartLowerLeft);
  console.log(JSON.stringify({ ...result, pass }, null, 2));
  if (!pass) process.exit(1);
})().catch((error) => {
  console.error(error.stack || error);
  process.exit(1);
});
