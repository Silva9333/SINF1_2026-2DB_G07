// ============================================================
// js/estrelas.js
// ============================================================
// Sistema interativo de seleção de estrelas para avaliação.
// Utilizado em: evento_detalhe.php, barraca_detalhe.php
//
// Como funciona:
//   - O HTML tem 5 botões .star-btn com data-valor="1" a "5"
//   - Um campo hidden (#notaInput) guarda a nota selecionada
//   - Ao clicar numa estrela, colore todas até ao valor clicado
//
// Elementos necessários no HTML:
//   .star-btn[data-valor]  — botões das estrelas
//   #notaInput             — campo hidden com o valor da nota
// ============================================================

/**
 * Seleciona uma nota de 1 a 5 estrelas.
 * Atualiza o campo hidden e a aparência visual das estrelas.
 *
 * @param {number} valor - Nota selecionada (1 a 5)
 */
function selecionarEstrela(valor) {
    // Guardar a nota no campo hidden do formulário
    document.getElementById('notaInput').value = valor;

    // Percorrer todos os botões de estrela e colorir os selecionados
    document.querySelectorAll('.star-btn').forEach(function(btn) {
        // classList.toggle(classe, condicao): adiciona se true, remove se false
        btn.classList.toggle('ativo', parseInt(btn.dataset.valor) <= valor);
    });
}
