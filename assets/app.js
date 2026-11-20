(() => {
  const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const calm = matchMedia('(prefers-reduced-motion:reduce)').matches;

  const toast = msg => {
    const t = document.createElement('div');
    t.className = 'toast'; t.textContent = msg; $('#toasts').append(t);
    setTimeout(() => t.classList.add('out'), 2200); setTimeout(() => t.remove(), 2600);
  };

  const fly = f => {
    const src = f.closest('.card,.detail')?.querySelector('.pic'), dst = $('.cart');
    if (!src || !dst || calm) return;
    const a = src.getBoundingClientRect(), b = dst.getBoundingClientRect(), n = document.createElement('div');
    n.className = 'fly'; const im = src.querySelector('img');
    if (im) { const c = im.cloneNode(); c.style.cssText = 'width:72px;height:72px;object-fit:cover;border-radius:8px'; n.append(c); } else n.textContent = src.textContent.trim();
    n.style.cssText = `left:${a.left + a.width / 2}px;top:${a.top + a.height / 2}px`;
    document.body.append(n);
    const dx = b.left + b.width / 2 - a.left - a.width / 2, dy = b.top + b.height / 2 - a.top - a.height / 2;
    n.animate([
      { transform: 'translate(-50%,-50%) scale(1)', opacity: 1 },
      { transform: `translate(${dx}px,${dy}px) translate(-50%,-50%) scale(.2)`, opacity: .3 }
    ], { duration: 700, easing: 'cubic-bezier(.5,-.3,.8,.5)' }).onfinish = () => n.remove();
  };

  // Thêm vào giỏ không tải lại trang
  document.addEventListener('submit', async e => {
    const f = e.target; if (!f.matches('[data-ajax]')) return;
    e.preventDefault();
    const btn = f.querySelector('button'); btn.disabled = true;
    try {
      const r = await fetch('cart.php', { method: 'POST', body: new FormData(f), headers: { 'X-Requested-With': 'fetch' } });
      const c = $('.cart span'); c.textContent = (await r.json()).count;
      c.classList.remove('bump'); void c.offsetWidth; c.classList.add('bump');
      toast('Đã thêm vào giỏ hàng ✓'); fly(f);
    } catch (_) { f.submit(); }
    btn.disabled = false;
  });

  // Hiện dần sản phẩm khi cuộn tới
  const io = new IntersectionObserver(es => es.forEach(x => {
    if (!x.isIntersecting) return;
    x.target.classList.add('in'); io.unobserve(x.target);
    setTimeout(() => x.target.classList.remove('rv', 'in'), 900);
  }), { threshold: .1 });
  $$('.rv').forEach(el => io.observe(el));

  // Thanh đầu trang + nút lên đầu trang (chỉ cập nhật mỗi khung hình)
  const top = $('.top'), up = $('#up'); let tick = false;
  addEventListener('scroll', () => {
    if (tick) return; tick = true;
    requestAnimationFrame(() => {
      top.classList.toggle('scrolled', scrollY > 20); up.classList.toggle('show', scrollY > 600); tick = false;
    });
  }, { passive: true });
  up.onclick = () => scrollTo({ top: 0 });

  // Đếm ngược Flash Sale đến hết ngày
  const cds = $$('.cd');
  if (cds.length) {
    const run = () => {
      const n = new Date(), e = new Date(n); e.setHours(23, 59, 59, 999);
      const s = Math.max(0, (e - n) / 1000 | 0);
      const t = [s / 3600 | 0, s / 60 % 60 | 0, s % 60].map(v => String(v).padStart(2, '0')).join(':');
      cds.forEach(c => c.textContent = t);
    };
    run(); setInterval(run, 1000);
  }

  // Nút cuộn ngang cho từng hàng sản phẩm
  $$('.nx').forEach(b => b.onclick = () => {
    const r = b.parentElement.firstElementChild, end = r.scrollLeft + r.clientWidth >= r.scrollWidth - 4;
    r.scrollTo({ left: end ? 0 : r.scrollLeft + r.clientWidth * .8, behavior: calm ? 'auto' : 'smooth' });
  });

  // Tải trước trang khi rê chuột để chuyển trang gần như tức thì
  const seen = new Set();
  const pre = e => {
    const a = e.target.closest && e.target.closest('a[href]');
    if (!a || a.origin !== location.origin || seen.has(a.href) || /logout/.test(a.href)) return;
    seen.add(a.href);
    const l = document.createElement('link'); l.rel = 'prefetch'; l.href = a.href; document.head.append(l);
  };
  addEventListener('mouseover', pre, { passive: true }); addEventListener('touchstart', pre, { passive: true });
})();
