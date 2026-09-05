/**
 * Hub da ficha do imóvel como grafo interativo (estilo "segundo cérebro" do
 * Obsidian): simulação de forças (d3-force), arraste com reação dos vizinhos e
 * realce em lilás dos nós/ligações conectados. Clique (sem arrastar) abre o
 * modal correspondente — abrirModal() é global na ficha.
 *
 * Dados vêm de window.HUB_CENTRO e window.HUB_NOS (definidos no PHP).
 * Degrada para uma grade de botões simples se o D3 não carregar.
 */
(function () {
  const el = document.getElementById('hub-graph');
  if (!el) return;

  const CENTRO = window.HUB_CENTRO || {};
  const NOS    = window.HUB_NOS || [];
  const LILAS  = '#b57bff';

  // ---- Fallback sem D3: grade de botões clicáveis --------------------------
  if (!window.d3) {
    el.classList.add('hub-graph-fallback');
    el.innerHTML = NOS.map(n =>
      `<button type="button" class="hub-fb" onclick="abrirModal('${n.modal}')">
         <span class="hub-fb-tile"><span class="hub-fb-emoji">${n.emoji}</span></span>
         <span class="hub-fb-lb">${n.label}</span>
       </button>`).join('');
    return;
  }

  const d3 = window.d3;

  // ---- Dimensões (quadrado) ------------------------------------------------
  let W = Math.max(320, el.clientWidth || 500);
  W = Math.min(W, 560);
  const H = W;

  // ---- Dados ---------------------------------------------------------------
  const centro = Object.assign({ id: 'centro', tipo: 'centro' }, CENTRO);
  centro.x = W / 2; centro.y = H / 2;                   // começa no meio, mas flutua junto
  const nodes = [centro].concat(NOS.map(n => Object.assign({ tipo: 'no' }, n)));
  const links = NOS.map(n => ({ source: 'centro', target: n.id }));

  // adjacência (para o realce de vizinhança)
  const adj = new Map(nodes.map(n => [n.id, new Set([n.id])]));
  links.forEach(l => { adj.get('centro').add(l.target); adj.get(l.target).add('centro'); });

  // ---- SVG -----------------------------------------------------------------
  const svg = d3.select(el).append('svg')
    .attr('class', 'hg-svg')
    .attr('viewBox', `0 0 ${W} ${H}`);

  const defs = svg.append('defs');
  const lg = defs.append('linearGradient').attr('id', 'hg-grafite').attr('x1', 0).attr('y1', 0).attr('x2', 0).attr('y2', 1);
  lg.append('stop').attr('offset', '0%').attr('stop-color', '#26262b');
  lg.append('stop').attr('offset', '100%').attr('stop-color', '#131316');
  const rg = defs.append('radialGradient').attr('id', 'hg-centro').attr('cx', '35%').attr('cy', '30%').attr('r', '75%');
  rg.append('stop').attr('offset', '0%').attr('stop-color', '#2c2c31');
  rg.append('stop').attr('offset', '78%').attr('stop-color', '#0a0a0c');

  const linkSel = svg.append('g').attr('class', 'hg-links')
    .selectAll('line').data(links).join('line').attr('class', 'hg-link');

  const nodeSel = svg.append('g').attr('class', 'hg-nodes')
    .selectAll('g').data(nodes).join('g')
    .attr('class', d => 'hg-node ' + (d.tipo === 'centro' ? 'is-centro' : 'is-no'));

  // nó central
  const c = nodeSel.filter(d => d.tipo === 'centro');
  c.append('circle').attr('class', 'hg-centro-c').attr('r', 52);
  c.append('text').attr('class', 'hg-centro-cod').attr('y', -18).text(d => d.cod || '');
  c.append('text').attr('class', 'hg-centro-nome').attr('y', 2)
    .text(d => (d.nome || '').length > 20 ? (d.nome).slice(0, 19) + '…' : (d.nome || ''));
  c.append('text').attr('class', 'hg-centro-valor').attr('y', 22).text(d => d.valor || '');

  // nós-módulo
  const m = nodeSel.filter(d => d.tipo === 'no');
  m.append('rect').attr('class', 'hg-tile').attr('x', -32).attr('y', -32).attr('width', 64).attr('height', 64).attr('rx', 18);
  m.append('text').attr('class', 'hg-emoji').attr('y', 2).text(d => d.emoji);
  m.append('text').attr('class', 'hg-label').attr('y', 50).text(d => d.label);

  // ---- Simulação de forças -------------------------------------------------
  const sim = d3.forceSimulation(nodes)
    .velocityDecay(0.35)                                  // um pouco mais "flutuante"
    .force('link', d3.forceLink(links).id(d => d.id).distance(140).strength(0.5))
    .force('charge', d3.forceManyBody().strength(-700))
    .force('collide', d3.forceCollide(d => d.tipo === 'centro' ? 66 : 46))
    // o centro flutua, mas com gravidade mais forte puxando pro meio (não vaza do quadro)
    .force('x', d3.forceX(W / 2).strength(d => d.tipo === 'centro' ? 0.14 : 0.045))
    .force('y', d3.forceY(H / 2).strength(d => d.tipo === 'centro' ? 0.14 : 0.045))
    .on('tick', ticked);

  function ticked() {
    const pad = 40;
    nodes.forEach(d => {
      d.x = Math.max(pad, Math.min(W - pad, d.x));
      d.y = Math.max(pad, Math.min(H - pad, d.y));
    });
    linkSel.attr('x1', d => d.source.x).attr('y1', d => d.source.y)
           .attr('x2', d => d.target.x).attr('y2', d => d.target.y);
    nodeSel.attr('transform', d => `translate(${d.x},${d.y})`);
  }

  // ---- Realce de vizinhança (lilás) ---------------------------------------
  function realce(id) {
    const near = adj.get(id) || new Set([id]);
    nodeSel.classed('on', d => near.has(d.id)).classed('dim', d => !near.has(d.id));
    linkSel.classed('on', l => l.source.id === id || l.target.id === id)
           .classed('dim', l => !(l.source.id === id || l.target.id === id));
  }
  function limpaRealce() {
    nodeSel.classed('on', false).classed('dim', false);
    linkSel.classed('on', false).classed('dim', false);
  }

  let arrastando = false;
  nodeSel.on('mouseenter', (e, d) => { if (!arrastando) realce(d.id); })
         .on('mouseleave', () => { if (!arrastando) limpaRealce(); });

  // ---- Arraste + clique ----------------------------------------------------
  let origem = null, moveu = false;
  const drag = d3.drag()
    .on('start', (e, d) => {
      arrastando = true; moveu = false; origem = [e.x, e.y];
      if (!e.active) sim.alphaTarget(0.3).restart();
      d.fx = d.x; d.fy = d.y;                                  // qualquer nó (inclusive o centro)
      realce(d.id);
    })
    .on('drag', (e, d) => {
      if (Math.hypot(e.x - origem[0], e.y - origem[1]) > 4) moveu = true;
      d.fx = e.x; d.fy = e.y;
    })
    .on('end', (e, d) => {
      arrastando = false;
      if (!e.active) sim.alphaTarget(0);
      d.fx = null; d.fy = null;                                // solta: volta a flutuar
      if (!moveu && d.modal && typeof abrirModal === 'function') abrirModal(d.modal);
      limpaRealce();
    });
  nodeSel.call(drag);
})();
