const { chromium } = require('playwright');

const url = process.argv[2] || 'https://shop.3wdistributing.com/';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 375, height: 812 },
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
  });

  await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });

  const data = await page.evaluate(() => {
    const rect = (selector) => {
      const el = document.querySelector(selector);
      if (!el) return null;
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return {
        x: Math.round(r.x),
        y: Math.round(r.y),
        w: Math.round(r.width),
        h: Math.round(r.height),
        display: s.display,
        visibility: s.visibility,
        opacity: s.opacity,
        backgroundImage: s.backgroundImage,
      };
    };

    return {
      toggle: rect('.mobile-toggle'),
      proxy: rect('.threew-mobile-menu-proxy'),
      topAtMenu: (() => {
        const el = document.elementFromPoint(window.innerWidth - 36, 36);
        return el ? { tag: el.tagName, cls: el.className, label: el.getAttribute('aria-label'), text: el.textContent.trim() } : null;
      })(),
      search: rect('.threew-mobile-header-search'),
      input: rect('.threew-mobile-header-search input[name="s"]'),
      button: rect('.threew-mobile-header-search button[type="submit"]'),
    };
  });

  const visible = (box) => box && box.display !== 'none' && box.visibility !== 'hidden' && Number(box.opacity) > 0;
  const menu = data.proxy || data.toggle;
  const hasBars = visible(data.proxy) || (data.toggle && data.toggle.backgroundImage.includes('linear-gradient'));
  const noOverlap = data.search && menu && data.search.x + data.search.w <= menu.x - 8;
  const searchUsable = visible(data.search) && visible(data.input) && visible(data.button) && data.input.w >= 150 && data.button.w >= 44 && data.button.h >= 44;
  const menuTopmost = data.topAtMenu && data.topAtMenu.cls === 'threew-mobile-menu-proxy';

  const passed = visible(menu) && menu.w >= 44 && menu.h >= 44 && hasBars && noOverlap && searchUsable && menuTopmost;

  if (!passed) {
    console.log('FAIL mobile header search/menu');
    console.log(JSON.stringify({ ...data, hasBars, noOverlap, searchUsable, menuTopmost }, null, 2));
    await browser.close();
    process.exit(1);
  }

  await browser.close();
  console.log('PASS mobile header has visible hamburger bars and usable search');
})();
