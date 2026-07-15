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

// Busca o gênero e preferência e plataforma do usuário logado
$stmtUserGender = $pdo->prepare("SELECT genero, genero_preferencia, plataforma FROM usuarios WHERE id = ?");
$stmtUserGender->execute([$currentUserId]);
$userGenderInfo = $stmtUserGender->fetch();
$userGender = $userGenderInfo['genero'] ?? null;
$userGenderPref = $userGenderInfo['genero_preferencia'] ?? null;
$userPlataforma = $userGenderInfo['plataforma'] ?? null;

// Função helper para criar a cláusula WHERE de filtro de gênero
function buildGenderFilterClause($userGender, $userPref) {
    if ($userPref === 'homem') {
        return "AND genero = 'masculino'";
    } elseif ($userPref === 'mulher') {
        return "AND genero = 'feminino'";
    }
    return ""; // Se preferência for 'ambos' ou não definida, não filtra
}

$genderFilter = buildGenderFilterClause($userGender, $userGenderPref);

// Busca jogos do usuário logado
$stmtGames = $pdo->prepare("SELECT jogo_id FROM usuario_top_jogos WHERE usuario_id = ?");
$stmtGames->execute([$currentUserId]);
$myGamesArr = $stmtGames->fetchAll(PDO::FETCH_COLUMN);

// Busca gêneros dos jogos que o usuário logado gosta
$stmtGenres = $pdo->prepare("SELECT g.genres FROM usuario_top_jogos utg JOIN games g ON utg.jogo_id = g.id WHERE utg.usuario_id = ?");
$stmtGenres->execute([$currentUserId]);
$myGenresArr = [];
while ($row = $stmtGenres->fetch()) {
    $parts = array_filter(array_map('trim', explode(',', $row['genres'])));
    $myGenresArr = array_merge($myGenresArr, $parts);
}

$perfilFeed = false;

// Se o usuário tem jogos, gêneros preferidos ou plataforma, tenta encontrar alguém com os mesmos gostos/console
if (!empty($myGenresArr) || !empty($myGamesArr) || !empty($userPlataforma)) {
    $conditions = [];
    $params = [
        'currentId' => $currentUserId, 
        'currentId2' => $currentUserId
    ];

    if (!empty($myGamesArr)) {
        $inQuery = implode(',', array_map('intval', $myGamesArr));
        $conditions[] = "utg.jogo_id IN ($inQuery)";
    }
    
    if (!empty($myGenresArr)) {
        // Escapa os termos para usar com REGEXP no MySQL (ex: 'Action|RPG|Shooter')
        $regexParts = array_map(function($g) use ($pdo) { return preg_quote($g); }, $myGenresArr);
        $regexStr = implode('|', $regexParts);
        $conditions[] = "g.genres REGEXP :regex";
        $params['regex'] = $regexStr;
    }

    if (!empty($userPlataforma)) {
        $conditions[] = "u.plataforma = :plataforma";
        $params['plataforma'] = $userPlataforma;
    }

    $conditionStr = implode(' OR ', $conditions);

    $sqlTarget = "SELECT DISTINCT u.id, u.nome, u.descricao, u.foto 
                  FROM usuarios u
                  LEFT JOIN usuario_top_jogos utg ON u.id = utg.usuario_id
                  LEFT JOIN games g ON utg.jogo_id = g.id
                  WHERE u.id != :currentId 
                  AND u.id NOT IN (SELECT usuario_destino FROM curtidas WHERE usuario_origem = :currentId2)
                  AND ($conditionStr)
                  $genderFilter
                  LIMIT 1";
                  
    $stmtT = $pdo->prepare($sqlTarget);
    $stmtT->execute($params);
    $perfilFeed = $stmtT->fetch();
}

// Fallback: se não encontrou por gênero, ou se o usuário não tem jogos, busca qualquer um
if (!$perfilFeed) {
    $sqlFallback = "SELECT id, nome, descricao, foto FROM usuarios
            WHERE id != :currentId 
            AND id NOT IN (SELECT usuario_destino FROM curtidas WHERE usuario_origem = :currentId2)
            $genderFilter
            LIMIT 1";
    $stmtF = $pdo->prepare($sqlFallback);
    $stmtF->execute(['currentId' => $currentUserId, 'currentId2' => $currentUserId]);
    $perfilFeed = $stmtF->fetch();
}

$jogosCard = [];
if ($perfilFeed) {
    $sqlJogosCard = "SELECT g.name as nome, g.image as foto FROM usuario_top_jogos utg JOIN games g ON utg.jogo_id = g.id WHERE utg.usuario_id = ? LIMIT 3";
    $stmtJC = $pdo->prepare($sqlJogosCard);
    $stmtJC->execute([$perfilFeed['id']]);
    $jogosCard = $stmtJC->fetchAll(PDO::FETCH_ASSOC);
}
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
    <div class="ContainerM">
        <div class="menuF">

            <div class="Topo">
                <div class="Nome">GameConneCt</div>

                <div class="topoBtns">
                    <a href="perfil.php" class="perfilBtn" title="Ver seu Perfil">Perfil</a>
                    <a href="matchs.php" class="matchBtn" title="Ver Matchs">Matchs</a>
                </div>
            </div>

            <div class="Conteudo">

                <div class="ladoEsquerdo">
                    <?php if ($perfilFeed): ?>
                        <h3 style="color:white; text-align:center; padding:10px 0;">Top Jogos</h3>
                        <?php foreach($jogosCard as $jc): ?>
                            <div class="Jogo" style="background-image: url('<?php echo htmlspecialchars($jc['foto']); ?>'); background-size: cover; background-position: center; border-radius: 12px; height: 120px; display:flex; align-items:flex-end;">
                                <span style="background: rgba(0,0,0,0.8); color: white; padding: 5px; width: 100%; text-align: center; font-size: 14px; border-radius: 0 0 10px 10px;"><?php echo htmlspecialchars($jc['nome']); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php for($i=count($jogosCard); $i<3; $i++): ?>
                             <div class="Jogo vazio" style="display:flex; justify-content:center; align-items:center; color: rgba(255,255,255,0.1); background: transparent; border: 2px dashed rgba(255,255,255,0.1); border-radius: 12px; height: 120px;"></div>
                        <?php endfor; ?>
                    <?php else: ?>
                        <div class="Jogo vazio" style="display:flex; justify-content:center; align-items:center; color: rgba(255,255,255,0.1); background: transparent; border: 2px dashed rgba(255,255,255,0.1); border-radius: 12px; height: 120px;">Vazio</div>
                    <?php endif; ?>
                </div>

                <div class="perfilU">
                    <?php if ($perfilFeed): ?>
                        <div style="width: 100%; flex: 1; min-height: 0; border-radius: 12px; overflow: hidden; margin-bottom: 15px; background: #1a1a1a; position: relative;">
                            <?php if (!empty($perfilFeed['foto'])): ?>
                                <img id="perfilImg" src="../uploads/<?php echo htmlspecialchars($perfilFeed['foto']); ?>"
                                    style="width: 100%; height: 100%; object-fit: cover;" alt="Foto de perfil" onload="adaptTextColor(this)">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; background: #333; color: #777;">Sem Foto</div>
                            <?php endif; ?>
                            
                            <div id="infoOverlay" class="infoPerfil" style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px; box-sizing: border-box; background: linear-gradient(to top, rgba(0,0,0,0.6), transparent); color: white; transition: all 0.4s ease; text-shadow: 1px 1px 3px rgba(0,0,0,0.8);">
                                <h2 style="margin: 0; font-size: 28px;"><?php echo htmlspecialchars($perfilFeed['nome']); ?></h2>
                                <p style="margin: 5px 0 0 0; font-size: 16px; opacity: 0.9; line-height: 1.4;"><?php echo !empty($perfilFeed['descricao']) ? nl2br(htmlspecialchars($perfilFeed['descricao'])) : "Nenhuma descrição..."; ?></p>
                            </div>
                        </div>

                        <script>
                            function adaptTextColor(imgEl) {
                                const canvas = document.createElement('canvas');
                                canvas.width = imgEl.naturalWidth;
                                canvas.height = imgEl.naturalHeight;
                                const ctx = canvas.getContext('2d');
                                
                                try {
                                    // Desenha a imagem no canvas virtual para análise
                                    ctx.drawImage(imgEl, 0, 0);

                                    // Analisa os últimos 25% da imagem (onde o texto fica posicionado)
                                    const startY = Math.floor(canvas.height * 0.75);
                                    const heightToAnalyze = canvas.height - startY;

                                    if (heightToAnalyze <= 0 || canvas.width <= 0) return;

                                    const imgData = ctx.getImageData(0, startY, canvas.width, heightToAnalyze);
                                    const data = imgData.data;

                                    let r = 0, g = 0, b = 0;
                                    let count = 0;

                                    // Pula os pixels a cada 4 (R, G, B, Alpha)
                                    // Para melhorar performance, verificamos 1 pixel a cada 10 para ter uma média.
                                    for (let i = 0; i < data.length; i += 40) {
                                        r += data[i];
                                        g += data[i+1];
                                        b += data[i+2];
                                        count++;
                                    }

                                    r = Math.floor(r / count);
                                    g = Math.floor(g / count);
                                    b = Math.floor(b / count);

                                    // Fórmula de Luminância de Perceção (Rec. 709)
                                    const luminance = (0.299 * r + 0.587 * g + 0.114 * b);

                                    const overlay = document.getElementById('infoOverlay');
                                    
                                    // Se a média da área for muito brilhante/clara
                                    if (luminance > 140) {
                                        overlay.style.color = '#111';
                                        overlay.style.textShadow = '0px 0px 5px rgba(255,255,255,0.7), 1px 1px 2px rgba(255,255,255,0.9)';
                                        overlay.style.background = 'linear-gradient(to top, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 100%)';
                                    } else {
                                        overlay.style.color = '#fff';
                                        overlay.style.textShadow = '0px 0px 5px rgba(0,0,0,0.7), 1px 1px 3px rgba(0,0,0,0.9)';
                                        overlay.style.background = 'linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%)';
                                    }
                                } catch (e) {
                                    console.log("Erro de Canvas (CORS) em imagem local: ", e);
                                }
                            }
                        </script>

                        <div class="Acoes">
                                <!-- Formulário para Não (Pass) -->
                            <form action="../process/like_action.php" method="POST" style="display:inline;">
                                <input type="hidden" name="usuarios_to" value="<?php echo htmlspecialchars($perfilFeed['id']); ?>">
                                <input type="hidden" name="action" value="pass">
                                <button type="submit" class="Nao">❌</button>
                            </form>

                            <!-- Formulário para Sim (Like) -->
                            <form action="../process/like_action.php" method="POST" style="display:inline;">
                                <input type="hidden" name="usuarios_to" value="<?php echo htmlspecialchars($perfilFeed['id']); ?>">
                                <input type="hidden" name="action" value="like">
                                <button type="submit" class="Sim">❤️</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="infoPerfil">
                            <h2>Fim da linha!</h2>
                            <p>Não há mais usuários para mostrar no momento. Volte mais tarde ou tente ajustar seus jogos.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </div>
</body>

</html>