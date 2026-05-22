/**
 * scopi.js — JavaScript principal do SCOPi
 * Sidebar retrátil, submenus, modais AJAX, sistema de notificações
 */

/* ════════════════════════════════════════════
   SIDEBAR
   ════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('sidebar');
  const btnMenu = document.getElementById('btnMenu');
  const CHAVE_SB = 'scopi_sidebar_retraida';

  function aplicarSidebar(retraida) {
    sidebar?.classList.toggle('retraida', retraida);
    document.body.classList.toggle('sidebar-retraida', retraida);
  }
  aplicarSidebar(localStorage.getItem(CHAVE_SB) === '1');

  btnMenu?.addEventListener('click', () => {
    const retraida = !sidebar.classList.contains('retraida');
    aplicarSidebar(retraida);
    localStorage.setItem(CHAVE_SB, retraida ? '1' : '0');
  });

  // Abre submenu ativo automaticamente
  document.querySelectorAll('.submenu .nav-link.ativo').forEach(l => {
    l.closest('.submenu')?.closest('.nav-item')?.classList.add('aberto');
  });
});

/* Submenus */
function toggleSubmenu(botao) {
  const item = botao.closest('.nav-item');
  const pai  = item.parentElement;
  pai?.querySelectorAll('.nav-item.aberto').forEach(i => { if(i !== item) i.classList.remove('aberto'); });
  item.classList.toggle('aberto');
}

/* Fechar modal com Esc */
document.addEventListener('keydown', e => { if(e.key === 'Escape') Scopi.fecharTodos(); });

/* ════════════════════════════════════════════
   OBJETO PRINCIPAL: Scopi
   ════════════════════════════════════════════ */
const Scopi = {
  url(path) {
    if (!path) return path;
    if (/^https?:\/\//i.test(path)) return path;
    const base = (window.SCOPI_BASE || '').replace(/\/+$/, '');
    return `${base}/${String(path).replace(/^\/+/, '')}`;
  },

  /* ── Modais genéricos ────────────────────── */
  abrirModal(id) {
    const el = document.getElementById(id);
    if(!el) return;
    el.classList.add('aberto');
    document.body.style.overflow = 'hidden';
    el.addEventListener('click', e => { if(e.target===el) Scopi.fecharModal(id); }, {once:true});
  },
  fecharModal(id) {
    document.getElementById(id)?.classList.remove('aberto');
    if(!document.querySelector('.overlay-modal.aberto')) document.body.style.overflow = '';
  },
  fecharTodos() {
    document.querySelectorAll('.overlay-modal.aberto').forEach(m => m.classList.remove('aberto'));
    document.body.style.overflow = '';
  },

  abrirCadastro(idModal, idForm) {
    const form = document.getElementById(idForm);
    if(form) {
      form.reset();
      const campoId = form.querySelector('input[name="id"]');
      if(campoId) campoId.value = '0';
      const titulo = document.querySelector(`#${idModal} .modal-titulo span`);
      if(titulo) titulo.textContent = 'Novo Cadastro';
    }
    Scopi.ativarAba(idModal, 'editar');
    Scopi.abrirModal(idModal);
  },

  async abrirRegistro(idModal, idForm, urlDados, id, abaInicial='visualizar') {
    Scopi.abrirModal(idModal);
    const overlay = document.getElementById(idModal);
    const corpo   = overlay?.querySelector('.modal-corpo');
    if(!corpo) return;
    if(!overlay.dataset.htmlOriginal) overlay.dataset.htmlOriginal = corpo.innerHTML;
    corpo.innerHTML = '<div class="carregando-modal"><div class="spinner"></div> Carregando...</div>';
    try {
      const resp = await fetch(`${Scopi.url(urlDados)}?id=${id}`, {headers:{'X-Requested-With':'XMLHttpRequest'}});
      const json = await resp.json();
      if(!json.sucesso) { Scopi.toast('erro', json.mensagem||'Erro ao carregar.'); Scopi.fecharModal(idModal); return; }
      corpo.innerHTML = overlay.dataset.htmlOriginal;
      Scopi.preencherVisualizacao(idModal, json.dados);
      Scopi.preencherFormulario(idForm, json.dados);
      const titulo = overlay.querySelector('.modal-titulo span');
      if(titulo) titulo.textContent = `#${json.dados.codigo||json.dados.numero||json.dados.id}`;
      Scopi.ativarAba(idModal, abaInicial);
    } catch(e) { Scopi.toast('erro','Falha na comunicação.'); Scopi.fecharModal(idModal); }
  },

  preencherVisualizacao(idModal, dados) {
    const o = document.getElementById(idModal);
    o?.querySelectorAll('[data-campo]').forEach(el => { el.textContent = dados[el.dataset.campo]??'—'; });
    o?.querySelectorAll('[data-badge]').forEach(el => {
      const v = dados[el.dataset.badge]??'';
      el.textContent = v; el.className = `badge badge-${v.replace(/_/g,'-')}`;
    });
  },
  preencherFormulario(idForm, dados) {
    const form = document.getElementById(idForm);
    if(!form) return;
    Object.entries(dados).forEach(([k,v]) => {
      const c = form.querySelector(`[name="${k}"]`);
      if(!c) return;
      if(c.tagName==='SELECT') c.value=v??'';
      else if(c.type==='checkbox') c.checked=!!v;
      else c.value=v??'';
    });
  },
  ativarAba(idModal, aba) {
    const o = document.getElementById(idModal);
    if(!o) return;
    o.querySelectorAll('.aba-btn').forEach(b => b.classList.toggle('ativa', b.dataset.aba===aba));
    o.querySelectorAll('.conteudo-aba').forEach(d => d.classList.toggle('ativo', d.dataset.aba===aba));
  },
  async enviarFormulario(idForm, idModal, url, cb=null) {
    const form = document.getElementById(idForm);
    if(!form) return;
    const btn = document.querySelector(`#${idModal} .btn-salvar`);
    if(btn) { btn.disabled=true; btn.textContent='Salvando...'; }
    try {
      const resp = await fetch(Scopi.url(url), {method:'POST',body:new FormData(form),headers:{'X-Requested-With':'XMLHttpRequest'}});
      const json = await resp.json();
      if(json.sucesso) { Scopi.fecharModal(idModal); Scopi.toast('sucesso',json.mensagem||'Salvo!'); if(cb) cb(json); else setTimeout(()=>location.reload(),900); }
      else Scopi.toast('erro',json.mensagem||'Erro ao salvar.');
    } catch(e) { Scopi.toast('erro','Falha na comunicação.'); }
    finally { if(btn) { btn.disabled=false; btn.textContent='Salvar'; } }
  },
  async confirmarAcao(msg, url, dados, cb=null) {
    if(!confirm(msg)) return;
    try {
      const fd = new FormData();
      Object.entries(dados).forEach(([k,v]) => fd.append(k,v));
      const resp = await fetch(Scopi.url(url), {method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}});
      const json = await resp.json();
      if(json.sucesso) { Scopi.toast('sucesso',json.mensagem); if(cb) cb(json); else setTimeout(()=>location.reload(),900); }
      else Scopi.toast('erro',json.mensagem);
    } catch(e) { Scopi.toast('erro','Falha na comunicação.'); }
  },
  toast(tipo, texto) {
    let el = document.getElementById('scopi-toast');
    if(!el) { el=document.createElement('div'); el.id='scopi-toast'; el.className='toast'; document.body.appendChild(el); }
    el.textContent=texto; el.className=`toast ${tipo}`;
    requestAnimationFrame(()=>el.classList.add('visivel'));
    clearTimeout(el._t); el._t=setTimeout(()=>el.classList.remove('visivel'),3500);
  },
  toggleCheckboxes(master) {
    document.querySelectorAll('.checkbox-linha').forEach(cb => { cb.checked=master.checked; });
  },

  /* ── Notificações ────────────────────────── */
  abrirNotificacoes() {
    Scopi.Notif.renderLista();
    Scopi.abrirModal('modalNotificacoes');
  },

  /* ════════════════════════════════════════════
     Scopi.Notif — Sistema de Notificações
     Dados mockados; em produção vêm de /notificacoes/dados
     ════════════════════════════════════════════ */
  Notif: {

    _ativa: null,       // ID da notificação aberta
    _filtro: 'todas',   // filtro de categoria ativo

    /**
     * Banco de dados de notificações (mock)
     * Em produção: fetch('/notificacoes/dados').then(r=>r.json())
     * Cada notificação pode ter "referencias" com rota e texto clicável
     */
    _dados: [
      {
        id: 1, lida: false, categoria: 'solicitacao',
        assunto: 'Sua solicitação foi autorizada',
        remetente: 'Sistema SCOPi', tempo: 'há 10 minutos',
        preview: 'A solicitação SOL-20260517-1234 foi aprovada pelo gerente.',
        mensagem: `Sua solicitação foi aprovada com sucesso pelo gerente responsável.<br><br>
          Clique no número abaixo para acompanhar o andamento:`,
        referencias: [{ tipo: 'solicitacao', numero: 'SOL-20260517-1234', rota: '/solicitacoes?id=1234' }]
      },
      {
        id: 2, lida: false, categoria: 'solicitacao',
        assunto: 'Nova solicitação aguarda aprovação',
        remetente: 'João da Silva', tempo: 'há 25 minutos',
        preview: 'A solicitação SOL-20260517-1235 está aguardando sua aprovação.',
        mensagem: `Uma nova solicitação foi registrada e aguarda sua aprovação como gerente.<br><br>
          Acesse a solicitação para analisar e tomar uma decisão:`,
        referencias: [{ tipo: 'solicitacao', numero: 'SOL-20260517-1235', rota: '/solicitacoes?id=1235' }]
      },
      {
        id: 3, lida: false, categoria: 'ordem',
        assunto: 'Ordem de Compra emitida',
        remetente: 'Setor de Compras', tempo: 'há 1 hora',
        preview: 'A ordem OC-20260517-0089 foi emitida para o fornecedor.',
        mensagem: `Uma ordem de compra foi gerada a partir da cotação aprovada e enviada ao fornecedor.<br><br>
          Acompanhe o status pela referência abaixo:`,
        referencias: [{ tipo: 'ordem', numero: 'OC-20260517-0089', rota: '/ordens?id=89' }]
      },
      {
        id: 4, lida: true, categoria: 'cotacao',
        assunto: 'Fornecedor respondeu à cotação',
        remetente: 'Sistema SCOPi', tempo: 'há 2 horas',
        preview: 'O fornecedor Tecno Suprimentos respondeu à COT-20260517-0042.',
        mensagem: `O fornecedor <strong>Tecno Suprimentos Ltda.</strong> enviou sua proposta para a cotação abaixo.<br><br>
          Acesse a cotação para comparar propostas e selecionar o vencedor:`,
        referencias: [{ tipo: 'cotacao', numero: 'COT-20260517-0042', rota: '/cotacoes?id=42' }]
      },
      {
        id: 5, lida: true, categoria: 'nota',
        assunto: 'Nota Fiscal recebida e registrada',
        remetente: 'Contabilidade', tempo: 'há 3 horas',
        preview: 'NF-e 000123456 referente à OC-20260517-0071 foi lançada.',
        mensagem: `A nota fiscal eletrônica abaixo foi importada e vinculada à ordem de compra correspondente.<br><br>
          Confira os detalhes:`,
        referencias: [
          { tipo: 'nota',  numero: 'NF-e 000123456',    rota: '/notas?id=456' },
          { tipo: 'ordem', numero: 'OC-20260517-0071',  rota: '/ordens?id=71'  }
        ]
      },
      {
        id: 6, lida: true, categoria: 'alerta',
        assunto: 'Cotação prestes a vencer',
        remetente: 'Sistema SCOPi', tempo: 'há 5 horas',
        preview: 'A cotação COT-20260516-0039 vence em 24 horas.',
        mensagem: `<strong>Atenção!</strong> A cotação abaixo vence em menos de 24 horas e ainda não possui proposta selecionada.<br><br>
          Acesse para analisar as propostas recebidas antes do prazo:`,
        referencias: [{ tipo: 'cotacao', numero: 'COT-20260516-0039', rota: '/cotacoes?id=39' }]
      },
      {
        id: 7, lida: true, categoria: 'solicitacao',
        assunto: 'Solicitação cancelada',
        remetente: 'Maria Oliveira', tempo: 'ontem',
        preview: 'A solicitação SOL-20260516-1198 foi cancelada pelo solicitante.',
        mensagem: `A solicitação abaixo foi cancelada pelo próprio solicitante antes de ser processada.<br><br>
          Nenhuma ação é necessária da sua parte:`,
        referencias: [{ tipo: 'solicitacao', numero: 'SOL-20260516-1198', rota: '/solicitacoes?id=1198' }]
      },
    ],

    /* Ícone por categoria */
    _icone(cat) {
      const mapa = {
        solicitacao: 'iconeSolicitacao.svg',
        ordem:       'iconeOC.svg',
        cotacao:     'iconeOC.svg',
        nota:        'iconeNF.svg',
        alerta:      'iconeAlerta.svg',
        sistema:     'iconeMenu.svg',
      };
      return Scopi.url(`/public/assets/icons/${mapa[cat] || 'iconeAlerta.svg'}`);
    },

    /* Nome legível da categoria */
    _nomeCat(cat) {
      return { solicitacao:'Solicitação', ordem:'Ordem de Compra', cotacao:'Cotação', nota:'Nota Fiscal', alerta:'Alerta', sistema:'Sistema' }[cat] || cat;
    },

    /* Dados filtrados */
    _filtrados() {
      if(this._filtro === 'todas') return this._dados;
      return this._dados.filter(n => n.categoria === this._filtro);
    },

    /* Conta não lidas */
    _contarNaoLidas() {
      return this._dados.filter(n => !n.lida).length;
    },

    /* Atualiza badge no topbar */
    _atualizarBadge() {
      const badge = document.getElementById('badgeNotif');
      if(!badge) return;
      const c = this._contarNaoLidas();
      badge.textContent = c > 9 ? '+9' : c;
      badge.style.display = c === 0 ? 'none' : 'flex';
    },

    /* Renderiza a lista de notificações */
    renderLista() {
      const lista = document.getElementById('notifLista');
      if(!lista) return;
      const dados = this._filtrados();

      if(dados.length === 0) {
        lista.innerHTML = `<div class="notif-vazio" style="padding:40px 20px;">
          <img src="${Scopi.url('/public/assets/icons/iconeNotificacao.svg')}" alt="" style="width:40px;opacity:.25;display:block;margin:0 auto 8px;">
          <p style="text-align:center;font-size:.75rem;color:#aaa;">Nenhuma notificação nesta categoria</p>
        </div>`;
        return;
      }

      lista.innerHTML = dados.map(n => `
        <div class="notif-item ${n.lida?'':'nao-lida'} ${this._ativa===n.id?'ativa':''}"
             data-id="${n.id}"
             onclick="Scopi.Notif.abrirDetalhe(${n.id})">
          <div class="notif-icone ${n.categoria}">
            <img src="${this._icone(n.categoria)}" alt="">
          </div>
          <div class="notif-item-conteudo">
            <div class="notif-assunto">${this._esc(n.assunto)}</div>
            <div class="notif-preview">${this._esc(n.preview)}</div>
            <span class="notif-tempo">${this._esc(n.tempo)}</span>
          </div>
        </div>
      `).join('');

      this._atualizarBadge();
    },

    /* Abre o detalhe de uma notificação */
    abrirDetalhe(id) {
      const n = this._dados.find(x => x.id === id);
      if(!n) return;

      // Marca como lida
      n.lida = true;
      this._ativa = id;

      // Atualiza item na lista
      document.querySelectorAll('.notif-item').forEach(el => {
        el.classList.toggle('ativa', Number(el.dataset.id) === id);
        if(Number(el.dataset.id) === id) el.classList.remove('nao-lida');
      });
      this._atualizarBadge();

      // Monta o detalhe
      const vazio    = document.getElementById('notifVazio');
      const conteudo = document.getElementById('notifDetalheConteudo');
      if(vazio)    vazio.style.display    = 'none';
      if(conteudo) conteudo.style.display = 'flex';

      // Assunto
      const elAssunto = document.getElementById('notifDetAssunto');
      if(elAssunto) elAssunto.textContent = n.assunto;

      // Categoria badge
      const elCat = document.getElementById('notifDetCategoria');
      if(elCat) {
        elCat.textContent = this._nomeCat(n.categoria);
        elCat.className   = `notif-badge-cat ${n.categoria}`;
      }

      // Tempo e remetente
      const elTempo = document.getElementById('notifDetTempo');
      if(elTempo) elTempo.textContent = n.tempo;
      const elRem = document.getElementById('notifDetRemetente');
      if(elRem) elRem.textContent = n.remetente;

      // Mensagem + referências clicáveis
      const elTexto = document.getElementById('notifDetTexto');
      if(elTexto) {
        let html = `<p style="margin-bottom:14px;">${n.mensagem}</p>`;

        if(n.referencias && n.referencias.length > 0) {
          html += `<div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">`;
          n.referencias.forEach(ref => {
            html += `
              <a href="${Scopi.url(ref.rota)}"
                 class="notif-link-ref"
                 onclick="Scopi.fecharModal('modalNotificacoes')"
                 title="Ir para ${ref.numero}">
                <img src="${this._icone(ref.tipo)}" alt="">
                ${ref.numero}
              </a>`;
          });
          html += `</div>`;
        }
        elTexto.innerHTML = html;
      }

      // Botão marcar lida
      const btnLida = document.getElementById('btnMarcarLida');
      if(btnLida) btnLida.textContent = 'Marcada como lida ✓';
    },

    /* Marcar notificação ativa como lida */
    marcarLida() {
      if(this._ativa === null) return;
      const n = this._dados.find(x => x.id === this._ativa);
      if(n) n.lida = true;
      this.renderLista();
      // Reabre o detalhe para atualizar botão
      this.abrirDetalhe(this._ativa);
    },

    /* Excluir notificação ativa */
    excluir() {
      if(this._ativa === null) return;
      this._dados = this._dados.filter(x => x.id !== this._ativa);
      this._ativa = null;

      // Oculta detalhe, mostra vazio
      const vazio    = document.getElementById('notifVazio');
      const conteudo = document.getElementById('notifDetalheConteudo');
      if(vazio)    vazio.style.display    = 'flex';
      if(conteudo) conteudo.style.display = 'none';

      this.renderLista();
    },

    /* Marcar todas como lidas */
    lerTodas() {
      this._dados.forEach(n => n.lida = true);
      this.renderLista();
      if(this._ativa !== null) this.abrirDetalhe(this._ativa);
    },

    /* Filtrar por categoria */
    filtrar(cat, btn) {
      this._filtro = cat;
      this._ativa  = null;

      // Oculta detalhe
      const vazio    = document.getElementById('notifVazio');
      const conteudo = document.getElementById('notifDetalheConteudo');
      if(vazio)    vazio.style.display    = 'flex';
      if(conteudo) conteudo.style.display = 'none';

      // Atualiza botões de filtro
      document.querySelectorAll('.notif-filtro-btn').forEach(b => b.classList.remove('ativo'));
      btn?.classList.add('ativo');

      this.renderLista();
    },

    /* Escapa HTML */
    _esc(str) {
      return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
  }
};

document.addEventListener('DOMContentLoaded', () => {
  /* Salva HTML original dos modais */
  document.querySelectorAll('.overlay-modal').forEach(o => {
    const corpo = o.querySelector('.modal-corpo');
    if(corpo) o.dataset.htmlOriginal = corpo.innerHTML;
  });

  /* Flash auto-hide */
  const flashEl = document.querySelector('.mensagem-flash');
  if(flashEl) setTimeout(()=>{ flashEl.style.transition='opacity .5s'; flashEl.style.opacity='0'; }, 4000);

  /* Badge inicial */
  setTimeout(() => Scopi.Notif._atualizarBadge(), 100);
});
