/* Verifies the design-review "reduced image height" fix actually computes on
 * the live search results page, at the three mobile widths the auditor probed.
 * Asserts: product-image img computed max-height == 45vh (in px) and rendered
 * height <= 45vh + 1px tolerance. Exits 0 on pass, 1 on fail. */
const { chromium } = require('playwright');

const url = process.argv[2] || 'https://shop.3wdistributing.com/?s=wheels';
const widths = [360, 390, 430];

(async () => {
  const browser = await chromium.launch({ headless: true });
  let failed = 0;

  for (const width of widths) {
    const page = await browser.newPage({
      viewport: { width, height: 844 },
      deviceScaleFactor: 1,
    });
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(4500);

    const probe = await page.evaluate((w) => {
      const vh = window.innerHeight; // 45vh in px
      const target = vh * 0.45;
      const imgs = Array.from(
        document.querySelectorAll('ul.products li.product .product-image img:not(.hover-image)')
      ).filter((i) => i.getBoundingClientRect().height > 0);
      if (!imgs.length) return { ok: false, reason: 'no product images found', vh, target };
      const rows = imgs.slice(0, 3).map((img) => {
        const cs = window.getComputedStyle(img);
        const r = img.getBoundingClientRect();
        const frame = img.closest('.product-image');
        const frameCs = frame ? window.getComputedStyle(frame) : null;
        return {
          maxH: cs.maxHeight,
          aspect: cs.aspectRatio,
          objFit: cs.objectFit,
          h: Math.round(r.height),
          w: Math.round(r.width),
          frameMaxH: frameCs ? frameCs.maxHeight : null,
          frameH: frame ? Math.round(frame.getBoundingClientRect().height) : null,
        };
      });
      // Pass criteria: computed max-height resolves to ~45vh (the browser
      // returns a resolved px value, e.g. 379.8px for 45vh at vh=844), the
      // image keeps aspect-ratio 1/1 with object-fit contain, and the rendered
      // height does not exceed the 45vh cap by more than 1px tolerance.
      const pass = rows.every((r) => {
        const computed = parseFloat(r.maxH);
        return (
          Math.abs(computed - target) < 2 &&
          r.h <= target + 1 &&
          r.aspect === '1 / 1' &&
          r.objFit === 'contain' &&
          r.frameMaxH && Math.abs(parseFloat(r.frameMaxH) - target) < 2
        );
      });
      return { ok: pass, vh, target: Math.round(target), rows };
    }, width);

    console.log(`\n=== ${width}px (vh=${probe.vh}, target 45vh=${probe.target}px) ===`);
    if (probe.reason) {
      console.log('FAIL:', probe.reason);
      failed++;
    } else {
      probe.rows.forEach((r, i) =>
        console.log(
          `  img[${i}] maxH=${r.maxH} aspect=${r.aspect} objFit=${r.objFit} h=${r.h}px w=${r.w}px | frame maxH=${r.frameMaxH} h=${r.frameH}px`
        )
      );
      console.log(probe.ok ? 'PASS' : 'FAIL — image-height cap not in effect');
      if (!probe.ok) failed++;
    }
    await page.close();
  }

  await browser.close();
  process.exit(failed ? 1 : 0);
})();
