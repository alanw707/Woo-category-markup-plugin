const { chromium } = require('playwright');

const viewport = { width: 375, height: 812 };
const userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
const categoryUrl = 'https://shop.3wdistributing.com/product-category/brabus/brabus-mercedes/g-wagon/gwagon-w465/';
const searchUrl = 'https://shop.3wdistributing.com/?s=tesla&post_type=product';

async function measure(page) {
  return page.evaluate(() => {
    const visible = (el) => {
      if (!el) return false;
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility !== 'hidden' && Number(s.opacity) !== 0 && r.bottom > 0 && r.top < 140;
    };
    const first = (selectors) => selectors.flatMap((sel) => Array.from(document.querySelectorAll(sel))).find(visible) || null;
    const box = (el) => {
      if (!el) return null;
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return {
        x: Math.round(r.x),
        y: Math.round(r.y),
        w: Math.round(r.width),
        h: Math.round(r.height),
        topCss: s.top,
        leftCss: s.left,
        rightCss: s.right,
        transform: s.transform,
        className: el.className,
      };
    };

    const cart = first(['#mini-cart', '.mini-cart', '.cart-toggle']);
    const badge = first(['#mini-cart .cart-items', '.mini-cart .cart-items', '#mini-cart .cart-badge', '.mini-cart .cart-badge', '#mini-cart .cart-count', '.mini-cart .cart-count']);
    const menu = first(['.mobile-toggle']);
    const logo = first(['.logo', '.header-logo']);
    const logoImg = first(['.logo img', '.header-logo img']);
    const header = first(['header', '#header']);

    return {
      bodyClass: document.body.className,
      headerClass: header ? header.className : null,
      cart: box(cart),
      badge: box(badge),
      menu: box(menu),
      logo: box(logo),
      logoImg: box(logoImg),
      badgeOffsetY: cart && badge ? Math.round(badge.getBoundingClientRect().y - cart.getBoundingClientRect().y) : null,
      badgeOffsetX: cart && badge ? Math.round(badge.getBoundingClientRect().x - cart.getBoundingClientRect().x) : null,
    };
  });
}

(async () => {
  const browser = await chromium.launch({ headless: true });

  async function load(url) {
    const page = await browser.newPage({ viewport, userAgent });
    const u = new URL(url);
    u.searchParams.set('mobile_header_regression', String(Date.now()));
    await page.goto(u.toString(), { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(2500);
    return page;
  }

  const categoryPage = await load(categoryUrl);
  const category = await measure(categoryPage);
  await categoryPage.close();

  const searchPage = await load(searchUrl);
  const searchBefore = await measure(searchPage);
  await searchPage.evaluate(() => window.scrollTo(0, 500));
  await searchPage.waitForTimeout(900);
  const searchAfterScroll = await measure(searchPage);
  await searchPage.close();
  await browser.close();

  const failures = [];

  if (searchBefore.badgeOffsetY === null || category.badgeOffsetY === null) {
    failures.push('badge measurement missing');
  } else if (Math.abs(searchBefore.badgeOffsetY - category.badgeOffsetY) > 2) {
    failures.push(`badge Y offset mismatch: search ${searchBefore.badgeOffsetY} vs category ${category.badgeOffsetY}`);
  }

  if (!searchBefore.menu || !searchAfterScroll.menu) {
    failures.push('menu measurement missing');
  } else if (searchAfterScroll.menu.y !== searchBefore.menu.y) {
    failures.push(`menu moved on scroll: before ${searchBefore.menu.y} after ${searchAfterScroll.menu.y}`);
  }

  if (!searchAfterScroll.logoImg) {
    failures.push('logo image measurement missing after scroll');
  } else if (searchAfterScroll.logoImg.transform !== 'none') {
    failures.push(`logo transformed on scroll: ${searchAfterScroll.logoImg.transform}`);
  }

  if (failures.length) {
    console.log('FAIL mobile search header regression');
    console.log(JSON.stringify({ category, searchBefore, searchAfterScroll, failures }, null, 2));
    process.exit(1);
  }

  console.log('PASS mobile search header regression');
})();
