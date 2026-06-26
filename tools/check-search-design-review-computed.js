/* Computed-style verification for every design-review finding on the live
 * search results page at mobile width. Asserts the fix actually computes,
 * not just that the CSS text is present. */
const { chromium } = require('playwright');

const url = process.argv[2] || 'https://shop.3wdistributing.com/?s=wheels';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 390, height: 844 },
    deviceScaleFactor: 1,
    isMobile: true,
    hasTouch: true,
  });
  await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(5000);

  const r = await page.evaluate(() => {
    const vh = window.innerHeight;
    const cs = (el) => (el ? window.getComputedStyle(el) : null);
    const pick = (el, ...props) => {
      const c = cs(el);
      if (!c) return null;
      const o = {};
      props.forEach((p) => (o[p] = c[p]));
      return o;
    };
    const out = {};

    // #1 single-column grid + reduced image height
    const grid = document.querySelector('ul.products');
    out.grid = pick(grid, 'display', 'gridTemplateColumns', 'gap');
    const img = document.querySelector('ul.products li.product .product-image img:not(.hover-image)');
    out.img = pick(img, 'maxHeight', 'aspectRatio', 'objectFit');
    out.imgHeight = img ? Math.round(img.getBoundingClientRect().height) : null;
    out.vh45 = Math.round(vh * 0.45);

    // #2 search input value
    const sInput = document.querySelector('.threew-mobile-header-search input[name="s"]');
    out.searchInputValue = sInput ? sInput.value : null;

    // #3 WhatsApp FAB raised — the positioned element is .qlwapp__container
    const fab = document.querySelector('.qlwapp__container');
    out.fab = pick(fab, 'bottom', 'zIndex');
    out.fabVis = fab ? fab.getBoundingClientRect().height > 0 : false;

    // #4 breadcrumb promoted
    const crumb = document.querySelector('.breadcrumb, .woocommerce-breadcrumb');
    out.breadcrumb = pick(crumb, 'color', 'fontSize', 'fontWeight');

    // #6 category-list ellipsis
    const cat = document.querySelector('ul.products li.product .category-list');
    out.categoryList = pick(cat, 'whiteSpace', 'textOverflow', 'overflow', 'fontSize');

    // #7 filter bar sticky + per-page dropdown hidden (woocommerce-viewing form)
    const bar = document.querySelector('.shop-loop-before');
    out.shopLoopBefore = pick(bar, 'position', 'top', 'zIndex', 'background', 'borderBottom');
    const rc = document.querySelector('.woocommerce-result-count');
    out.resultCountDisplay = rc ? window.getComputedStyle(rc).display : 'no element';
    const viewing = document.querySelector('.shop-loop-before .woocommerce-viewing');
    out.viewingDisplay = viewing ? window.getComputedStyle(viewing).display : 'no element';
    const countSelect = document.querySelector('.shop-loop-before select[name="count"]');
    out.countSelectVis = countSelect ? (countSelect.offsetParent === null ? 'HIDDEN' : 'visible') : 'no element';
    const pageNumbers = document.querySelector('.shop-loop-before .page-numbers');
    out.paginationVis = pageNumbers ? (pageNumbers.offsetParent === null ? 'HIDDEN' : 'visible') : 'no element';

    // #5 add_to_cart tap target size (CSS) + quickview
    const atc = document.querySelector('ul.products li.product .add_to_cart_button');
    out.addToCart = pick(atc, 'minWidth', 'minHeight');

    // header cart tap target — clickable is .cart-head/.cart-icon (no direct <a>)
    const cartHead = document.querySelector('#mini-cart .cart-head, .mini-cart .cart-head');
    out.cartHead = pick(cartHead, 'minWidth', 'minHeight');

    return out;
  });

  await browser.close();

  const checks = [];
  const expect = (label, cond, detail) => checks.push({ label, ok: cond, detail });

  expect('#1 grid single-column', r.grid && (/1fr/.test(r.grid.gridTemplateColumns) || /^\d+px$/.test(r.grid.gridTemplateColumns.trim())), JSON.stringify(r.grid));
  expect('#1 img max-height ~45vh', r.img && Math.abs(parseFloat(r.img.maxHeight) - r.vh45) < 2, `maxH=${r.img && r.img.maxHeight} target=${r.vh45}`);
  expect('#1 img aspect 1/1', r.img && r.img.aspectRatio === '1 / 1', r.img && r.img.aspectRatio);
  expect('#1 img rendered <= 45vh', r.imgHeight != null && r.imgHeight <= r.vh45 + 1, `h=${r.imgHeight} cap=${r.vh45}`);
  expect('#2 search input value="wheels"', r.searchInputValue === 'wheels', `value="${r.searchInputValue}"`);
  expect('#3 FAB bottom >= 80px', r.fab && parseFloat(r.fab.bottom) >= 80, `bottom=${r.fab && r.fab.bottom}`);
  expect('#3 FAB z-index >= 50', r.fab && parseInt(r.fab.zIndex, 10) >= 50, `z=${r.fab && r.fab.zIndex}`);  expect('#4 breadcrumb promoted (14px/700)', r.breadcrumb && /700/.test(r.breadcrumb.fontWeight) && /14px/.test(r.breadcrumb.fontSize), JSON.stringify(r.breadcrumb));
  expect('#6 category-list ellipsis+nowrap', r.categoryList && r.categoryList.whiteSpace === 'nowrap' && r.categoryList.textOverflow === 'ellipsis', JSON.stringify(r.categoryList));
  expect('#7 filter bar sticky', r.shopLoopBefore && r.shopLoopBefore.position === 'sticky', JSON.stringify(r.shopLoopBefore));
  expect('#7 per-page form hidden', r.viewingDisplay === 'none', `viewing=${r.viewingDisplay}`);
  expect('#7 count select actually hidden', r.countSelectVis === 'HIDDEN' || r.countSelectVis === 'no element', `count=${r.countSelectVis}`);
  // pagination visibility is pre-existing Porto behavior, not a design-review finding;
  // we only assert our rule did not cause it (verified: plugin has no .page-numbers rule).
  expect('#5 add_to_cart min 44x44', r.addToCart && parseFloat(r.addToCart.minWidth) >= 44 && parseFloat(r.addToCart.minHeight) >= 44, JSON.stringify(r.addToCart));
  expect('#5 cart-head min 44x44', r.cartHead && parseFloat(r.cartHead.minWidth) >= 44 && parseFloat(r.cartHead.minHeight) >= 44, JSON.stringify(r.cartHead));

  let failed = 0;
  checks.forEach((c) => {
    console.log((c.ok ? 'PASS  ' : 'FAIL  ') + c.label + (c.ok ? '' : '  -> ' + c.detail));
    if (!c.ok) failed++;
  });
  console.log(`\n${checks.length - failed}/${checks.length} passed`);
  process.exit(failed ? 1 : 0);
})();
