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

document.addEventListener('DOMContentLoaded', init_abas);
document.addEventListener('DOMContentLoaded', init_menu_lateral);
