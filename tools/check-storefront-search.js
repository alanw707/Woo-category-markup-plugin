const { chromium } = require('playwright');

const url = process.argv[2] || 'https://shop.3wdistributing.com/';

(async () => {
  const browser = await chromium.launch({ headless: true });

  for (const viewport of [
    { name: 'mobile', width: 375, height: 900 },
    { name: 'desktop', width: 1280, height: 900 },
  ]) {
    const page = await browser.newPage({ viewport });
    const errors = [];
    const liveAjax = [];

    page.on('pageerror', (error) => errors.push(error.message));
    page.on('console', (message) => {
      if (message.type() === 'error') errors.push(message.text());
    });
    page.on('request', (request) => {
      if (request.url().includes('porto_ajax_search_posts')) liveAjax.push(request.url());
    });

    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(2500);

    const searchInput = viewport.name === 'mobile'
      ? page.locator('.header-center .searchform-popup input[name="s"]:visible, .threew-mobile-header-search input[name="s"]:visible').first()
      : page.locator('input[name="s"]:visible').first();

    await searchInput.fill('brabus');
    await page.waitForTimeout(800);
    await searchInput.press('Enter');
    await page.waitForLoadState('domcontentloaded', { timeout: 10000 }).catch(() => {});

    const productCards = await page.locator('ul.products li.product, .products .product-col').count();
    const blogPosts = await page.locator('article.post').count();
    const passed = page.url().includes('s=brabus') && !page.url().includes('post_type=product') && productCards > 0 && blogPosts === 0 && liveAjax.length === 0 && !errors.some((error) => /Unexpected token|JSON|porto_ajax_search_posts|live-search/i.test(error));

    if (!passed) {
      console.log(`FAIL ${viewport.name} search`);
      console.log(JSON.stringify({ url: page.url(), productCards, blogPosts, liveAjax: liveAjax.length, errors: errors.slice(-5) }, null, 2));
      await browser.close();
      process.exit(1);
    }

    await page.close();
  }

  await browser.close();
  console.log('PASS storefront search opens product grid without broken live-search ajax');
})();
