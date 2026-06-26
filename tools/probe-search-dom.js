const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  await page.goto('https://shop.3wdistributing.com/?s=wheels', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(5000);
  const probe = await page.evaluate(() => {
    const body = document.body.className;
    const qlwappEls = Array.from(document.querySelectorAll('[class*="qlwapp"], a[href*="wa.me"], a[href*="whatsapp"], .joinchat__button')).map(el => ({
      cls: el.className, tag: el.tagName,
      bottom: window.getComputedStyle(el).bottom,
      zIndex: window.getComputedStyle(el).zIndex,
      vis: el.getBoundingClientRect().height > 0,
    }));
    const rc = document.querySelector('.woocommerce-result-count');
    const ordering = document.querySelector('.woocommerce-ordering');
    const slb = document.querySelector('.shop-loop-before');
    const cart = document.querySelector('#mini-cart, .mini-cart');
    const cartA = cart ? cart.querySelector('a') : null;
    return {
      bodyClass: body,
      hasWoo: body.includes('woocommerce'),
      hasSearch: body.includes('search'),
      qlwappEls,
      resultCount: rc ? { display: window.getComputedStyle(rc).display, text: rc.textContent.trim().slice(0, 40) } : 'NONE',
      ordering: ordering ? 1 : 0,
      shopLoopBefore: slb ? slb.innerHTML.replace(/\s+/g, ' ').slice(0, 250) : 'NONE',
      cart: cart ? { cls: cart.className, aMinW: cartA ? window.getComputedStyle(cartA).minWidth : null, aMinH: cartA ? window.getComputedStyle(cartA).minHeight : null } : 'NONE',
    };
  });
  console.log(JSON.stringify(probe, null, 2));
  await browser.close();
})();
