const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
  await page.goto('https://shop.3wdistributing.com/?s=wheels', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(5000);
  const probe = await page.evaluate(() => {
    const viewing = document.querySelector('.shop-loop-before .woocommerce-viewing');
    const countSel = document.querySelector('.shop-loop-before select[name="count"]');
    const pag = document.querySelector('.shop-loop-before .page-numbers');
    const pagNav = document.querySelector('.woocommerce-pagination');
    // is pagination hidden by my rule? check matching CSS rules
    const pagMatches = pag ? (pag.matches('.woocommerce-viewing') ? 'MATCHES .woocommerce-viewing' : 'does not match .woocommerce-viewing') : 'no pag';
    // actual visibility via offsetParent
    const vis = (el) => el ? (el.offsetParent === null ? 'HIDDEN' : 'visible') : 'no el';
    // check for load-more / infinite scroll
    const loadMore = document.querySelector('.shop-loop-after .load-more, .porto-load-more, .pagination-load-more, .yith-infs-button, .woocommerce-load-more');
    return {
      viewingDisplay: viewing ? getComputedStyle(viewing).display : 'no el',
      viewingVis: vis(viewing),
      countSelDisplay: countSel ? getComputedStyle(countSel).display : 'no el',
      countSelVis: vis(countSel),
      pagDisplay: pag ? getComputedStyle(pag).display : 'no el',
      pagVis: vis(pag),
      pagParent: pag ? pag.parentElement.className : 'no pag',
      pagMatches,
      pagNavDisplay: pagNav ? getComputedStyle(pagNav).display : 'no nav',
      loadMore: loadMore ? { tag: loadMore.tagName, cls: loadMore.className, vis: vis(loadMore) } : 'NONE',
    };
  });
  console.log(JSON.stringify(probe, null, 2));
  await browser.close();
})();
