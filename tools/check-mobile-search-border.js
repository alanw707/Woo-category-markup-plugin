const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 390, height: 844 },
    isMobile: true,
    hasTouch: true,
  });

  await page.goto('https://shop.3wdistributing.com/?s=wheels', {
    waitUntil: 'domcontentloaded',
    timeout: 60000,
  });
  await page.waitForTimeout(5000);

  const input = page.locator('.header-center .searchform-popup.advanced-search-layout input[name="s"]:visible').first();
  await input.evaluate((el) => el.focus());
  await page.waitForTimeout(300);

  const data = await input.evaluate((el) => {
    const s = getComputedStyle(el);
    const r = el.getBoundingClientRect();
    return {
      width: Math.round(r.width),
      height: Math.round(r.height),
      borderTop: s.borderTop,
      borderColor: s.borderColor,
      borderRadius: s.borderRadius,
      boxShadow: s.boxShadow,
      outline: s.outline,
      appearance: s.appearance,
      webkitAppearance: s.webkitAppearance,
    };
  });

  await page.screenshot({ path: 'docs/screenshots/mobile-search-border-probe/search-page-mobile-header.png' });
  await browser.close();
  console.log(JSON.stringify(data, null, 2));

  const hasBorder = !data.borderTop.startsWith('0px');
  if (hasBorder) {
    console.log('FAIL gray border still present');
    process.exit(1);
  }
  console.log('PASS no border on visible mobile header search input');
})();
