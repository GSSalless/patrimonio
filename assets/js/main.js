// Máscara CPF/CNPJ
function mascara_cpf_cnpj(input) {
  let v = input.value.replace(/\D/g, '');
  if (v.length <= 11) {
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
  } else {
    v = v.replace(/^(\d{2})(\d)/, '$1.$2');
    v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
    v = v.replace(/(\d{4})(\d)/, '$1-$2');
  }
  input.value = v;
}

// Máscara CEP
function mascara_cep(input) {
  let v = input.value.replace(/\D/g, '').substring(0, 8);
  if (v.length > 5) v = v.replace(/(\d{5})(\d)/, '$1-$2');
  input.value = v;
}

// Autopreenchimento de endereço via ViaCEP
async function busca_cep(cep_input) {
  const cep = cep_input.value.replace(/\D/g, '');
  if (cep.length !== 8) return;
  try {
    const r = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
    const d = await r.json();
    if (d.erro) return;
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
    set('logradouro', d.logradouro);
    set('bairro', d.bairro);
    set('cidade', d.localidade);
    set('estado', d.uf);
  } catch (_) {}
}

// Máscara monetária
function mascara_moeda(input) {
  let v = input.value.replace(/\D/g, '');
  if (!v) { input.value = ''; return; }
  v = (parseInt(v) / 100).toFixed(2);
  input.value = v.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Abas simples
function init_abas() {
  const links = document.querySelectorAll('.abas a[data-aba]');
  const paineis = document.querySelectorAll('.aba-painel');
  if (!links.length) return;

  links.forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      links.forEach(l => l.classList.remove('ativa'));
      paineis.forEach(p => p.hidden = true);
      link.classList.add('ativa');
      const alvo = document.getElementById('aba-' + link.dataset.aba);
      if (alvo) alvo.hidden = false;
    });
  });

  // Ativa primeira aba
  if (links[0]) links[0].click();
}

// Confirma exclusão
function confirmar_exclusao(msg) {
  return confirm(msg || 'Confirma a exclusão?');
}

// Menu lateral esquerdo (drawer)
function init_menu_lateral() {
  const toggle  = document.getElementById('menu-toggle');
  const menu    = document.getElementById('menu-lateral');
  const overlay = document.getElementById('menu-overlay');
  const fechar  = document.getElementById('menu-fechar');
  if (!toggle || !menu) return;

  const abrir = () => {
    document.body.classList.add('menu-aberto');
    menu.setAttribute('aria-hidden', 'false');
    toggle.setAttribute('aria-expanded', 'true');
    if (overlay) overlay.hidden = false;
  };
  const fecharMenu = () => {
    document.body.classList.remove('menu-aberto');
    menu.setAttribute('aria-hidden', 'true');
    toggle.setAttribute('aria-expanded', 'false');
    if (overlay) overlay.hidden = true;
  };

  toggle.addEventListener('click', abrir);
  if (fechar)  fechar.addEventListener('click', fecharMenu);
  if (overlay) overlay.addEventListener('click', fecharMenu);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharMenu(); });
}

// Relógio vivo no topo (atualiza o horário a cada minuto)
function init_relogio() {
  const el = document.getElementById('relogio');
  if (!el) return;
  const tick = () => {
    const d = new Date();
    const hh = String(d.getHours()).padStart(2, '0');
    const mm = String(d.getMinutes()).padStart(2, '0');
    el.textContent = `${hh}:${mm}`;
  };
  tick();
  setInterval(tick, 30000);
}

// Relógios mundiais (Gestão Geral) — usa Intl para lidar com fuso/horário de verão
function init_relogios_mundiais() {
  const nodes = document.querySelectorAll('.gg2-relogio[data-tz]');
  if (!nodes.length) return;
  const tick = () => {
    nodes.forEach((n) => {
      const tz = n.getAttribute('data-tz');
      const alvo = n.querySelector('.gg2-rel-hora');
      if (!alvo) return;
      try {
        alvo.textContent = new Intl.DateTimeFormat('pt-BR', {
          hour: '2-digit', minute: '2-digit', hour12: false, timeZone: tz,
        }).format(new Date());
      } catch (_) { /* fuso inválido: mantém --:-- */ }
    });
  };
  tick();
  setInterval(tick, 30000);
}

// Modal de seleção de cliente (admin). Abre ao clicar num item do cliente
// sem cliente setado, ou no chip do topo. Ao escolher, navega para a rota
// pretendida com ?cliente_id — o bootstrap seta e redireciona para a URL limpa.
function init_modal_cliente() {
  const modal = document.getElementById('modal-cliente');
  if (!modal) return;
  const base = modal.dataset.base || '/';
  let next = 'dashboard';

  const abrir = (n) => {
    next = n || 'dashboard';
    modal.hidden = false;
    document.body.classList.add('modal-aberto');
    const busca = modal.querySelector('.mc-busca');
    if (busca) { busca.value = ''; busca.dispatchEvent(new Event('input')); setTimeout(() => busca.focus(), 50); }
  };
  const fechar = () => { modal.hidden = true; document.body.classList.remove('modal-aberto'); };

  document.querySelectorAll('.js-abre-clientes').forEach((el) => {
    el.addEventListener('click', (e) => { e.preventDefault(); abrir(el.dataset.next); });
  });

  modal.querySelectorAll('.mc-item').forEach((it) => {
    it.addEventListener('click', (e) => {
      e.preventDefault();
      const id = it.dataset.id;
      if (!id) return;
      const sep = next.includes('?') ? '&' : '?';
      window.location.href = base + next + sep + 'cliente_id=' + encodeURIComponent(id);
    });
  });

  // Fechar: X, clique no fundo, Esc
  modal.addEventListener('click', (e) => {
    if (e.target === modal || e.target.closest('.mc-fechar')) fechar();
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.hidden) fechar(); });

  // Busca simples (filtra a lista pelo texto)
  const busca = modal.querySelector('.mc-busca');
  if (busca) {
    busca.addEventListener('input', () => {
      const q = busca.value.trim().toLowerCase();
      modal.querySelectorAll('.mc-item').forEach((it) => {
        it.hidden = q !== '' && !it.textContent.toLowerCase().includes(q);
      });
    });
  }
}

document.addEventListener('DOMContentLoaded', init_abas);
document.addEventListener('DOMContentLoaded', init_menu_lateral);
document.addEventListener('DOMContentLoaded', init_relogio);
document.addEventListener('DOMContentLoaded', init_relogios_mundiais);
document.addEventListener('DOMContentLoaded', init_modal_cliente);
