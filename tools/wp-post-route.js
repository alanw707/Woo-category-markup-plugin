const user = process.env.WP_USER;
const pass = process.env.WP_APP_PASS;
const route = process.argv[2];
if (!user || !pass || !route) process.exit(1);
(async () => {
  const res = await fetch(`https://shop.3wdistributing.com/wp-json/${route}`, {
    method: 'POST',
    headers: { Authorization: 'Basic ' + Buffer.from(`${user}:${pass}`).toString('base64') },
  });
  const text = await res.text();
  console.log(`status ${res.status}`);
  try {
    const data = JSON.parse(text);
    console.log(JSON.stringify(data));
  } catch {
    console.log(text.slice(0, 200));
  }
  if (!res.ok) process.exit(1);
})();
