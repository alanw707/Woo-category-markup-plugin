#!/usr/bin/env node
const base = new URL(process.argv[2] || 'https://shop.3wdistributing.com/');
const MAX_SITEMAPS = 20;
const MAX_BYTES = 2_000_000;

const failures = [];
const warnings = [];
const pages = [];
const note = (arr, msg) => arr.push(msg);
const okUrl = (u) => { try { return new URL(u, base).href; } catch { return ''; } };

async function get(url, accept = 'text/html,*/*', userAgent = '3W SEO diagnostic; Googlebot-compatible audit') {
  const res = await fetch(url, {
    redirect: 'follow',
    signal: AbortSignal.timeout(20000),
    headers: {
      'user-agent': userAgent,
      accept,
    },
  });
  const buf = await res.arrayBuffer();
  const text = new TextDecoder('utf-8', { fatal: false }).decode(buf.slice(0, MAX_BYTES));
  return { url: res.url, status: res.status, headers: Object.fromEntries(res.headers), text };
}

function locs(xml) {
  return [...xml.matchAll(/<loc>\s*([^<\s]+)\s*<\/loc>/gi)].map(m => m[1].replace(/&amp;/g, '&'));
}

function hrefs(html) {
  return [...html.matchAll(/<a\b[^>]*\bhref=["']([^"'#]+)["']/gi)]
    .map(m => okUrl(m[1]))
    .filter(Boolean);
}

function textOf(html, re) { return (html.match(re) || [,''])[1].replace(/\s+/g, ' ').trim(); }
function meta(html, name) {
  return textOf(html, new RegExp(`<meta[^>]+(?:name|property)=["']${name}["'][^>]+content=["']([^"']*)["']`, 'i')) ||
    textOf(html, new RegExp(`<meta[^>]+content=["']([^"']*)["'][^>]+(?:name|property)=["']${name}["']`, 'i'));
}
function canonical(html) { return textOf(html, /<link[^>]+rel=["']canonical["'][^>]+href=["']([^"']+)["']/i); }
function robotsMeta(html, headers) { return `${headers['x-robots-tag'] || ''} ${meta(html, 'robots')}`.toLowerCase(); }
function visibleTextLen(html) {
  return html.replace(/<script[\s\S]*?<\/script>/gi, ' ')
    .replace(/<style[\s\S]*?<\/style>/gi, ' ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim().length;
}
function schemaText(html) {
  return [...html.matchAll(/<script[^>]+type=["']application\/ld\+json["'][^>]*>([\s\S]*?)<\/script>/gi)]
    .map(m => m[1]).join('\n');
}
function hasSchema(html, word) { return new RegExp(`"@type"\\s*:\\s*(?:"[^"]*${word}[^"]*"|\\[[^\\]]*${word}[^\\]]*\\])`, 'i').test(schemaText(html)); }
function hasWord(html, word) { return new RegExp(word, 'i').test(schemaText(html)); }

function robotDisallowsAll(robots, agent) {
  const lines = robots.split(/\r?\n/).map(l => l.replace(/#.*/, '').trim()).filter(Boolean);
  let agents = [], matched = false, blocked = false;
  for (const line of lines) {
    const m = line.match(/^user-agent:\s*(.+)$/i);
    if (m) { agents.push(m[1].toLowerCase()); continue; }
    if (/^(allow|disallow):/i.test(line)) {
      if (agents.length) {
        matched = agents.some(a => a === '*' || agent.toLowerCase().includes(a) || a.includes(agent.toLowerCase()));
        agents = [];
      }
      const d = line.match(/^disallow:\s*(.*)$/i);
      if (matched && d && d[1].trim() === '/') blocked = true;
    }
  }
  return blocked;
}

async function discoverSitemaps() {
  const robots = await get(new URL('/robots.txt', base), 'text/plain,*/*').catch(e => ({ status: 0, text: String(e), headers: {}, url: new URL('/robots.txt', base).href }));
  if (robots.status !== 200) note(failures, `robots.txt not reachable (${robots.status})`);
  const declared = [...robots.text.matchAll(/^sitemap:\s*(.+)$/gim)].map(m => m[1].trim());
  for (const bot of ['Googlebot', 'OAI-SearchBot', 'ChatGPT-User', 'PerplexityBot']) {
    if (robotDisallowsAll(robots.text, bot)) note(failures, `robots.txt blocks search crawler ${bot}`);
  }
  for (const bot of ['GPTBot', 'ClaudeBot', 'Google-Extended', 'CCBot']) {
    if (robotDisallowsAll(robots.text, bot)) note(warnings, `robots.txt blocks AI crawler ${bot}`);
  }
  const candidates = [...new Set([...declared, '/sitemap_index.xml', '/sitemap.xml', '/wp-sitemap.xml'].map(okUrl).filter(Boolean))];
  const sitemaps = [];
  const urls = [];
  for (const url of candidates) {
    const r = await get(url, 'application/xml,text/xml,*/*').catch(() => null);
    if (!r || r.status !== 200 || !/<(?:urlset|sitemapindex)\b/i.test(r.text)) continue;
    sitemaps.push(url);
    const found = locs(r.text);
    urls.push(...found.filter(u => !/\.xml(\.gz)?(?:$|[?#])/i.test(u)));
    for (const child of found.filter(u => /\.xml(\.gz)?(?:$|[?#])/i.test(u)).slice(0, MAX_SITEMAPS)) {
      const cr = await get(child, 'application/xml,text/xml,*/*').catch(() => null);
      if (cr?.status === 200) urls.push(...locs(cr.text).filter(u => !/\.xml(\.gz)?(?:$|[?#])/i.test(u)));
    }
  }
  if (!sitemaps.length) note(failures, 'no reachable XML sitemap found');
  return [...new Set(urls)];
}

async function checkPage(label, url) {
  const r = await get(url).catch(e => ({ status: 0, text: String(e), headers: {}, url }));
  const html = r.text || '';
  pages.push(`${label}:${r.status}:${r.url}`);
  if (r.status !== 200) note(failures, `${label} status ${r.status}`);
  if (/\bnoindex\b/.test(robotsMeta(html, r.headers))) note(failures, `${label} has noindex`);
  const title = textOf(html, /<title[^>]*>([\s\S]*?)<\/title>/i);
  if (title.length < 10) note(warnings, `${label} weak/missing title`);
  if (meta(html, 'description').length < 50) note(warnings, `${label} weak/missing meta description`);
  if (!canonical(html)) note(warnings, `${label} missing canonical`);
  if ((html.match(/<h1\b/gi) || []).length !== 1) note(warnings, `${label} should have exactly one H1`);
  if (visibleTextLen(html) < 300) note(warnings, `${label} thin crawlable text`);
  return html;
}

async function checkBotAccess(url) {
  const searchBots = ['Googlebot', 'OAI-SearchBot', 'ChatGPT-User', 'PerplexityBot'];
  const aiBots = ['GPTBot', 'ClaudeBot', 'Google-Extended'];
  for (const bot of searchBots) {
    const r = await get(url, 'text/html,*/*', bot).catch(e => ({ status: 0, text: String(e), headers: {} }));
    if (r.status !== 200 || visibleTextLen(r.text || '') < 300 || /\bnoindex\b/.test(robotsMeta(r.text || '', r.headers || {}))) {
      note(failures, `${bot} cannot crawl home (${r.status})`);
    }
  }
  for (const bot of aiBots) {
    const r = await get(url, 'text/html,*/*', bot).catch(e => ({ status: 0, text: String(e), headers: {} }));
    if (r.status !== 200) note(warnings, `${bot} gets home status ${r.status}`);
  }
}

const sitemapUrls = await discoverSitemaps();
await checkBotAccess(base.href);
const homeHtml = await checkPage('home', base.href);
const discovered = hrefs(homeHtml);
const product = sitemapUrls.find(u => /\/product\//i.test(u) && !/product-category/i.test(u)) || discovered.find(u => /\/product\//i.test(u) && !/product-category/i.test(u));
const category = sitemapUrls.find(u => /\/product-category\//i.test(u)) || discovered.find(u => /\/product-category\//i.test(u));

if (!sitemapUrls.some(u => /\/product\//i.test(u))) note(failures, 'sitemap has no product URLs');
if (!sitemapUrls.some(u => /\/product-category\//i.test(u))) note(warnings, 'sitemap has no product-category URLs');

if (!hasSchema(homeHtml, 'Organization')) note(warnings, 'home missing Organization schema');
if (!hasSchema(homeHtml, 'WebSite')) note(warnings, 'home missing WebSite schema');
if (!hasWord(homeHtml, 'SearchAction')) note(warnings, 'home missing WebSite SearchAction schema');

if (category) {
  const html = await checkPage('category', category);
  if (!hasSchema(html, 'BreadcrumbList')) note(warnings, 'category missing BreadcrumbList schema');
} else note(failures, 'no category URL discoverable');

if (product) {
  const html = await checkPage('product', product);
  if (!hasSchema(html, 'Product')) note(failures, 'product missing Product schema');
  if (!hasWord(html, 'Offer')) note(warnings, 'product missing Offer schema');
  if (!hasWord(html, 'priceCurrency')) note(warnings, 'product schema missing priceCurrency');
  if (!hasSchema(html, 'BreadcrumbList')) note(warnings, 'product missing BreadcrumbList schema');
} else note(failures, 'no product URL discoverable');

const llms = await get(new URL('/llms.txt', base), 'text/plain,*/*').catch(() => ({ status: 0 }));
if (llms.status !== 200) note(warnings, 'no /llms.txt for AI crawlers');

console.log(`SEO loop ${base.href}`);
console.log(`pages checked: ${pages.join(' | ')}`);
console.log(`sitemap urls sampled: ${sitemapUrls.length}`);
console.log(`failures: ${failures.length}`);
for (const f of failures.slice(0, 12)) console.log(`FAIL ${f}`);
console.log(`warnings: ${warnings.length}`);
for (const w of warnings.slice(0, 12)) console.log(`WARN ${w}`);
process.exitCode = failures.length ? 1 : 0;
