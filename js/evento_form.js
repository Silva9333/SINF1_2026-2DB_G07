// ============================================================
// js/evento_form.js
// ============================================================
// Lógica do formulário de criação/edição de eventos.
// Mostra ou esconde a secção de artistas consoante o tipo
// de evento selecionado (só concertos têm artistas).
//
// Utilizado em: admin_evento_form.php
//
// Elementos necessários no HTML:
//   #tipoSelect      — dropdown de tipo de evento
//   #artistasSection — div com a seleção de artistas
// ============================================================

/**
 * Mostra a secção de artistas apenas se o tipo for "concerto".
 * Chamada quando o utilizador muda o dropdown de tipo.
 */
function mostrarArtistas() {
    var tipo = document.getElementById('tipoSelect').value;
    var secção = document.getElementById('artistasSection');

    // Só concertos têm artistas associados
    secção.style.display = (tipo === 'concerto') ? 'block' : 'none';
}

// Ao carregar a página, verificar o tipo já selecionado
// (importante no modo de edição, onde o tipo já está preenchido)
document.addEventListener('DOMContentLoaded', mostrarArtistas);
