const user = process.env.WP_USER;
const pass = process.env.WP_APP_PASS;
if (!user || !pass) throw new Error('Set WP_USER and WP_APP_PASS');
const auth = 'Basic ' + Buffer.from(`${user}:${pass}`).toString('base64');
const base = 'https://shop.3wdistributing.com/wp-json';
async function get(path) {
  const r = await fetch(base + path, { headers: { Authorization: auth } });
  const t = await r.text();
  let j; try { j = JSON.parse(t); } catch { j = t; }
  return { status: r.status, data: j };
}
(async () => {
  for (const path of [
    '/wp/v2/search?search=home&per_page=10',
    '/wp/v2/pages?slug=home',
    '/wp/v2/pages?search=home&per_page=10',
    '/wp/v2/porto_builder?per_page=20',
    '/wp/v2/porto_block?per_page=20',
    '/wp/v2/blocks?per_page=20',
    '/wp/v2/types'
  ]) {
    const {status, data} = await get(path);
    console.log('\n### ' + path + ' status ' + status);
    if (Array.isArray(data)) {
      for (const x of data.slice(0, 10)) console.log(JSON.stringify({id:x.id, type:x.type, slug:x.slug, title:x.title?.rendered || x.title, url:x.url, subtype:x.subtype, status:x.status}, null, 0));
    } else if (typeof data === 'object') {
      console.log(Object.keys(data).slice(0,80).join(', '));
    } else console.log(String(data).slice(0,500));
  }
})();
