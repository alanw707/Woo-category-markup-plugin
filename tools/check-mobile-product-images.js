const { chromium } = require('playwright');

const url = process.argv[2] || 'https://shop.3wdistributing.com/';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 375, height: 900 }, deviceScaleFactor: 1 });

  await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(4000);

  const failures = await page.evaluate(() => {
    return Array.from(document.querySelectorAll('.product-image img:not(.hover-image)'))
      .map((img) => {
        const frame = img.closest('.product-image')?.querySelector('a') || img.closest('.product-image');
        const ir = img.getBoundingClientRect();
        const fr = frame.getBoundingClientRect();
        return {
          alt: img.alt || img.currentSrc.split('/').pop(),
          img: { w: Math.round(ir.width), h: Math.round(ir.height) },
          frame: { w: Math.round(fr.width), h: Math.round(fr.height) },
        };
      })
      .filter((item) => item.img.w > 0 && item.frame.w > 0)
      .filter((item) => item.img.w > item.frame.w + 8 || item.img.h > item.frame.h + 8 || item.img.h < item.frame.h * 0.7)
      .slice(0, 5);
  });

  await browser.close();

  if (failures.length) {
    console.log(`FAIL non-responsive/clipped product images: ${failures.length}`);
    console.log(JSON.stringify(failures, null, 2));
    process.exit(1);
  }

  console.log('PASS product images responsive inside product cards');
})();
