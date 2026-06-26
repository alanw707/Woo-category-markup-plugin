const fs = require('fs');
const user = process.env.WP_USER;
const pass = process.env.WP_APP_PASS;
const id = process.argv[2] || '881';
if (!user || !pass) throw new Error('Set WP_USER and WP_APP_PASS');
const auth = 'Basic ' + Buffer.from(`${user}:${pass}`).toString('base64');
const base = 'https://shop.3wdistributing.com/wp-json/wp/v2/porto_builder/';
(async () => {
  const beforeRes = await fetch(base + id + '?context=edit', { headers: { Authorization: auth } });
  const before = await beforeRes.json();
  if (!beforeRes.ok) throw new Error(JSON.stringify(before));
  const raw = before.content?.raw || '';
  const backup = `backups/porto-builder-${id}-${new Date().toISOString().replace(/[:.]/g, '-')}.json`;
  fs.mkdirSync('backups', { recursive: true });
  fs.writeFileSync(backup, JSON.stringify({ id: before.id, slug: before.slug, title: before.title?.raw, status: before.status, content: raw }, null, 2));
  const updateRes = await fetch(base + id, {
    method: 'POST',
    headers: { Authorization: auth, 'content-type': 'application/json' },
    body: JSON.stringify({ content: '' })
  });
  const updated = await updateRes.json();
  if (!updateRes.ok) throw new Error(JSON.stringify(updated));
  console.log(JSON.stringify({ backup, id: updated.id, slug: updated.slug, status: updated.status, beforeBytes: raw.length, afterBytes: (updated.content?.raw || '').length }, null, 2));
})();
