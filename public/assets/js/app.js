/* NSBM NewsDesk — progressive enhancements. The site works without JavaScript;
   this file adds menus, live validation, counters, previews and confirmations. */
(function () {
  'use strict';
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  /* Cover images: hide a photo that fails to load so the category panel shows. */
  $$('.cover img, .upload-preview img').forEach(img => {
    const drop = () => img.remove();
    if (img.complete && img.naturalWidth === 0) drop();
    else img.addEventListener('error', drop);
  });

  /* Mobile section menu and admin sidebar. */
  const toggle = (btnSel, targetSel) => {
    const btn = document.querySelector(btnSel), target = document.querySelector(targetSel);
    if (!btn || !target) return;
    btn.addEventListener('click', () => {
      const open = target.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && target.classList.contains('is-open')) { target.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false'); btn.focus(); }
    });
  };
  toggle('.nav-toggle', '#section-nav');
  toggle('.side-toggle', '#sidebar');
  document.addEventListener('click', e => {
    const sidebar = document.getElementById('sidebar');
    if (sidebar && sidebar.classList.contains('is-open') && !sidebar.contains(e.target) && !e.target.closest('.side-toggle')) sidebar.classList.remove('is-open');
    $$('details.account-menu[open]').forEach(d => { if (!d.contains(e.target)) d.removeAttribute('open'); });
  });

  /* Dismissible messages. */
  $$('.toast-close').forEach(b => b.addEventListener('click', () => b.closest('.toast').remove()));

  /* Confirm destructive actions. */
  $$('form[data-confirm]').forEach(form => form.addEventListener('submit', e => {
    if (!window.confirm(form.dataset.confirm)) e.preventDefault();
  }));
  $$('[data-confirm-click]').forEach(btn => btn.addEventListener('click', e => {
    if (!window.confirm(btn.dataset.confirmClick)) e.preventDefault();
  }));

  /* Character counters for fields with maxlength. */
  $$('[data-count]').forEach(input => {
    const max = input.maxLength;
    if (!max || max < 0) return;
    const out = document.createElement('small');
    out.className = 'char-count';
    out.setAttribute('aria-live', 'polite');
    input.insertAdjacentElement('afterend', out);
    const update = () => {
      const n = input.value.length;
      out.textContent = `${n.toLocaleString()} / ${max.toLocaleString()}`;
      out.classList.toggle('is-near', n > max * 0.9);
    };
    input.addEventListener('input', update);
    update();
  });

  /* Show / hide password. */
  $$('[data-reveal]').forEach(btn => btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.reveal);
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.textContent = show ? 'Hide' : 'Show';
  }));

  /* Password strength hint. */
  $$('[data-strength]').forEach(input => {
    const bar = input.closest('.field').querySelector('.strength');
    if (!bar) return;
    input.addEventListener('input', () => {
      const v = input.value;
      let s = 0;
      if (v.length >= 8) s++;
      if (/[A-Za-z]/.test(v) && /\d/.test(v)) s++;
      if (v.length >= 12 && /[^A-Za-z0-9]/.test(v)) s++;
      bar.className = 'strength' + (v ? ' s' + Math.max(1, s) : '');
    });
  });

  /* Announcement-only fields. */
  const kindInputs = $$('input[name="kind"]');
  const kindFields = $$('[data-show-when]');
  const syncKind = () => {
    const kind = (kindInputs.find(i => i.checked) || {}).value;
    kindFields.forEach(f => { f.hidden = f.dataset.showWhen !== kind; });
  };
  kindInputs.forEach(i => i.addEventListener('change', syncKind));
  syncKind();

  /* Image preview and size check before upload. */
  $$('input[type="file"][data-max-size]').forEach(input => {
    const preview = input.closest('.upload').querySelector('[data-preview]');
    input.addEventListener('change', () => {
      const file = input.files[0];
      clearError(input);
      if (!file) return;
      if (file.size > Number(input.dataset.maxSize)) { input.value = ''; showError(input, 'This photo is larger than 2 MB. Choose a smaller one.'); return; }
      if (!/^image\/(jpeg|png|webp)$/.test(file.type)) { input.value = ''; showError(input, 'Choose a JPG, PNG or WebP photo.'); return; }
      const url = URL.createObjectURL(file);
      let img = preview.querySelector('img');
      if (!img) { img = document.createElement('img'); preview.prepend(img); }
      img.src = url; img.alt = 'Selected cover photo';
      preview.classList.add('has-image');
    });
  });

  /* Form validation with clear inline messages (the server validates again). */
  const messageFor = (el) => {
    const v = el.validity;
    const label = (el.closest('.field')?.querySelector('label, legend, .label')?.textContent || 'This field').replace(/optional|required when rejecting/i, '').trim();
    if (v.valueMissing) return el.type === 'file' ? 'Choose a file.' : `${label} is required.`;
    if (v.typeMismatch && el.type === 'email') return 'Enter an email address like name@example.com.';
    if (v.tooShort) return `${label} needs at least ${el.minLength} characters (now ${el.value.length}).`;
    if (v.tooLong) return `${label} can be at most ${el.maxLength} characters.`;
    if (v.patternMismatch) return 'Use at least 8 characters, including a letter and a number.';
    if (v.rangeUnderflow) return `Choose a date on or after ${el.min}.`;
    if (v.rangeOverflow) return `Choose a date on or before ${el.max}.`;
    if (v.customError) return el.validationMessage;
    return el.validationMessage;
  };
  function showError(el, msg) {
    const field = el.closest('.field') || el.parentElement;
    clearError(el);
    field.classList.add('has-error');
    const p = document.createElement('p');
    p.className = 'field-error';
    p.id = (el.id || el.name) + '-error';
    p.textContent = msg;
    field.appendChild(p);
    el.setAttribute('aria-invalid', 'true');
    el.setAttribute('aria-describedby', p.id);
  }
  function clearError(el) {
    const field = el.closest('.field') || el.parentElement;
    field.classList.remove('has-error');
    field.querySelectorAll('.field-error').forEach(n => n.remove());
    el.removeAttribute('aria-invalid');
  }
  const checkCustom = (el, form) => {
    el.setCustomValidity('');
    if (el.dataset.match) {
      const other = document.getElementById(el.dataset.match);
      if (other && el.value && el.value !== other.value) el.setCustomValidity('The passwords do not match.');
    }
    if (el.dataset.after) {
      const other = document.getElementById(el.dataset.after);
      if (other && other.value && el.value && el.value < other.value) el.setCustomValidity('The end date must be on or after the start date.');
    }
    if (el.tagName !== 'SELECT' && el.type !== 'file' && el.type !== 'password' && el.required && el.value && !el.value.trim()) el.setCustomValidity('This cannot be only spaces.');
  };
  $$('form[data-validate]').forEach(form => {
    form.noValidate = true;
    const fields = () => $$('input, select, textarea', form).concat($$(`[form="${form.id}"]`)).filter(el => el.willValidate);
    form.addEventListener('submit', e => {
      let first = null;
      // A "reject" button needs feedback text.
      const needs = e.submitter && e.submitter.dataset.needs;
      fields().forEach(el => {
        checkCustom(el, form);
        if (needs && el.name === needs && !el.value.trim()) el.setCustomValidity('Write feedback so the author knows what to change.');
        else if (el.name === 'review_note') el.setCustomValidity('');
        if (!el.checkValidity()) { showError(el, messageFor(el)); first = first || el; } else clearError(el);
      });
      if (first) { e.preventDefault(); first.focus(); }
    });
    form.addEventListener('input', e => {
      const el = e.target;
      if (el.closest('.field')?.classList.contains('has-error')) { checkCustom(el, form); if (el.checkValidity()) clearError(el); }
    });
    form.addEventListener('focusout', e => {
      const el = e.target;
      if (!el.willValidate || !el.value) return;
      checkCustom(el, form);
      if (!el.checkValidity()) showError(el, messageFor(el)); else clearError(el);
    });
  });

  /* Prevent double submits on slow connections. */
  $$('form[method="post"]').forEach(form => form.addEventListener('submit', e => {
    if (e.defaultPrevented) return;
    setTimeout(() => $$('button', form).forEach(b => { if (!b.closest('[data-bulk]')) b.disabled = true; }), 0);
  }));

  /* Copy story link. */
  $$('[data-copy-link]').forEach(btn => btn.addEventListener('click', async () => {
    const label = btn.querySelector('span');
    try { await navigator.clipboard.writeText(location.href); label.textContent = 'Link copied'; }
    catch { label.textContent = 'Copy failed'; }
    setTimeout(() => { label.textContent = 'Copy link'; }, 2000);
  }));

  /* Print report. */
  $$('[data-print]').forEach(btn => btn.addEventListener('click', () => window.print()));

  /* Bulk comment moderation. */
  const bulk = document.querySelector('[data-bulk]');
  if (bulk) {
    const boxes = $$(`input[form="${bulk.id}"][name="ids[]"]`);
    const all = bulk.querySelector('[data-select-all]');
    const count = bulk.querySelector('[data-bulk-count]');
    const sync = () => {
      const n = boxes.filter(b => b.checked).length;
      count.textContent = `${n} selected`;
      $$('button', bulk).forEach(b => { b.disabled = n === 0; });
      all.checked = n > 0 && n === boxes.length;
      all.indeterminate = n > 0 && n < boxes.length;
    };
    all.addEventListener('change', () => { boxes.forEach(b => { b.checked = all.checked; }); sync(); });
    boxes.forEach(b => b.addEventListener('change', sync));
    sync();
  }
})();
