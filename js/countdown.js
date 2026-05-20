// ============================================================
// js/countdown.js
// ============================================================
// Countdown timer para a Queima das Fitas 2026.
// Atualiza os elementos HTML a cada segundo com os dias,
// horas, minutos e segundos que faltam para o início.
//
// Utilizado em: index.php
// Elementos necessários no HTML:
// #cd-dias, #cd-horas, #cd-min, #cd-seg, #countdown
// ============================================================

// Data e hora de início da Queima das Fitas 2026
const dataQueima = new Date('2026-05-11T21:30:00');

// Formata um número com dois dígitos (ex: 7 → "07")
const pad = n => String(Math.floor(n)).padStart(2, '0');

function atualizarCountdown() {
    const diff = dataQueima - new Date(); // diferença em milissegundos

    // Se a data já passou, mostrar mensagem de início
    if (diff <= 0) {
        document.getElementById('countdown').innerHTML =
            '<span style="font-size:1.5rem;">🎉 A Queima das Fitas já começou!</span>';
        return;
    }

    // Converter milissegundos em dias, horas, minutos e segundos
    document.getElementById('cd-dias').textContent  = pad(diff / 86400000);
    document.getElementById('cd-horas').textContent = pad((diff % 86400000) / 3600000);
    document.getElementById('cd-min').textContent   = pad((diff % 3600000) / 60000);
    document.getElementById('cd-seg').textContent   = pad((diff % 60000) / 1000);
}

// Executar imediatamente e repetir a cada 1 segundo (1000ms)
atualizarCountdown();
setInterval(atualizarCountdown, 1000);
