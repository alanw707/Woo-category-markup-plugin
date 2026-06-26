(function () {
  'use strict';

  var cfg = window.threewNewsletterPopup || {};
  var key = 'threewNewsletterPopupSuppressedAt';
  var dayMs = 24 * 60 * 60 * 1000;
  var suppressMs = (parseInt(cfg.suppressionDays || 30, 10) || 30) * dayMs;

  function suppressed() {
    var last = parseInt(localStorage.getItem(key) || '0', 10);
    return last && Date.now() - last < suppressMs;
  }

  function suppress() {
    localStorage.setItem(key, String(Date.now()));
  }

  function show() {
    var modal = document.getElementById('threew-newsletter-popup');
    if (!modal) return;
    modal.hidden = false;
    suppress();
  }

  function hide() {
    var modal = document.getElementById('threew-newsletter-popup');
    if (modal) modal.hidden = true;
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (suppressed()) return;

    var form = document.getElementById('threew-newsletter-popup-form');
    var close = document.getElementById('threew-newsletter-popup-close');
    var status = document.getElementById('threew-newsletter-popup-status');

    setTimeout(show, (parseInt(cfg.delaySeconds || 7, 10) || 0) * 1000);

    if (close) close.addEventListener('click', function () { suppress(); hide(); });
    if (!form) return;

    form.addEventListener('submit', function (event) {
      event.preventDefault();

      var email = form.querySelector('[name="email"]').value.trim();
      var consent = form.querySelector('[name="consent"]').checked;
      if (!email || !consent) {
        status.textContent = 'Enter your email and accept newsletter signup.';
        return;
      }

      var body = new URLSearchParams();
      body.set('action', 'threew_np_subscribe');
      body.set('nonce', cfg.nonce || '');
      body.set('email', email);
      body.set('consent', consent ? '1' : '0');

      form.querySelector('button[type="submit"]').disabled = true;
      status.textContent = 'Submitting...';

      fetch(cfg.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
      })
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (!data.success) throw new Error(data.data && data.data.message ? data.data.message : 'Signup failed.');
          suppress();
          form.innerHTML = '<p class="threew-np-success"></p>';
          form.querySelector('.threew-np-success').textContent = 'Success. Use code ' + data.data.couponCode + ' for ' + data.data.discountPercent + '% off.';
        })
        .catch(function (error) {
          status.textContent = error.message;
          form.querySelector('button[type="submit"]').disabled = false;
        });
    });
  });
})();
