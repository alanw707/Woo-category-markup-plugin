const user = process.env.WP_USER;
const pass = process.env.WP_APP_PASS;
const plugin = process.argv[2];
if (!user || !pass || !plugin) {
  console.error('missing WP_USER, WP_APP_PASS, or plugin slug');
  process.exit(1);
}

(async () => {
  const auth = 'Basic ' + Buffer.from(`${user}:${pass}`).toString('base64');
  const base = 'https://shop.3wdistributing.com/wp-json/wp/v2/plugins/';
  const url = base + plugin;
  const res = await fetch(url, {
    method: 'POST',
    headers: {
      Authorization: auth,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ status: 'active' }),
  });
  const text = await res.text();
  let data;
  try { data = JSON.parse(text); } catch { data = { raw: text.slice(0, 200) }; }
  console.log(`status ${res.status}`);
  if (data.plugin || data.status) {
    console.log(`${data.plugin || plugin}: ${data.status || 'unknown'}`);
  } else {
    console.log(`${data.code || 'no-code'} ${(data.message || data.raw || '').slice(0, 180)}`);
  }
  if (!res.ok) process.exit(1);
})();
