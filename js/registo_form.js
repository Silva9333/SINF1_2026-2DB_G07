// ============================================================
// js/registo_form.js
// ============================================================
// Validação do lado do cliente para o formulário de registo.
// Verifica se as duas passwords coincidem antes de submeter.
//
// Utilizado em: registo.php
//
// NOTA: Esta validação é apenas para usabilidade (feedback
// imediato ao utilizador). A validação definitiva acontece
// sempre no servidor, na BusinessLogicLayer.php.
//
// Elementos necessários no HTML:
//   form             — formulário de registo (o primeiro da página)
//   #password        — campo da password
//   #confirmar       — campo de confirmação da password
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    var form      = document.querySelector('form');
    var password  = document.getElementById('password');
    var confirmar = document.getElementById('confirmar');

    // Interceptar o envio do formulário para validar primeiro
    form.addEventListener('submit', function (e) {
        if (password.value !== confirmar.value) {
            e.preventDefault(); // Impede o envio do formulário
            alert('As passwords não coincidem! Por favor verifique.');
            confirmar.focus();  // Colocar o cursor no campo de confirmação
        }
    });

});
