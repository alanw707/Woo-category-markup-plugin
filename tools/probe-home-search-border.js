const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 390, height: 844 },
    isMobile: true,
    hasTouch: true,
  });

  await page.goto('https://shop.3wdistributing.com/', {
    waitUntil: 'domcontentloaded',
    timeout: 60000,
  });
  await page.waitForTimeout(5000);

  const popupClose = page.locator('#threew-newsletter-popup .newsletter-close, #threew-newsletter-popup .mfp-close, #threew-newsletter-popup [aria-label="Close"]').first();
  if (await popupClose.count()) {
    await popupClose.click({ force: true }).catch(() => {});
    await page.waitForTimeout(300);
  }

  const input = page.locator('body.home .header-side .searchform input[name="s"], body.home header .searchform input[name="s"]').first();
  await input.evaluate((el) => el.focus());
  await page.waitForTimeout(300);

  const data = await input.evaluate((el) => {
    const s = getComputedStyle(el);
    const r = el.getBoundingClientRect();
    const wrap = el.closest('.searchform-fields');
    const ws = wrap ? getComputedStyle(wrap) : null;
    const wr = wrap ? wrap.getBoundingClientRect() : null;
    return {
      input: {
        tag: el.tagName,
        type: el.getAttribute('type'),
        cls: el.className,
        value: el.value,
        placeholder: el.getAttribute('placeholder'),
        width: Math.round(r.width),
        height: Math.round(r.height),
        borderTop: s.borderTop,
        borderColor: s.borderColor,
        borderRadius: s.borderRadius,
        boxShadow: s.boxShadow,
        outline: s.outline,
        outlineColor: s.outlineColor,
        outlineStyle: s.outlineStyle,
        outlineWidth: s.outlineWidth,
        appearance: s.appearance,
        webkitAppearance: s.webkitAppearance,
        backgroundColor: s.backgroundColor,
        backgroundImage: s.backgroundImage,
        color: s.color,
      },
      wrapper: wrap ? {
        cls: wrap.className,
        width: Math.round(wr.width),
        height: Math.round(wr.height),
        border: ws.border,
        borderColor: ws.borderColor,
        borderRadius: ws.borderRadius,
        boxShadow: ws.boxShadow,
        outline: ws.outline,
        backgroundColor: ws.backgroundColor,
      } : null,
    };
  });

  await page.screenshot({ path: 'docs/screenshots/home-search-border-probe/mobile-home-search.png' });
  await browser.close();
  console.log(JSON.stringify(data, null, 2));
})();
