// Satu dropdown GGmart untuk seluruh form dan filter. Select asli menjaga x-model,
// validasi form, dan event change; pengguna berinteraksi dengan combobox di bawah.
document.addEventListener('alpine:init', () => {
  let active = null, sequence = 0;
  Alpine.directive('gg-select', (select, _, { effect, cleanup }) => {
    const wrapper = document.createElement('span');
    wrapper.className = 'gg-select' + (select.classList.contains('w-auto') ? ' gg-select-auto' : '');
    const trigger = document.createElement('button');
    trigger.type = 'button'; trigger.className = select.className + ' gg-select-trigger';
    trigger.setAttribute('role', 'combobox'); trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    const caption = document.createElement('span'), arrow = document.createElement('span');
    arrow.className = 'gg-select-arrow'; arrow.textContent = '⌄'; arrow.setAttribute('aria-hidden', 'true');
    trigger.append(caption, arrow);
    const label = select.labels?.[0] || select.parentElement.querySelector('label');
    const name = select.getAttribute('aria-label') || label?.childNodes[0]?.textContent?.trim() || 'Pilihan';
    trigger.setAttribute('aria-label', name);
    const panel = document.createElement('div'); panel.className = 'gg-select-panel'; panel.hidden = true;
    const search = document.createElement('input'); search.type = 'search'; search.className = 'gg-select-search'; search.placeholder = 'Cari pilihan...'; search.setAttribute('aria-label', 'Cari ' + name.toLowerCase());
    const list = document.createElement('div'); list.className = 'gg-select-list'; list.id = 'gg-select-' + ++sequence; list.setAttribute('role', 'listbox'); list.setAttribute('aria-label', name);
    panel.append(search, list); trigger.setAttribute('aria-controls', list.id);
    select.before(wrapper); wrapper.append(select, trigger); document.body.append(panel);
    select.classList.add('gg-select-native'); select.setAttribute('aria-hidden', 'true'); select.tabIndex = -1;
    let opened = false, index = -1, options = [], disposed = false;
    function position() {
      if (!opened) return;
      const rect = trigger.getBoundingClientRect(), room = window.innerHeight - rect.bottom - 12;
      const height = Math.min(310, Math.max(130, Math.max(room, rect.top - 12)));
      panel.style.width = Math.min(Math.max(rect.width, 180), window.innerWidth - 24) + 'px';
      panel.style.left = Math.max(12, Math.min(rect.left, window.innerWidth - parseFloat(panel.style.width) - 12)) + 'px';
      panel.style.maxHeight = height + 'px'; list.style.maxHeight = Math.max(80, height - (search.hidden ? 16 : 64)) + 'px';
      panel.style.top = (room >= Math.min(height, 250) ? rect.bottom + 6 : Math.max(12, rect.top - Math.min(panel.scrollHeight, height) - 6)) + 'px';
    }
    function close(focus = false) {
      opened = false; panel.hidden = true; trigger.setAttribute('aria-expanded', 'false'); trigger.removeAttribute('aria-activedescendant');
      if (active === close) active = null;
      if (focus) trigger.focus();
    }
    function highlight(next) {
      if (!options.length) { index = -1; return; }
      index = Math.max(0, Math.min(options.length - 1, next));
      options.forEach((option, i) => option.classList.toggle('gg-select-highlight', i === index));
      trigger.setAttribute('aria-activedescendant', options[index].id);
      options[index].scrollIntoView({ block: 'nearest' });
    }
    function choose(option) {
      if (option.disabled) return;
      select.value = option.value; select.dispatchEvent(new Event('change', { bubbles: true }));
      trigger.removeAttribute('aria-invalid'); sync(); close(true);
    }
    function render() {
      list.replaceChildren(); options = [];
      const query = search.value.toLocaleLowerCase('id');
      Array.from(select.options).forEach((option, i) => {
        if (option.hidden || !option.textContent.toLocaleLowerCase('id').includes(query)) return;
        const button = document.createElement('button'); button.type = 'button'; button.className = 'gg-select-option'; button.textContent = option.textContent; button.disabled = option.disabled;
        button.tabIndex = -1; button.id = list.id + '-option-' + i; button.setAttribute('role', 'option'); button.setAttribute('aria-selected', String(option.value === select.value));
        button.addEventListener('click', () => choose(option)); list.append(button);
        if (!button.disabled) options.push(button);
      });
      if (!list.children.length) { const empty = document.createElement('p'); empty.className = 'gg-select-empty'; empty.textContent = 'Pilihan tidak ditemukan.'; list.append(empty); }
      index = -1; position();
    }
    function sync() {
      if (disposed) return;
      const modelValue = select._x_model?.get();
      if (modelValue !== undefined) select.value = String(modelValue ?? '');
      caption.textContent = select.selectedOptions[0]?.textContent || 'Pilih...';
      trigger.disabled = select.disabled; trigger.title = caption.textContent;
      trigger.setAttribute('aria-required', String(select.required));
      if (select.getAttribute('aria-describedby')) trigger.setAttribute('aria-describedby', select.getAttribute('aria-describedby'));
      if (select.disabled) close();
      if (opened) render();
    }
    function open() {
      if (select.disabled) return;
      if (active && active !== close) active();
      active = close; opened = true; search.value = ''; search.hidden = select.options.length < 10;
      panel.hidden = false; trigger.setAttribute('aria-expanded', 'true'); render();
      const selected = options.findIndex(option => option.getAttribute('aria-selected') === 'true');
      highlight(Math.max(0, selected)); if (!search.hidden) search.focus();
    }
    function keydown(event) {
      if (event.key === 'Escape' && opened) { event.preventDefault(); close(true); }
      else if (event.key === 'Tab') close();
      else if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
        event.preventDefault(); if (!opened) { open(); return; }
        highlight(event.key === 'Home' ? 0 : event.key === 'End' ? options.length - 1 : index + (event.key === 'ArrowDown' ? 1 : -1));
      } else if (event.key === 'Enter' || (event.key === ' ' && event.target === trigger)) {
        event.preventDefault(); if (!opened) open(); else if (options[index]) options[index].click();
      } else if (event.target === trigger && event.key.length === 1 && !event.ctrlKey && !event.metaKey) {
        event.preventDefault();
        if (!opened) open();
        search.hidden = false; search.value += event.key; render(); search.focus();
      }
    }
    trigger.addEventListener('click', () => opened ? close() : open()); trigger.addEventListener('keydown', keydown);
    search.addEventListener('input', render); search.addEventListener('keydown', keydown);
    const outside = event => { if (!wrapper.contains(event.target) && !panel.contains(event.target)) close(); };
    const scroll = event => { if (!panel.contains(event.target)) position(); };
    const changed = () => queueMicrotask(sync);
    const invalid = event => { event.preventDefault(); trigger.setAttribute('aria-invalid', 'true'); trigger.focus(); };
    select.addEventListener('change', changed); select.addEventListener('invalid', invalid);
    document.addEventListener('pointerdown', outside); window.addEventListener('resize', position); window.addEventListener('scroll', scroll, true);
    const observer = new MutationObserver(changed); observer.observe(select, { childList: true, subtree: true, attributes: true, characterData: true });
    // Alpine effect mengikuti penggantian model secara programatis (reset form/kecamatan).
    effect(() => { select._x_model?.get(); queueMicrotask(sync); });
    sync();
    cleanup(() => {
      disposed = true; close(); observer.disconnect(); panel.remove();
      select.removeEventListener('change', changed); select.removeEventListener('invalid', invalid);
      document.removeEventListener('pointerdown', outside); window.removeEventListener('resize', position); window.removeEventListener('scroll', scroll, true);
    });
  });
});
