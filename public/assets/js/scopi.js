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
  const sidebar = document.getElementById('sidebar');
  const item = botao.closest('.nav-item');
  const pai  = item.parentElement;

  // Se a sidebar estiver recolhida, expandir primeiro e depois abrir o submenu
  if (sidebar && sidebar.classList.contains('retraida')) {
    sidebar.classList.remove('retraida');
    document.body.classList.remove('sidebar-retraida');
    localStorage.setItem('scopi_sidebar_retraida', '0');
    // Fecha outros submenus abertos e abre o clicado
    pai?.querySelectorAll('.nav-item.aberto').forEach(i => { if(i !== item) i.classList.remove('aberto'); });
    item.classList.add('aberto');
    return;
  }

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

  formatarStatus(status) {
    if (!status) return '—';
    const mapa = {
      'aberto': 'Aberto',
      'em_aberto': 'Aberto',
      'aberta': 'Aberto',
      'autorizado': 'Autorizado',
      'autorizada': 'Autorizado',
      'em_cotacao': 'Em Cotação',
      'fechada': 'Fechado',
      'fechado': 'Fechado',
      'enviado': 'Enviado',
      'enviada': 'Enviado',
      'parcialmente_atendido': 'Parcialmente Atendido',
      'parcialmente_atendida': 'Parcialmente Atendido',
      'concluido': 'Concluído',
      'concluida': 'Concluído',
      'cancelado': 'Cancelado',
      'cancelada': 'Cancelado',
      'recusado': 'Recusado',
      'recusada': 'Recusado',
      'registrada': 'Registrado',
      'vinculada': 'Vinculado'
    };
    return mapa[status.toLowerCase()] || (status.charAt(0).toUpperCase() + status.slice(1).toLowerCase());
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

    // Limpa pilha de buscas aninhadas ao fechar modal de busca
    if (id === 'modalBuscaGlobal') {
      pilhaBuscasAninhadas = [];
      document.getElementById('btnVoltarBusca').style.display = 'none';
    }
  },
  fecharTodos() {
    document.querySelectorAll('.overlay-modal.aberto').forEach(m => m.classList.remove('aberto'));
    document.body.style.overflow = '';
  },

  abrirCadastro(idModal, idForm) {
    const form = document.getElementById(idForm);
    const overlay = document.getElementById(idModal);
    if(form) {
      form.reset();
      const campoId = form.querySelector('input[name="id"]');
      if(campoId) campoId.value = '0';
      const titulo = document.querySelector(`#${idModal} .modal-titulo span`);
      if(titulo) {
        if(!overlay.dataset.tituloBase) overlay.dataset.tituloBase = titulo.textContent.replace(/^(Cadastro de |Edição de Cadastro de |Histórico de Cadastro de )/, '').trim();
        titulo.textContent = `Cadastro de ${overlay.dataset.tituloBase}`;
      }
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
      const resp = await fetch(`${Scopi.url(urlDados)}?id=${id}`, {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
      const json = await resp.json();
      if(!json.sucesso) { Scopi.toast('erro', json.mensagem||'Erro ao carregar.'); Scopi.fecharModal(idModal); return; }
      corpo.innerHTML = overlay.dataset.htmlOriginal;
      Scopi.preencherVisualizacao(idModal, json.dados);
      Scopi.preencherFormulario(idForm, json.dados);
      const titulo = overlay.querySelector('.modal-titulo span');
      if(titulo) {
        if(!overlay.dataset.tituloBase) overlay.dataset.tituloBase = titulo.textContent.replace(/^(Cadastro de |Edição de Cadastro de |Histórico de Cadastro de )/, '').trim();
        titulo.textContent = (abaInicial === 'visualizar') ? `Cadastro de ${overlay.dataset.tituloBase}` : `Edição de Cadastro de ${overlay.dataset.tituloBase}`;
      }
      
      overlay.dataset.entidadeId = json.dados.id;
      const matches = urlDados.match(/^\/([^\/]+)\//);
      if(matches && matches[1]) overlay.dataset.entidadeNome = matches[1];

      Scopi.ativarAba(idModal, abaInicial);
    } catch(e) { Scopi.toast('erro','Falha na comunicação.'); Scopi.fecharModal(idModal); }
  },

  preencherVisualizacao(idModal, dados) {
    const o = document.getElementById(idModal);
    o?.querySelectorAll('[data-campo]').forEach(el => { el.textContent = dados[el.dataset.campo]??'—'; });
    o?.querySelectorAll('[data-badge]').forEach(el => {
      const v = dados[el.dataset.badge]??'';
      const badgeKey = el.dataset.badge;
      const textoFormatado = (badgeKey === 'status' || badgeKey === 'situacao') ? Scopi.formatarStatus(v) : v;
      el.textContent = textoFormatado; el.className = `badge badge-${v.replace(/_/g,'-')}`;
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
    
    const btnSalvar = o.querySelector('.btn-salvar');
    if(btnSalvar) {
      btnSalvar.style.display = (aba === 'visualizar') ? 'none' : 'inline-flex';
    }
    
    if (typeof window.scopiAbaChangeHook === 'function') {
        window.scopiAbaChangeHook(idModal, aba);
    }
    window.dispatchEvent(new CustomEvent('scopiAbaChange', { detail: { idModal, aba } }));
  },
  async enviarFormulario(idForm, idModal, url, cb=null) {
    const form = document.getElementById(idForm);
    if(!form) return;
    const btn = document.querySelector(`#${idModal} .btn-salvar`);
    if(btn) { btn.disabled=true; btn.textContent='Salvando...'; }
    try {
      const resp = await fetch(Scopi.url(url), {method:'POST',body:new FormData(form),credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
      const json = await resp.json();
      if(json.sucesso) { Scopi.fecharModal(idModal); Scopi.toast('sucesso',json.mensagem||'Salvo!'); if(cb) cb(json); else setTimeout(()=>location.reload(),900); }
      else Scopi.toast('erro',json.mensagem||'Erro ao salvar.');
    } catch(e) { Scopi.toast('erro','Falha na comunicação.'); }
    finally { if(btn) { btn.disabled=false; btn.textContent='Salvar'; } }
  },
  async confirmarAcao(msg, url, dados, cb=null) {
    Scopi.confirmar(msg, async () => {
      try {
        const fd = new FormData();
        Object.entries(dados).forEach(([k,v]) => fd.append(k,v));
        const resp = await fetch(Scopi.url(url), {method:'POST',body:fd,credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
        const json = await resp.json();
        if(json.sucesso) { Scopi.toast('sucesso',json.mensagem); if(cb) cb(json); else setTimeout(()=>location.reload(),900); }
        else Scopi.toast('erro',json.mensagem);
      } catch(e) { Scopi.toast('erro','Falha na comunicação.'); }
    });
  },
  toast(tipo, texto) {
    let el = document.getElementById('scopi-toast');
    if(!el) { el=document.createElement('div'); el.id='scopi-toast'; el.className='toast'; document.body.appendChild(el); }
    el.textContent=texto; el.className=`toast ${tipo}`;
    requestAnimationFrame(()=>el.classList.add('visivel'));
    clearTimeout(el._t); el._t=setTimeout(()=>el.classList.remove('visivel'),3500);
  },

  confirmar(mensagem, callback, callbackNao) {
    const modal = document.createElement('div');
    modal.className = 'overlay-modal';
    modal.innerHTML = `
      <div class="modal modal-pequeno" style="max-width: 450px;">
        <div class="modal-cabecalho">
          <div class="modal-titulo"><img src="${Scopi.url('/public/assets/icons/iconeAlerta.svg')}" alt="" style="width:20px;height:20px;margin-right:8px;"> Confirmação</div>
        </div>
        <div class="modal-corpo" style="padding: 20px; text-align: center;">
          <p style="margin: 0 0 20px 0; font-size: 1rem; line-height: 1.5;">${String(mensagem).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}</p>
        </div>
        <div class="modal-rodape">
          <button class="btn btn-secundario" id="btnCancelarAcao">Cancelar</button>
          <button class="btn btn-perigo" id="btnConfirmarAcao">Confirmar</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    modal.classList.add('aberto');
    const btnConfirmar = modal.querySelector('#btnConfirmarAcao');
    const btnCancelar = modal.querySelector('#btnCancelarAcao');
    btnConfirmar.addEventListener('click', () => {
      modal.remove();
      if(typeof callback === 'function') callback();
    });
    btnCancelar.addEventListener('click', () => {
      modal.remove();
      if(typeof callbackNao === 'function') callbackNao();
    });
  },

  toggleCheckboxes(master) {
    document.querySelectorAll('.checkbox-linha').forEach(cb => { cb.checked=master.checked; });
  },

  /* ── Notificações ────────────────────────── */
  abrirNotificacoes() {
    Scopi.Notif.renderLista();
    Scopi.abrirModal('modalNotificacoes');
  },

  Notif: {
    _ativa: null,
    _filtro: 'todas',
    _dados: [],
    _icone(cat) { return Scopi.url(`/public/assets/icons/iconeAlerta.svg`); },
    _nomeCat(cat) { return cat.charAt(0).toUpperCase() + cat.slice(1); },
    _filtrados() { return this._filtro === 'todas' ? this._dados : this._dados.filter(n => n.categoria === this._filtro); },
    _contarNaoLidas() { return this._dados.filter(n => !n.lida).length; },
    _atualizarBadge() {
      const badge = document.querySelector('#badgeNotif');
      if(!badge) return;
      const count = this._contarNaoLidas();
      badge.textContent = count;
      badge.style.display = count > 0 ? 'flex' : 'none';
    },
    async renderLista() {
      try {
        const resp = await fetch(Scopi.url(`/notificacoes/listar?categoria=${this._filtro}`), {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
        const json = await resp.json();
        if(json.sucesso && json.dados) {
          this._dados = json.dados;
          const cont = document.getElementById('notifLista');
          if(!cont) return;

          const filtrados = this._filtrados();
          if(filtrados.length === 0) {
            cont.innerHTML = '<div style="padding:20px;text-align:center;color:#888;">Nenhuma notificação</div>';
            return;
          }

          let html = '';
          filtrados.forEach(notif => {
            html += `<div class="notif-item ${notif.lida ? '' : 'nao-lida'}" onclick="Scopi.Notif.abrirDetalhe(${notif.id})" style="cursor:pointer;">
              <div class="notif-item-icon"><img src="${Scopi.url('/public/assets/icons/iconeAlerta.svg')}" style="width:16px;filter:brightness(0) invert(1);" alt=""></div>
              <div class="notif-item-conteudo">
                <div class="notif-item-assunto">${this._esc(notif.titulo)}</div>
                <div class="notif-item-preview">${this._esc(notif.mensagem.substring(0, 60))}...</div>
                <div class="notif-item-tempo">${notif.criado_em}</div>
              </div>
              ${!notif.lida ? '<div class="notif-item-indicador"></div>' : ''}
            </div>`;
          });
          cont.innerHTML = html;
          this._atualizarBadge();
        }
      } catch(e) {
        console.error('Erro ao carregar notificações:', e);
      }
    },
    async abrirDetalhe(id) {
      try {
        const resp = await fetch(Scopi.url(`/notificacoes/dados?id=${id}`), {credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
        const json = await resp.json();
        if(json.sucesso && json.dados) {
          const notif = json.dados;
          document.getElementById('notifDetAssunto').textContent = this._esc(notif.titulo);
          document.getElementById('notifDetCategoria').textContent = this._nomeCat(notif.categoria);
          document.getElementById('notifDetTempo').textContent = notif.criado_em;
          document.getElementById('notifDetRemetente').textContent = notif.remetente || 'Sistema';
          document.getElementById('notifDetTexto').textContent = this._esc(notif.mensagem);

          document.getElementById('notifVazio').style.display = 'none';
          document.getElementById('notifDetalheConteudo').style.display = 'flex';

          document.getElementById('btnMarcarLida').textContent = notif.lida ? 'Marcar como não lida' : 'Marcar como lida';
          document.getElementById('btnMarcarLida').onclick = () => this.marcarLida(id);

          this._ativa = id;
        }
      } catch(e) {
        console.error('Erro ao abrir notificação:', e);
      }
    },
    async marcarLida(id) {
      try {
        await fetch(Scopi.url(`/notificacoes/marcar-lida`), {method:'POST', credentials:'include', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body:`id=${id}`});
        this.renderLista();
      } catch(e) { console.error('Erro:', e); }
    },
    async excluir(id) {
      Scopi.confirmar('Deseja excluir esta notificação?', async () => {
        try {
          const resp = await fetch(Scopi.url(`/notificacoes/excluir`), {method:'POST', credentials:'include', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body:`id=${id}`});
          const json = await resp.json();
          if(json.sucesso) {
            Scopi.fecharModal('notifDetalhModal');
            this.renderLista();
            Scopi.toast('sucesso', 'Notificação excluída');
          }
        } catch(e) { console.error('Erro:', e); }
      });
    },
    async lerTodas() {
      try {
        await fetch(Scopi.url(`/notificacoes/ler-todas`), {method:'POST', credentials:'include', headers:{'X-Requested-With':'XMLHttpRequest'}});
        this.renderLista();
      } catch(e) { console.error('Erro:', e); }
    },
    filtrar(cat, btn) {
      this._filtro = cat;
      document.querySelectorAll('.notif-filtro-btn').forEach(b => b.classList.remove('ativo'));
      btn.classList.add('ativo');
      this.renderLista();
    },
    _esc(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
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

/* --------------------------------------------
   MODAIS GLOBAIS (Busca e Histórico)
   -------------------------------------------- */

let buscaGlobalAtual = { tabela: '', idCodigo: '', idNome: '', filtros: {}, contexto: '' };
let pilhaBuscasAninhadas = []; // Histórico de buscas aninhadas para navegação

// Função para gerar filtros contextuais
function gerarFiltrosBuscaGlobal(tabela, contexto = '') {
    // Esconder busca por termo quando há filtros específicos
    const divBuscaTermo = document.getElementById('divBuscaTermoGlobal');
    if(divBuscaTermo) divBuscaTermo.style.display = 'none';

    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px;">';

    const criarCampo = (label, type = 'text', id, placeholder = '', onchange = '') => {
        const changeEvent = onchange || `buscaGlobalAtual.filtros['${id}'] = this.value`;
        return `<div style="display:flex;flex-direction:column;"><label style="font-size:0.85rem;font-weight:500;margin-bottom:4px;">${label}</label><input type="${type}" id="filtro_${id}" placeholder="${placeholder}" onchange="${changeEvent}" style="width:100%;padding:6px;border:1px solid #E4D7EA;border-radius:4px;font-size:0.9rem;"></div>`;
    };

    const criarSelect = (label, id, options) => {
        const opts = options.map(o => `<option value="${o.value}">${o.label}</option>`).join('');
        const onchange = `buscaGlobalAtual.filtros['${id}'] = this.value`;
        return `<div style="display:flex;flex-direction:column;"><label style="font-size:0.85rem;font-weight:500;margin-bottom:4px;">${label}</label><select id="filtro_${id}" onchange="${onchange}" style="width:100%;padding:6px;border:1px solid #E4D7EA;border-radius:4px;font-size:0.9rem;">${opts}</select></div>`;
    };

    const criarCampoBuscaAninhada = (label, id, tabela, idCampoVisualizacao) => {
        return `<div style="display:flex;flex-direction:column;">
                    <label style="font-size:0.85rem;font-weight:500;margin-bottom:4px;">${label}</label>
                    <div style="display:flex;gap:4px;">
                        <input type="text" id="filtro_${id}_codigo" placeholder="Código..." onchange="buscaGlobalAtual.filtros['${id}'] = this.value" style="flex:1;padding:6px;border:1px solid #E4D7EA;border-radius:4px;font-size:0.9rem;">
                        <button type="button" class="btn btn-primario" style="padding:4px 6px;font-size:0.8rem;" onclick="Scopi.iconeBuscaAninhada('${tabela}', 'filtro_${id}_codigo', '${idCampoVisualizacao}')"><img src="${Scopi.url('/public/assets/icons/iconeBusca.svg')}" style="width:13px;margin:0;" alt="Buscar"></button>
                    </div>
                    <span id="${idCampoVisualizacao}" style="font-size:0.85rem;color:#666;margin-top:4px;"></span>
                </div>`;
    };

    switch(tabela) {
        case 'usuarios':
            html += criarCampo('Matrícula', 'text', 'matricula', 'Ex: 25000000');
            html += criarCampo('Nome', 'text', 'nome', '');
            html += criarCampoBuscaAninhada('Departamento', 'departamento_id', 'departamentos', 'nomeDeptoUsuario');
            html += criarSelect('Perfil de Acesso', 'perfil', [
                {value: '', label: 'Todos'},
                {value: 'administrador', label: 'Administrador'},
                {value: 'cadastrador', label: 'Cadastrador'},
                {value: 'comprador', label: 'Comprador'},
                {value: 'gerente', label: 'Gerente'},
                {value: 'contabilidade', label: 'Contabilidade'},
                {value: 'usuario', label: 'Usuário'}
            ]);
            break;

        case 'departamentos':
            html += criarCampo('Código', 'text', 'codigo', 'Ex: dep0000');
            html += criarCampo('Nome', 'text', 'nome', '');
            html += criarCampoBuscaAninhada('Gerente', 'gerente_id', 'usuarios', 'nomeGerenteDpto');
            break;

        case 'fornecedores':
            html += criarCampo('Código', 'text', 'codigo', 'Ex: forn000000');
            html += criarCampo('Razão Social', 'text', 'razao_social', '');
            html += criarCampo('Nome Fantasia', 'text', 'nome_fantasia', '');
            html += criarCampo('CNPJ', 'text', 'cnpj', 'Ex: 12.345.678/0001-90');
            html += criarCampoBuscaAninhada('Categoria', 'categoria_id', 'categorias', 'nomeCategoriaForn');
            break;

        case 'produtos':
            html += criarCampo('Código', 'text', 'codigo', 'Ex: prod000000');
            html += criarCampo('Nome', 'text', 'nome', '');
            html += criarCampoBuscaAninhada('Categoria', 'categoria_id', 'categorias', 'nomeCategoriaProd');
            break;

        case 'categorias':
            html += criarCampo('Código', 'text', 'codigo', '');
            html += criarCampo('Nome', 'text', 'nome', '');
            break;

        case 'condicoes_pagamento':
            html += criarCampo('Código', 'text', 'codigo', '');
            html += criarCampo('Descrição', 'text', 'descricao', '');
            break;

        case 'cotacoes':
            html += criarCampo('Número', 'text', 'numero', 'Ex: cot00000');
            html += criarSelect('Status', 'status', [
                {value: '', label: 'Todos'},
                {value: 'aberta', label: 'Aberta'},
                {value: 'fechada', label: 'Fechada'},
                {value: 'concluida', label: 'Concluída'},
                {value: 'cancelada', label: 'Cancelada'}
            ]);
            html += criarCampoBuscaAninhada('Solicitação', 'solicitacao_id', 'solicitacoes', 'numSolicitacaoCot');
            html += criarCampoBuscaAninhada('Usuário', 'usuario_id', 'usuarios', 'nomeUsuarioCot');
            break;
    }

    html += '</div>';
    return html;
}

Scopi.iconeBusca = function(tabela, idCampoCodigo, idCampoNome, filtrosExtras) {
    buscaGlobalAtual = { tabela, idCodigo: idCampoCodigo, idNome: idCampoNome, filtros: { status: 'ativo' }, isAninhada: false };

    // Configurar titulo
    const titulos = {
        'usuarios': 'Usuário',
        'departamentos': 'Departamento',
        'categorias': 'Categoria',
        'produtos': 'Produto',
        'fornecedores': 'Fornecedor',
        'ordens': 'Ordem de Compra',
        'cotacoes': 'Cotação',
        'condicoes_pagamento': 'Condição de Pagamento'
    };
    document.getElementById('buscaGlobalEntidadeNome').textContent = titulos[tabela] || 'Registro';

    // Limpar busca anterior
    document.getElementById('inputBuscaGlobal').value = '';
    document.getElementById('tbodyBuscaGlobal').innerHTML = '<tr><td colspan="5" style="text-align:center;color:#888;">Digite um termo e clique em buscar.</td></tr>';

    // Gerar filtros contextuais
    const filtrosDiv = document.getElementById('filtrosBuscaGlobal');
    filtrosDiv.innerHTML = gerarFiltrosBuscaGlobal(tabela);
    filtrosDiv.style.display = 'block';

    // Configurar cabeçalho da tabela dinamicamente
    let htmlHead = '<tr>';
    if(tabela === 'usuarios') { htmlHead += '<th>Matrícula</th><th>Nome</th><th>Departamento</th>'; }
    else if(tabela === 'produtos') { htmlHead += '<th>Código</th><th>Nome</th><th>Categoria</th>'; }
    else if(tabela === 'fornecedores') { htmlHead += '<th>Código</th><th>Razão Social</th><th>Nome Fantasia</th><th>CNPJ</th><th>Localidade</th>'; }
    else if(tabela === 'ordens' || tabela === 'cotacoes') { htmlHead += '<th>Número</th><th>Status</th>' + (tabela==='ordens'?'<th>Fornecedor</th>':''); }
    else { htmlHead += '<th>Código</th><th>Descrição</th>'; }
    htmlHead += '<th style="width:80px;"></th></tr>';
    document.getElementById('theadBuscaGlobal').innerHTML = htmlHead;

    Scopi.abrirModal('modalBuscaGlobal');
    setTimeout(() => document.getElementById('inputBuscaGlobal').focus(), 100);
};

Scopi.iconeBuscaAninhada = function(tabela, idCampoCodigo, idCampoVisualizacao) {
    if (buscaGlobalAtual.tabela) pilhaBuscasAninhadas.push({...buscaGlobalAtual});
    buscaGlobalAtual = { tabela, idCodigo: idCampoCodigo, idNome: idCampoVisualizacao, filtros: { status: 'ativo' }, isAninhada: true };
    document.getElementById('btnVoltarBusca').style.display = pilhaBuscasAninhadas.length > 0 ? 'flex' : 'none';

    const titulos = {
        'usuarios': 'Usuário',
        'departamentos': 'Departamento',
        'categorias': 'Categoria',
        'produtos': 'Produto',
        'fornecedores': 'Fornecedor',
        'condicoes_pagamento': 'Condição de Pagamento'
    };
    document.getElementById('buscaGlobalEntidadeNome').textContent = titulos[tabela] || 'Registro';

    document.getElementById('inputBuscaGlobal').value = '';
    document.getElementById('tbodyBuscaGlobal').innerHTML = '<tr><td colspan="5" style="text-align:center;color:#888;">Digite um termo e clique em buscar.</td></tr>';

    const filtrosDiv = document.getElementById('filtrosBuscaGlobal');
    filtrosDiv.innerHTML = gerarFiltrosBuscaGlobal(tabela);
    filtrosDiv.style.display = 'block';

    let htmlHead = '<tr>';
    if(tabela === 'usuarios') { htmlHead += '<th>Matrícula</th><th>Nome</th><th>Departamento</th>'; }
    else if(tabela === 'produtos') { htmlHead += '<th>Código</th><th>Nome</th><th>Categoria</th>'; }
    else if(tabela === 'fornecedores') { htmlHead += '<th>Código</th><th>Razão Social</th><th>Nome Fantasia</th><th>CNPJ</th>'; }
    else { htmlHead += '<th>Código</th><th>Descrição</th>'; }
    htmlHead += '<th style="width:80px;"></th></tr>';
    document.getElementById('theadBuscaGlobal').innerHTML = htmlHead;

    Scopi.abrirModal('modalBuscaGlobal');
    setTimeout(() => document.getElementById('inputBuscaGlobal').focus(), 100);
};

Scopi.voltarBuscaAninhada = function() {
    if (pilhaBuscasAninhadas.length === 0) return;

    const buscaAnterior = pilhaBuscasAninhadas.pop();
    buscaGlobalAtual = buscaAnterior;

    const titulos = {
        'usuarios': 'Usuário',
        'departamentos': 'Departamento',
        'categorias': 'Categoria',
        'produtos': 'Produto',
        'fornecedores': 'Fornecedor',
        'condicoes_pagamento': 'Condição de Pagamento'
    };
    document.getElementById('buscaGlobalEntidadeNome').textContent = titulos[buscaAnterior.tabela] || 'Registro';

    document.getElementById('inputBuscaGlobal').value = '';
    document.getElementById('tbodyBuscaGlobal').innerHTML = '<tr><td colspan="5" style="text-align:center;color:#888;">Digite um termo e clique em buscar.</td></tr>';

    const filtrosDiv = document.getElementById('filtrosBuscaGlobal');
    filtrosDiv.innerHTML = gerarFiltrosBuscaGlobal(buscaAnterior.tabela);
    filtrosDiv.style.display = 'block';

    let htmlHead = '<tr>';
    const tabela = buscaAnterior.tabela;
    if(tabela === 'usuarios') { htmlHead += '<th>Matrícula</th><th>Nome</th><th>Departamento</th>'; }
    else if(tabela === 'produtos') { htmlHead += '<th>Código</th><th>Nome</th><th>Categoria</th>'; }
    else if(tabela === 'fornecedores') { htmlHead += '<th>Código</th><th>Razão Social</th><th>Nome Fantasia</th><th>CNPJ</th>'; }
    else { htmlHead += '<th>Código</th><th>Descrição</th>'; }
    htmlHead += '<th style="width:80px;"></th></tr>';
    document.getElementById('theadBuscaGlobal').innerHTML = htmlHead;

    // Atualiza visibilidade do botão voltar
    document.getElementById('btnVoltarBusca').style.display = pilhaBuscasAninhadas.length > 0 ? 'flex' : 'none';

    setTimeout(() => document.getElementById('inputBuscaGlobal').focus(), 100);
};

Scopi.executarBuscaGlobal = async function() {
    const termo = document.getElementById('inputBuscaGlobal').value.trim();
    if (termo.length < 2 && termo !== '') {
        alert('Digite pelo menos 2 caracteres para buscar.');
        return;
    }

    const tbody = document.getElementById('tbodyBuscaGlobal');
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Buscando...</td></tr>';

    try {
        // Construir URL com TODOS os filtros
        let url = '/busca-global/dados?tabela=' + buscaGlobalAtual.tabela + '&termo=' + encodeURIComponent(termo);

        // Adicionar todos os filtros presentes no objeto
        Object.keys(buscaGlobalAtual.filtros).forEach(key => {
            const valor = buscaGlobalAtual.filtros[key];
            if (valor && valor !== '') {
                url += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(valor);
            }
        });

        const resp = await fetch(Scopi.url(url), {credentials:'include'});
        const res = await resp.json();

        if (!res.sucesso) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:red;">' + res.mensagem + '</td></tr>';
            return;
        }

        if (res.dados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#888;">Nenhum resultado encontrado.</td></tr>';
            return;
        }

        let html = '';
        res.dados.forEach(d => {
            html += '<tr>';
            html += '<td>' + (d.identificador||'') + '</td>';
            html += '<td>' + (d.descricao||'') + '</td>';

            if(buscaGlobalAtual.tabela === 'usuarios' || buscaGlobalAtual.tabela === 'produtos') {
                html += '<td>' + (d.extra1||'') + '</td>';
            } else if (buscaGlobalAtual.tabela === 'fornecedores') {
                html += '<td>' + (d.extra1||'') + '</td>';
                html += '<td>' + (d.extra2||'') + '</td>';
            } else if (buscaGlobalAtual.tabela === 'ordens') {
                html += '<td>' + (d.extra1||'') + '</td>';
            }

            html += '<td><button class="btn btn-primario" style="padding:4px 8px;font-size:0.75rem;" onclick="Scopi.selecionarBuscaGlobal(\'' + d.identificador + '\', \'' + d.descricao.replace(/'/g, "\\'") + '\')">Selecionar</button></td>';
            html += '</tr>';
        });
        tbody.innerHTML = html;

    } catch(e) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:red;">Erro de conexão.</td></tr>';
    }
};

Scopi.selecionarBuscaGlobal = function(codigo, nome) {
    const inputCod = document.getElementById(buscaGlobalAtual.idCodigo);
    if(inputCod) {
        inputCod.value = codigo;
        inputCod.dispatchEvent(new Event('blur'));
        inputCod.dispatchEvent(new Event('change'));
    }

    if(buscaGlobalAtual.idNome) {
        const elNome = document.getElementById(buscaGlobalAtual.idNome);
        if(elNome) {
            if(elNome.tagName === 'INPUT') {
                elNome.value = nome;
            } else {
                elNome.textContent = nome;
                elNome.style.display = 'inline';
            }
        }
    }

    Scopi.fecharModal('modalBuscaGlobal');
};

Scopi.abrirHistorico = async function(entidade, entidadeId, tituloAmigavel = 'Histórico') {
    document.getElementById('historicoGlobalTitulo').textContent = `Histórico de Cadastro de ${tituloAmigavel}`;
    const timeline = document.getElementById('timelineHistorico');
    const vazio = document.getElementById('historicoVazio');
    
    timeline.innerHTML = '<div style="padding: 20px;">Carregando...</div>';
    vazio.style.display = 'none';
    
    Scopi.abrirModal('modalHistoricoGlobal');
    
    try {
        const resp = await fetch(Scopi.url('/historico/dados?entidade=' + entidade + '&entidade_id=' + entidadeId), {credentials:'include'});
        const res = await resp.json();
        
        if(!res.sucesso || !res.dados || res.dados.length === 0) {
            timeline.innerHTML = '';
            vazio.style.display = 'block';
            return;
        }
        
        let html = '';
        res.dados.forEach(h => {
            let acaoMsg = h.acao || 'Atualização';
            
            html += '<div style="margin-bottom: 20px; position: relative;">';
            html += '<div style="position: absolute; left: -26px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: var(--primaria); border: 2px solid #fff;"></div>';
            html += '<div style="font-weight: 600; color: var(--texto-principal); margin-bottom: 2px;">' + acaoMsg + '</div>';
            html += '<div style="font-size: 0.8rem; color: var(--texto-secundario); margin-bottom: 6px;">';
            html += '<span>' + h.data_hora_formatada + '</span> • <span>Por: ' + (h.nome_usuario||'Sistema') + '</span>';
            html += '</div>';
            
            if(h.detalhes_html) { html += '<div style="font-size: 0.8rem; color: #555; margin-top: 4px; line-height: 1.4;">' + h.detalhes_html + '</div>'; }
            
            html += '</div>';
        });
        
        timeline.innerHTML = html;
        
    } catch(e) {
        timeline.innerHTML = '';
        vazio.style.display = 'block';
        vazio.textContent = 'Erro ao carregar histórico.';
    }
};
