const { chromium } = require('playwright');

const homeUrl = process.argv[2] || 'https://shop.3wdistributing.com/';
const productUrl = process.argv[3] || 'https://shop.3wdistributing.com/product-category/brabus/';
const mobileUserAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

async function snapshot(page, url) {
  const checked = new URL(url);
  checked.searchParams.set('threew_home_header_check', String(Date.now()));
  await page.goto(checked.toString(), { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(2500);

  const cartCount = await page.locator('#mini-cart').count();
  if (cartCount) {
    await page.locator('#mini-cart').nth(cartCount - 1).click({ force: true });
    await page.waitForTimeout(400);
  }

  return page.evaluate(() => {
    const visible = (el) => {
      if (!el) return false;
      const rect = el.getBoundingClientRect();
      const style = getComputedStyle(el);
      return rect.width > 0 && rect.height > 0 && style.display !== 'none' && style.visibility !== 'hidden' && Number(style.opacity) > 0;
    };
    const box = (selector) => {
      const el = Array.from(document.querySelectorAll(selector)).find(visible);
      if (!el) return null;
      const rect = el.getBoundingClientRect();
      const style = getComputedStyle(el);
      return { x: Math.round(rect.x), y: Math.round(rect.y), w: Math.round(rect.width), h: Math.round(rect.height), right: Math.round(rect.right), bg: style.backgroundColor };
    };

    const visibleCart = Array.from(document.querySelectorAll('#mini-cart')).filter(visible).pop();
    const badgeHost = visibleCart && visibleCart.querySelector('.cart-items, .cart-count, .cart-badge');
    if (badgeHost) {
      badgeHost.style.display = 'inline-flex';
      badgeHost.textContent = '2';
      badgeHost.dataset.count = '2';
    }

    const popup = Array.from(document.querySelectorAll('.threew-mobile-cart-popup')).find(visible) || Array.from(document.querySelectorAll('#mini-cart .cart-popup, #mini-cart .widget_shopping_cart')).find(visible);
    let popupTopmost = false;
    let popupBox = null;
    if (popup) {
      const rect = popup.getBoundingClientRect();
      popupBox = { x: Math.round(rect.x), y: Math.round(rect.y), w: Math.round(rect.width), h: Math.round(rect.height) };
      popupTopmost = [10, rect.width / 2, rect.width - 10].every((dx) => {
        const top = document.elementFromPoint(rect.x + dx, rect.y + 10);
        return top && (top === popup || popup.contains(top));
      });
    }

    const badgeBox = badgeHost ? (() => {
      const rect = badgeHost.getBoundingClientRect();
      const style = getComputedStyle(badgeHost);
      return { x: Math.round(rect.x), y: Math.round(rect.y), w: Math.round(rect.width), h: Math.round(rect.height), right: Math.round(rect.right), bg: style.backgroundColor };
    })() : null;

    return {
      logo: box('.logo img, .header-logo img'),
      search: box('.threew-mobile-header-search'),
      cart: box('#mini-cart, .mini-cart, .cart-toggle'),
      badge: badgeBox,
      popup: popupBox,
      popupTopmost,
    };
  });
}

(async () => {
  const browser = await chromium.launch({ headless: true });

  try {
    const home = await browser.newPage({ viewport: { width: 375, height: 812 }, userAgent: mobileUserAgent });
    const product = await browser.newPage({ viewport: { width: 375, height: 812 }, userAgent: mobileUserAgent });
    const homeData = await snapshot(home, homeUrl);
    const productData = await snapshot(product, productUrl);
    const redBadge = homeData.badge && homeData.badge.bg === productData.badge.bg;
    const matchesProductHeader = homeData.search && productData.search && Math.abs(homeData.search.x - productData.search.x) <= 2 && Math.abs(homeData.search.right - productData.search.right) <= 4;
    const popupUsable = homeData.popup && homeData.popup.w >= 280 && homeData.popupTopmost;

    if (!matchesProductHeader || !popupUsable || !redBadge) {
      console.log('FAIL home mobile header bugs');
      console.log(JSON.stringify({ home: homeData, product: productData, checks: { matchesProductHeader, popupUsable, redBadge } }, null, 2));
      process.exitCode = 1;
      return;
    }

    console.log('PASS home mobile header bugs');
  } catch (error) {
    console.log('FAIL home mobile header bugs: ' + (error && error.message ? error.message : error));
    process.exitCode = 1;
  } finally {
    await browser.close();
  }
})();
