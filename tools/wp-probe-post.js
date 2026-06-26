const user=process.env.WP_USER, pass=process.env.WP_APP_PASS, id=process.argv[2]||'881', type=process.argv[3]||'porto_builder';
if(!user||!pass) throw new Error('creds');
const auth='Basic '+Buffer.from(`${user}:${pass}`).toString('base64');
(async()=>{ const r=await fetch(`https://shop.3wdistributing.com/wp-json/wp/v2/${type}/${id}?context=edit`,{headers:{Authorization:auth}}); const j=await r.json(); console.log('status',r.status); console.log(JSON.stringify({id:j.id,slug:j.slug,status:j.status,type:j.type,title:j.title,template:j.template,meta:j.meta,content:(j.content?.raw||j.content?.rendered||'').slice(0,4000)},null,2)); })();
