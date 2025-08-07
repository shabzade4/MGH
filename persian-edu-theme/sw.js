const VERSION = 'v1';
const ASSETS = [
  '/',
  '/wp-content/themes/persian-edu-theme/assets/css/main.min.css',
  '/wp-content/themes/persian-edu-theme/assets/js/main.min.js'
];
self.addEventListener('install', (e)=>{
  e.waitUntil(caches.open(VERSION).then(c=>c.addAll(ASSETS)));
});
self.addEventListener('activate', (e)=>{
  e.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==VERSION).map(k=>caches.delete(k)))));
});
self.addEventListener('fetch', (e)=>{
  const req = e.request;
  if (req.method !== 'GET') return;
  e.respondWith(
    caches.match(req).then(res => res || fetch(req).then(r=>{
      const copy = r.clone();
      if (copy.ok && (copy.headers.get('content-type')||'').includes('text/css') || (copy.url.includes('/assets/'))){
        caches.open(VERSION).then(c=>c.put(req, copy));
      }
      return r;
    }).catch(()=> caches.match('/')))
  );
});