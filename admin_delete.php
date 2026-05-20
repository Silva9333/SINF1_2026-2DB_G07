<?php
// ============================================================
// admin_delete.php - ELIMINAR ENTIDADES (Eventos/Artistas/Barracas/Faculdades)
// ============================================================
// Esta página processa a eliminação de qualquer entidade.
// Recebe: ?tipo=evento&id=X  (ou artista, barraca, faculdade)
// Apenas administradores podem aceder.
// Não tem interface visual - apenas processa e redireciona.
// ============================================================
require_once 'BusinessLogicLayer.php';
requireAdmin();

$tipo = $_GET['tipo'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

// Mapa de tipos para funções e destinos de redirecionamento
$mapa = [
    'evento'    => ['processDeleteEvento',    'eventos.php'],
    'artista'   => ['processDeleteArtista',   'artistas.php'],
    'barraca'   => ['processDeleteBarraca',   'barracas.php'],
    'faculdade' => ['processDeleteFaculdade', 'faculdades.php'],
];

if (!isset($mapa[$tipo]) || $id <= 0) {
    header('Location: admin.php');
    exit;
}

// Chamar a função de eliminação correta
$funcao   = $mapa[$tipo][0];
$destino  = $mapa[$tipo][1];
$resultado = $funcao($id); // Chama processDeleteEvento($id), etc.

// Redirecionar para a listagem com mensagem na sessão
session_start();
$_SESSION['flash_msg']  = ($resultado['sucesso'] ? '✅ ' : '❌ ') . $resultado['mensagem'];
$_SESSION['flash_tipo'] = $resultado['sucesso'] ? 'success' : 'error';

header("Location: $destino");
exit;
?>
