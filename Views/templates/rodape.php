  </main><!-- /pagina -->
</div><!-- /conteudo -->
</div><!-- /layout -->

<script>const SCOPI_BASE = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>/public/assets/js/scopi.js"></script>
<script>
// RF15: Carregar contagem de notificações não lidas
(function(){
  function atualizarBadge() {
    fetch(SCOPI_BASE + '/notificacoes/contar', {headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(r => r.json())
      .then(j => {
        const badge = document.getElementById('badgeNotif');
        if (badge && j.sucesso) {
          if (j.total > 0) {
            badge.textContent = j.total > 99 ? '99+' : j.total;
            badge.style.display = '';
          } else {
            badge.style.display = 'none';
          }
        }
      }).catch(() => {});
  }
  atualizarBadge();
  setInterval(atualizarBadge, 60000); // Atualizar a cada 60 segundos
})();
</script>
</body>
</html>

