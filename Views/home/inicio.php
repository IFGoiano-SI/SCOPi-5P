<?php
/**
 * inicio.php
 * Dashboard principal do SCOPi com 4 gráficos:
 * 1. Solicitações por status (pizza)
 * 2. Cotações por mês (barras)
 * 3. Ordens de compra por status (rosca)
 * 4. Valor de notas fiscais por mês (linha)
 */
?>
<script>document.getElementById('topbarTitulo').textContent = 'Página Inicial';</script>

<div class="pagina-cabecalho">
  <h1 class="pagina-titulo">Painel de Controle</h1>
  <p class="pagina-subtitulo">Visão geral das operações do SCOPi</p>
</div>

<style>
.grade-graficos {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    gap: 16px;
    height: calc(100vh - 210px);
    overflow: hidden;
}
.card-grafico {
    display: flex;
    flex-direction: column;
    padding: 16px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    height: 100%;
}
.card-grafico h3 {
    margin: 0 0 12px 0;
    font-size: 1rem;
    color: var(--texto-primario);
}
.chart-container {
    flex: 1;
    min-height: 0;
    position: relative;
}
</style>

<div class="grade-graficos">

  <!-- Gráfico 1: Solicitações por status -->
  <div class="card-grafico">
    <h3>Solicitações por Status</h3>
    <div class="chart-container"><canvas id="graficoSolicitacoes"></canvas></div>
  </div>

  <!-- Gráfico 2: Cotações por mês -->
  <div class="card-grafico">
    <h3>Cotações nos Últimos 6 Meses</h3>
    <div class="chart-container"><canvas id="graficoCotacoes"></canvas></div>
  </div>

  <!-- Gráfico 3: Ordens por status -->
  <div class="card-grafico">
    <h3>Ordens de Compra por Status</h3>
    <div class="chart-container"><canvas id="graficoOrdens"></canvas></div>
  </div>

  <!-- Gráfico 4: Valor de notas por mês -->
  <div class="card-grafico">
    <h3>Notas Fiscais — Valor Total por Mês</h3>
    <div class="chart-container"><canvas id="graficoNotas"></canvas></div>
  </div>

</div>

<!-- Dados PHP → JS -->
<script>
const dadosSolicitacoes = <?= json_encode($dadosSolicitacoes ?? []) ?>;
const dadosCotacoes     = <?= json_encode($dadosCotacoes     ?? []) ?>;
const dadosOrdens       = <?= json_encode($dadosOrdens       ?? []) ?>;
const dadosNotas        = <?= json_encode($dadosNotas        ?? []) ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
/* ── Paleta SCOPi ── */
const CORES = {
  roxo:    '#9133D2',
  medio:   '#510B76',
  escuro:  '#23003C',
  magenta: '#C2185B',
  verde:   '#2E7D32',
  azul:    '#1565C0',
  laranja: '#E65100',
  cinza:   '#616161',
};

Chart.defaults.font.family = "'Sora', sans-serif";
Chart.defaults.color = '#1A0029';

/* Helper: extrai labels e valores de array [{status, total}] */
function extrairStatus(arr) {
  return {
    labels: arr.map(r => traduzirStatus(r.status || r.mes)),
    valores: arr.map(r => Number(r.total)),
  };
}
function extrairValores(arr) {
  return {
    labels: arr.map(r => r.mes),
    valores: arr.map(r => Number(r.total)),
  };
}

function traduzirStatus(s) {
  const mapa = {
    em_aberto: 'Em Aberto', autorizada: 'Autorizada', em_cotacao: 'Em Cotação',
    recusada: 'Recusada', cancelada: 'Cancelada', concluida: 'Concluída',
    aberta: 'Aberta', fechada: 'Fechada', enviada: 'Enviada',
    parcialmente_atendida: 'Parc. Atendida',
  };
  return mapa[s] || s;
}

/* ── Gráfico 1: Pizza — Solicitações por status ── */
(function() {
  const { labels, valores } = extrairStatus(dadosSolicitacoes);
  new Chart(document.getElementById('graficoSolicitacoes'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Solicitações',
        data: valores,
        backgroundColor: [CORES.roxo + 'CC', CORES.medio + 'CC', CORES.magenta + 'CC', CORES.verde + 'CC', CORES.azul + 'CC', CORES.cinza + 'CC'],
        borderColor: [CORES.roxo, CORES.medio, CORES.magenta, CORES.verde, CORES.azul, CORES.cinza],
        borderWidth: 1.5,
        borderRadius: 4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#EDE7F6' }, ticks: { precision: 0 } },
        x: { grid: { display: false } }
      }
    }
  });
})();

/* ── Gráfico 2: Barras — Cotações por mês ── */
(function() {
  const { labels, valores } = extrairValores(dadosCotacoes);
  new Chart(document.getElementById('graficoCotacoes'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Cotações',
        data: valores,
        backgroundColor: CORES.roxo + 'CC',
        borderColor: CORES.roxo,
        borderWidth: 1.5,
        borderRadius: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#EDE7F6' }, ticks: { precision: 0 } },
        x: { grid: { display: false } }
      }
    }
  });
})();

/* ── Gráfico 3: Ordens por status ── */
(function() {
  const { labels, valores } = extrairStatus(dadosOrdens);
  new Chart(document.getElementById('graficoOrdens'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Ordens',
        data: valores,
        backgroundColor: [CORES.laranja + 'CC', CORES.azul + 'CC', CORES.verde + 'CC', CORES.cinza + 'CC'],
        borderColor: [CORES.laranja, CORES.azul, CORES.verde, CORES.cinza],
        borderWidth: 1.5,
        borderRadius: 4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#EDE7F6' }, ticks: { precision: 0 } },
        x: { grid: { display: false } }
      }
    }
  });
})();

/* ── Gráfico 4: Valor de notas por mês ── */
(function() {
  const { labels, valores } = extrairValores(dadosNotas);
  new Chart(document.getElementById('graficoNotas'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Valor Total (R$)',
        data: valores,
        backgroundColor: CORES.verde + 'CC',
        borderColor: CORES.verde,
        borderWidth: 1.5,
        borderRadius: 4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: function(c){ return 'R$ ' + c.raw.toLocaleString('pt-BR',{minimumFractionDigits:2}); } } }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: '#EDE7F6' },
          ticks: { callback: function(v){ return 'R$ ' + v.toLocaleString('pt-BR'); } }
        },
        x: { grid: { display: false } }
      }
    }
  });
})();
</script>
