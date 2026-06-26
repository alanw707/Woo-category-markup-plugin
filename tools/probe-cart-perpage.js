const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  await page.goto('https://shop.3wdistributing.com/?s=wheels', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(5000);
  const probe = await page.evaluate(() => {
    const cart = document.querySelector('#mini-cart, .mini-cart');
    const directA = cart ? cart.querySelector(':scope > a') : null;
    const anyA = cart ? cart.querySelector('a') : null;
    const cartInfo = cart ? {
      outerHTMLStart: cart.outerHTML.slice(0, 200),
      directChildA: directA ? { tag: directA.tagName, minW: getComputedStyle(directA).minWidth, minH: getComputedStyle(directA).minHeight, w: Math.round(directA.getBoundingClientRect().width), h: Math.round(directA.getBoundingClientRect().height) } : 'NONE',
      firstA: anyA ? { tag: anyA.tagName, cls: anyA.className, minW: getComputedStyle(anyA).minWidth, minH: getComputedStyle(anyA).minHeight, w: Math.round(anyA.getBoundingClientRect().width), h: Math.round(anyA.getBoundingClientRect().height), parentCls: anyA.parentElement.className } : 'NONE',
    } : 'NO CART';
    // per-page / page-size dropdown in shop-loop-before
    const slb = document.querySelector('.shop-loop-before');
    const selects = slb ? Array.from(slb.querySelectorAll('select')).map(s => ({ name: s.name, cls: s.className, val: s.value, vis: getComputedStyle(s).display })) : [];
    const yithPerPage = document.querySelector('.yith-wcan-per-page, .products-per-page, [name="ppp"], .per-page');
    return {
      cartInfo,
      slbSelects: selects,
      perPageEl: yithPerPage ? { tag: yithPerPage.tagName, cls: yithPerPage.className, vis: getComputedStyle(yithPerPage).display } : 'NONE',
      slbFullText: slb ? slb.textContent.replace(/\s+/g, ' ').trim().slice(0, 200) : 'NONE',
    };
  });
  console.log(JSON.stringify(probe, null, 2));
  await browser.close();
})();
