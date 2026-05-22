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

<div class="grade-graficos">

  <!-- Gráfico 1: Solicitações por status -->
  <div class="card-grafico">
    <h3>Solicitações por Status</h3>
    <canvas id="graficoSolicitacoes" height="220"></canvas>
  </div>

  <!-- Gráfico 2: Cotações por mês -->
  <div class="card-grafico">
    <h3>Cotações nos Últimos 6 Meses</h3>
    <canvas id="graficoCotacoes" height="220"></canvas>
  </div>

  <!-- Gráfico 3: Ordens por status -->
  <div class="card-grafico">
    <h3>Ordens de Compra por Status</h3>
    <canvas id="graficoOrdens" height="220"></canvas>
  </div>

  <!-- Gráfico 4: Valor de notas por mês -->
  <div class="card-grafico">
    <h3>Notas Fiscais — Valor Total por Mês</h3>
    <canvas id="graficoNotas" height="220"></canvas>
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
    type: 'pie',
    data: {
      labels,
      datasets: [{
        data: valores,
        backgroundColor: [CORES.roxo, CORES.medio, CORES.magenta, CORES.verde, CORES.azul, CORES.cinza],
        borderWidth: 2,
        borderColor: '#fff',
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom', labels: { padding: 14, font: { size: 12 } } }
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
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#EDE7F6' }, ticks: { precision: 0 } },
        x: { grid: { display: false } }
      }
    }
  });
})();

/* ── Gráfico 3: Rosca — Ordens por status ── */
(function() {
  const { labels, valores } = extrairStatus(dadosOrdens);
  new Chart(document.getElementById('graficoOrdens'), {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data: valores,
        backgroundColor: [CORES.escuro, CORES.medio, CORES.roxo, CORES.magenta, CORES.verde, CORES.azul, CORES.cinza],
        borderWidth: 2,
        borderColor: '#fff',
      }]
    },
    options: {
      responsive: true,
      cutout: '60%',
      plugins: { legend: { position: 'bottom', labels: { padding: 14, font: { size: 12 } } } }
    }
  });
})();

/* ── Gráfico 4: Linha — Valor de notas por mês ── */
(function() {
  const { labels, valores } = extrairValores(dadosNotas);
  new Chart(document.getElementById('graficoNotas'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'R$ Total',
        data: valores,
        borderColor: CORES.roxo,
        backgroundColor: CORES.roxo + '22',
        fill: true,
        tension: .4,
        pointBackgroundColor: CORES.roxo,
        pointRadius: 5,
        pointHoverRadius: 7,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: '#EDE7F6' },
          ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR') }
        },
        x: { grid: { display: false } }
      }
    }
  });
})();
</script>
