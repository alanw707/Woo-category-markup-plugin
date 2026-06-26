const user = process.env.WP_USER;
const pass = process.env.WP_APP_PASS;
if (!user || !pass) process.exit(1);
(async () => {
  const res = await fetch('https://shop.3wdistributing.com/wp-json/wp/v2/plugins', {
    headers: { Authorization: 'Basic ' + Buffer.from(`${user}:${pass}`).toString('base64') },
  });
  const data = await res.json();
  console.log(`status ${res.status}`);
  for (const p of data) {
    if ((p.plugin || '').includes('storefront') || (p.name || '').includes('3W')) {
      console.log(`${p.plugin} | ${p.status} | ${p.name}`);
    }
  }
})();
