/**
 * Modal de exclusão genérico: funciona para clientes, usuários e enfermeiros.
 * O botão "Excluir" deve ter data-id="<id>" e data-nome="<texto opcional>".
 */
$('#delete-modal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  var id = button.data('id');
  var modal = $(this);

  modal.find('#confirm').attr('href', 'delete.php?id=' + id);
});

/* Preview de imagem: só roda se o campo existir na página */
var imagemNova = document.getElementById('imagemnova');
if (imagemNova) {
  imagemNova.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => document.getElementById('preview').src = ev.target.result;
    reader.readAsDataURL(file);
  });
}

/* Máscara de data dd/mm/aaaa: só roda se o campo existir */
var campoData = document.getElementById('datanasc');
if (campoData) {
  campoData.addEventListener('input', function (e) {
    let v = e.target.value.replace(/\D/g, '').slice(0, 8);
    if (v.length >= 5) {
      v = v.slice(0, 2) + '/' + v.slice(2, 4) + '/' + v.slice(4);
    } else if (v.length >= 3) {
      v = v.slice(0, 2) + '/' + v.slice(2);
    }
    e.target.value = v;
  });
}