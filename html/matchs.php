<?php
session_start();
require_once '../config/conex.php';

// Previne cache do navegador para evitar retorno por histórico pós-logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verifica se está logado
if (!isset($_SESSION['usuarios_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = $_SESSION['usuarios_id'];

// Busca os matches do usuário logado
$sql = "SELECT u.id, u.nome, u.foto, u.descricao, u.contato 
        FROM matches m 
        JOIN usuarios u ON (u.id = m.usuario1 OR u.id = m.usuario2) 
        WHERE (m.usuario1 = :myid OR m.usuario2 = :myid) AND u.id != :myid";
$stmt = $pdo->prepare($sql);
$stmt->execute(['myid' => $currentUserId]);
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($matches as &$m) {
    $sqlJ = "SELECT g.name as nome, g.image as foto FROM usuario_top_jogos utg JOIN games g ON utg.jogo_id = g.id WHERE utg.usuario_id = ? LIMIT 3";
    $stmtJ = $pdo->prepare($sqlJ);
    $stmtJ->execute([$m['id']]);
    $m['jogos'] = $stmtJ->fetchAll(PDO::FETCH_ASSOC);
}
unset($m); // Evita bug de referência pendente ao último elemento

// Passa os dados do PHP para o JavaScript em formato JSON
$matchesJson = json_encode($matches);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../fotos/favicon.ico" type="image/x-icon">
    <title>GameConneCt</title>
</head>

<body>
    <div class="ContainerMt">
        <div class="menuMt">

            <div class="TopoMt">
                <div class="NomeMt">GameConneCt</div>

                <div class="topoBtnsMt">
                    <a href="perfil.php" class="perfilBtn" title="Ver seu Perfil">Perfil</a>
                    <a href="menu.php" class="matchBtn" title="Voltar ao Feed">Buscar</a>
                </div>
            </div>

            <div id="notificationContainer" style="padding: 0 20px;"></div>

            <div class="ConteudoMt">

                <div class="ladoEsquerdoMt">
                    <!-- Preenchido dinamicamente pelo JS ao renderizar o Match -->
                </div>

                <div class="perfilU">
                    <div id="matchCard" style="width: 100%; flex: 1; min-height: 0; display:flex; flex-direction:column;">
                        <!-- Conteúdo preenchido pelo JS -->
                        <div class="infoPerfil">
                            <h2>Seus Matches aparecerão aqui</h2>
                            <p>Continue no Feed para encontrar pessoas.</p>
                        </div>
                    </div>

                    <div class="AcoesMT" style="display:none; align-items:center;" id="acoesMatch">
                        <button onclick="anterior()" style="background:#2a2a2a; color:white; border:none; cursor:pointer;" onmouseover="this.style.transform='scale(1.1)'; this.style.background='#444';" onmouseout="this.style.transform='scale(1)'; this.style.background='#2a2a2a';" title="Anterior">⬅️</button>
                        <button class="SimMt" onclick="document.getElementById('chatModal').style.display='flex'" title="Chat">💬</button>
                        <button onclick="proximo()" style="background:#2a2a2a; color:white; border:none; cursor:pointer;" onmouseover="this.style.transform='scale(1.1)'; this.style.background='#444';" onmouseout="this.style.transform='scale(1)'; this.style.background='#2a2a2a';" title="Próximo">➡️</button>
                    </div>
                </div>

                <!-- Modal de Chat Indisponível -->
                <div id="chatModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; justify-content:center; align-items:center;">
                    <div style="background:#1a1a1a; padding:30px; border-radius:15px; text-align:center; border: 2px solid #00c50a;">
                        <h2 style="color:white; margin-top:0;">Chat</h2>
                        <p style="color:#ddd; margin: 20px 0;">O chat não está disponível no momento.</p>
                        <button onclick="document.getElementById('chatModal').style.display='none'" style="padding:10px 20px; background:#00c50a; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold;">Fechar</button>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script>
        const matches = <?php echo $matchesJson; ?>;
        let indexAtual = 0;

        function mostrarNotificacao(mensagem, tipo) {
            const container = document.getElementById('notificationContainer');
            const div = document.createElement('div');
            const cores = { success: '#00c50a', warning: '#e0a800', error: '#d10000' };
            div.style.cssText = `background:${cores[tipo] || '#333'};color:#fff;padding:12px 18px;border-radius:8px;margin-bottom:8px;font-weight:600;animation:messageIn 0.3s ease-out;`;
            div.textContent = mensagem;
            container.appendChild(div);
            setTimeout(() => div.remove(), 4000);
        }

        const cardContainer = document.getElementById('matchCard');
        const acoesContainer = document.getElementById('acoesMatch');

        function renderMatch() {
            if (matches.length > 0) {
                acoesContainer.style.display = 'flex';

                const match = matches[indexAtual];
                const fotoHtml = match.foto
                    ? '<img src="../uploads/' + match.foto + '" style="width: 100%; height: 100%; object-fit: cover;" alt="Foto de perfil">'
                    : '<div style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; background: #333; color: #777;">Sem Foto</div>';

                const descHtml = match.descricao ? match.descricao.replace(/\n/g, "<br>") : "Nenhuma descrição.";
                const contatoInfo = match.contato 
                    ? ('<div style="margin-top:12px;"><span style="background:rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); color:white; padding:6px 12px; border-radius:8px; font-weight:bold; font-size:14px; text-shadow:none; backdrop-filter: blur(4px);">' + match.contato + '</span></div>') 
                    : ('<div style="margin-top:12px;"><span style="background:rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); color:white; padding:6px 12px; border-radius:8px; font-weight:bold; font-size:14px; text-shadow:none; backdrop-filter: blur(4px);">Contato: Não informado</span></div>');

                cardContainer.innerHTML = 
                    '<div style="width: 100%; flex: 1; min-height: 0; border-radius: 12px; overflow: hidden; margin-bottom: 15px; background: #1a1a1a; position: relative;">' +
                        fotoHtml +
                        '<div class="infoPerfil" style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px; box-sizing: border-box; background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.7) 60%, transparent 100%); color: white; transition: all 0.4s ease; text-shadow: 1px 1px 3px rgba(0,0,0,0.8);">' +
                            '<h2 style="margin: 0; font-size: 28px;">' + match.nome + '</h2>' +
                            '<p style="margin: 5px 0 0 0; font-size: 16px; opacity: 0.9; line-height: 1.4;">' + descHtml + '</p>' +
                            contatoInfo +
                            '<p style="font-size: 12px; margin-top: 15px; color: #ccc;"><i>Match ' + (indexAtual + 1) + ' de ' + matches.length + '</i></p>' +
                        '</div>' +
                    '</div>';

                // Atualizar barra da esquerda com os jogos do match
                let sidebarContainer = document.querySelector('.ladoEsquerdoMt');
                let hud = `<h3 style="color:white; text-align:center; padding:10px 0;">Jogos de ${match.nome.split(' ')[0]}</h3>`;
                match.jogos.forEach(j => {
                    hud += `<div class="Jogo" style="background-image: url('${j.foto}'); background-size: cover; background-position: center; display:flex; align-items:flex-end; border-radius:12px; height:120px;">
                                <span style="background: rgba(0,0,0,0.8); color: white; padding: 5px; width: 100%; text-align: center; font-size: 14px; border-radius: 0 0 10px 10px;">${j.nome}</span>
                            </div>`;
                });
                for(let i = match.jogos.length; i < 3; i++) {
                    hud += `<div class="Jogo vazio" style="display:flex; justify-content:center; align-items:center; color: rgba(255,255,255,0.1); background: transparent; border: 2px dashed rgba(255,255,255,0.1); border-radius: 12px; height: 120px;"></div>`;
                }
                sidebarContainer.innerHTML = hud;

            } else {
                cardContainer.innerHTML = '<h2>Nenhum Match ainda :(</h2><p>Continue interagindo no Feed para encontrar pessoas!</p>';
                document.querySelector('.ladoEsquerdoMt').innerHTML = `<div class="Jogo vazio" style="display:flex; justify-content:center; align-items:center; color: rgba(255,255,255,0.1); background: transparent; border: 2px dashed rgba(255,255,255,0.1); border-radius: 12px; height: 120px;">Vazio</div>`;
            }
        }

        function anterior() {
            if (matches.length > 0) {
                indexAtual = (indexAtual - 1 + matches.length) % matches.length;
                renderMatch();
            }
        }

        function proximo() {
            if (matches.length > 0) {
                indexAtual = (indexAtual + 1) % matches.length;
                renderMatch();
            }
        }

        // Renderiza inicialmente
        renderMatch();
    </script>
</body>

</html>