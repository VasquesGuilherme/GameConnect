<?php
session_start();
// Previne cache do navegador para evitar retorno por histórico pós-logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verifica se está logado
if (!isset($_SESSION['usuarios_id'])) {
    header("Location: login.php");
    exit();
}
include_once '../process/perfil_action.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../fotos/favicon.ico">
    <title>GameConneCt - Meu Perfil</title>
</head>

<body>

    <div class="ContainerPerfil">
        <div class="menuPerfil">

            <div class="TopoPerfil">
                <h1 class="Nome">GameConneCt</h1>
                <div class="topoBtnsPerfil" style="display:flex; align-items:center; gap: 15px;">
                    <a href="menu.php" class="inicioBtn" title="Buscar">Buscar</a>
                    <a href="matchs.php" class="matchBtn" title="Matchs">Matchs</a>
                    <a href="#" class="sairBtn" title="Sair" onclick="abrirModalLogout(); return false;">Sair</a>
                </div>
            </div>

            <div id="notificationContainer" style="padding: 0 20px;"></div>

            <div class="ConteudoPerfil">

                <div class="ladoEsquerdoPerfil" id="jogosContainer">
                    <h3 style="color:white; text-align:center; margin-bottom: 20px;">Seu Top Jogos</h3>
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <?php if (isset($jogos[$i])): ?>
                            <div class="Jogo"
                                style="background-image: url('<?php echo htmlspecialchars($jogos[$i]['foto']); ?>'); background-size: cover; background-position: center; cursor: pointer; display:flex; align-items:flex-end;"
                                onclick="removerJogo(<?php echo intval($jogos[$i]['id']); ?>)" title="Clique para remover">
                                <span
                                    style="background: rgba(0,0,0,0.8); color: white; padding: 5px; border-radius: 0 0 10px 10px; width: 100%; text-align: center; font-size: 14px;"><?php echo htmlspecialchars($jogos[$i]['nome']); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="Jogo vazio" onclick="abrirModalRAWG()"
                                style="cursor:pointer; display:flex; justify-content:center; align-items:center; font-size:40px; color: rgba(255,255,255,0.3); background: transparent; border: 2px dashed rgba(255,255,255,0.3); border-radius: 12px; transition: all 0.3s ease;"
                                onmouseover="this.style.borderColor='#a70000'; this.style.color='#a70000'; this.style.transform='scale(1.05)';"
                                onmouseout="this.style.borderColor='rgba(255,255,255,0.3)'; this.style.color='rgba(255,255,255,0.3)'; this.style.transform='scale(1)';"
                                title="Adicionar Jogo">+</div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <div class="detalhesUsuario">


                    <div class="fotoUsuarioBox">
                        <label id="uploadLabel" style="cursor:not-allowed;" title="Clique em editar para alterar foto">
                            <div style="width: 200px; height: 200px; border-radius: 12px; overflow: hidden; border: 2px solid #a70000; box-shadow: 0 0 15px rgba(167,0,0,0.3); margin-right: 20px; transition: 0.3s; display:flex; justify-content:center; align-items:center; background:#1a1a1a;"
                                id="previewFoto" title="Clique em editar para alterar foto">
                                <?php if (!empty($fotoUsuario)): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($fotoUsuario); ?>"
                                        style="width:100%;height:100%;object-fit:cover;">
                                <?php else: ?>
                                    <span style="font-size:50px; color:#a70000; font-weight:bold;">+</span>
                                <?php endif; ?>
                            </div>
                        </label>

                        <input type="file" name="foto" id="uploadFoto" style="display:none;"
                            onchange="previewImage(event)" disabled>
                    </div>

                    <div class="infoUsuarioBox">

                        <!-- NOME -->
                        <div class="cabecalhoInfoPerfil">
                            <h2 id="nomeView"><?php echo htmlspecialchars($nomeUsuario); ?></h2>

                            <input type="text" id="nomeEdit" value="<?php echo htmlspecialchars($nomeUsuario); ?>"
                                style="display:none; font-size:24px; font-weight:bold; margin-bottom:15px; width:100%; background:#333; color:white; border:none; padding:10px; border-radius:8px; box-sizing:border-box; outline:none;"
                                placeholder="Seu Nome">

                            <button id="btnEditar" onclick="toggleEdit()"
                                style="background:transparent; border:1px solid #a70000; color:white; padding:5px 10px; border-radius:5px; cursor:pointer; font-size:14px; transition:0.3s;"
                                onmouseover="this.style.background='#a70000'"
                                onmouseout="this.style.background='transparent'">✏️ Editar</button>
                        </div>

                        <!-- VISUAL -->
                        <div id="viewMode">
                            <p><strong>Idade:</strong>
                                <span
                                    id="idadeView"><?php echo $idade ? htmlspecialchars($idade) . ' anos' : 'Não informada'; ?></span>
                            </p>

                            <p><strong>Contato:</strong>
                                <span id="contatoView"><?php echo htmlspecialchars($contatoUsuario ?? ''); ?></span>
                            </p>

                            <p><strong>Plataforma Principal:</strong>
                                <span
                                    id="plataformaView"><?php echo !empty($plataformaUsuario) ? htmlspecialchars($plataformaUsuario) : 'Não informada'; ?></span>
                            </p>

                            <p><strong>Descrição:</strong></p>
                            <p id="descView"><?php echo htmlspecialchars($descricaoUsuario ?? ''); ?></p>
                        </div>

                        <!-- EDIÇÃO -->
                        <div id="editMode" style="display:none;">
                            <div class="editFormCard">

                                <div class="editFormRow">
                                    <div class="editFormGroup">
                                        <label class="editLabel">🎮 Plataforma Principal</label>
                                        <select id="plataformaEdit" required class="editSelect">
                                            <option value="" disabled <?php echo empty($plataformaUsuario) ? 'selected' : ''; ?>>Selecione...</option>
                                            <option value="PC" <?php echo ($plataformaUsuario ?? '') === 'PC' ? 'selected' : ''; ?>>Computador (PC)</option>
                                            <option value="Xbox" <?php echo ($plataformaUsuario ?? '') === 'Xbox' ? 'selected' : ''; ?>>Xbox</option>
                                            <option value="PlayStation" <?php echo ($plataformaUsuario ?? '') === 'PlayStation' ? 'selected' : ''; ?>>PlayStation</option>
                                            <option value="Android" <?php echo ($plataformaUsuario ?? '') === 'Android' ? 'selected' : ''; ?>>Android</option>
                                            <option value="iOS" <?php echo ($plataformaUsuario ?? '') === 'iOS' ? 'selected' : ''; ?>>iOS</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="editFormGroup">
                                    <label class="editLabel">🕹️ Contato</label>
                                    <div class="editContatoRow">
                                        <select id="tipoContatoEdit" onchange="mudarPlaceholderContato()" class="editSelectSmall">
                                            <option value="steam" <?php echo strpos($contatoUsuario ?? '', 'Steam') !== false ? 'selected' : ''; ?>>Steam</option>
                                            <option value="psn" <?php echo strpos($contatoUsuario ?? '', 'PSN') !== false ? 'selected' : ''; ?>>PSN</option>
                                            <option value="xbox" <?php echo strpos($contatoUsuario ?? '', 'Xbox') !== false ? 'selected' : ''; ?>>Xbox</option>
                                        </select>
                                        <input type="text" id="contatoEdit"
                                            value="<?php echo htmlspecialchars(preg_replace('/^(Steam:|PSN:|Xbox:)\s*/', '', $contatoUsuario ?? '')); ?>"
                                            placeholder="Seu nome de usuário" required
                                            class="editInput editInputFlex">
                                    </div>
                                </div>

                                <div class="infoMessagePermanent" style="background:#111; border:1px solid #a70000; color:#fff; padding:16px 18px; border-radius:12px; margin-bottom:20px; font-size:14px;">
                                    <strong>Aviso obrigatório:</strong> todos os campos desta área devem ser preenchidos para salvar seu perfil corretamente. Preencha tudo e clique em <strong>Salvar Alterações</strong>.
                                </div>

                                <div class="editFormGroup">
                                    <label class="editLabel">📝 Sobre você</label>
                                    <textarea id="descEdit" placeholder="Escreva um pouco sobre você..." required
                                        class="editTextarea" rows="3"><?php echo htmlspecialchars($descricaoUsuario ?? ''); ?></textarea>
                                </div>

                                <div class="editFormActions">
                                    <button type="button" onclick="salvarEdit()" class="btnSalvarEdit">💾 Salvar Alterações</button>
                                    <button type="button" onclick="abrirModalExcluirConta()" class="btnExcluirEdit">🗑️ Excluir Conta</button>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal do RAWG -->
    <div id="modalRawg"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; justify-content:center; align-items:center;">
        <div style="background:#222; padding: 20px; border-radius: 10px; width: 400px; max-width: 90%; color: white;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px;">
                <h3 style="margin:0;">Buscar Jogo (RAWG)</h3>
                <button onclick="fecharModalRAWG()"
                    style="background:transparent; border:none; color:white; font-size:20px; cursor:pointer;"
                    id="btnCloseModal">✖</button>
            </div>
            <input type="text" id="rawgSearchInput" placeholder="Digite o nome do jogo..."
                oninput="debouncedBuscarJogosRawg()"
                style="width: 100%; padding: 10px; border-radius: 5px; border:none; margin-bottom: 10px; box-sizing: border-box;">
            <button onclick="buscarJogosRawg()"
                style="width:100%; padding: 10px; background: #a70000; color: white; border:none; border-radius: 5px; cursor:pointer; font-weight:bold;">Buscar</button>
            <div id="rawgResults" style="margin-top: 15px; max-height: 300px; overflow-y: auto;"></div>
        </div>
    </div>

    <script>
        // ============================================
        // SISTEMA DE NOTIFICAÇÕES PADRONIZADO
        // ============================================
        function mostrarNotificacao(mensagem, tipo = 'error', duracao = 5000) {
            const container = document.getElementById('notificationContainer');
            if (!container) return;

            const div = document.createElement('div');
            div.className = `${tipo}Message show`;
            div.style.marginBottom = '10px';
            div.textContent = mensagem;
            div.setAttribute('role', 'alert');

            container.appendChild(div);

            if (duracao > 0) {
                setTimeout(() => {
                    div.classList.remove('show');
                    setTimeout(() => div.remove(), 300);
                }, duracao);
            }
        }

        function limparNotificacoes() {
            const container = document.getElementById('notificationContainer');
            if (container) {
                container.innerHTML = '';
            }
        }

        function mudarPlaceholderContato() {
            let tipo = document.getElementById('tipoContatoEdit').value;
            let inputContato = document.getElementById('contatoEdit');
            const placeholders = {
                steam: 'Digite seu nome de usuário da Steam',
                psn:   'Digite seu nome de usuário da PSN',
                xbox:  'Digite seu Gamertag do Xbox'
            };
            inputContato.placeholder = placeholders[tipo] || 'Digite seu nome de usuário';
        }

        // Sem restrição de caracteres para nomes de conta

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function () {
                document.getElementById('previewFoto').innerHTML =
                    '<img src="' + reader.result + '" style="width:100%;height:100%;border-radius:12px;object-fit:cover;">';
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        function toggleEdit() {
            document.getElementById('viewMode').style.display = 'none';
            document.getElementById('editMode').style.display = 'block';
            document.getElementById('nomeView').style.display = 'none';
            document.getElementById('nomeEdit').style.display = 'block';
            document.getElementById('btnEditar').style.display = 'none'; // Some o botão editar enquanto edita
            document.getElementById('uploadLabel').style.cursor = 'pointer';
            document.getElementById('uploadLabel').title = 'Clique para alterar foto';
            document.getElementById('previewFoto').title = 'Clique para alterar foto';
            document.getElementById('previewFoto').style.border = '2px dashed #a70000';
            document.getElementById('uploadFoto').disabled = false;
            mudarPlaceholderContato();
        }

        function salvarEdit() {
            const nomeInput = document.getElementById('nomeEdit');
            const contatoInput = document.getElementById('contatoEdit');
            const descInput = document.getElementById('descEdit');
            const plataformaInput = document.getElementById('plataformaEdit');

            let nomeVal = nomeInput.value.trim();
            let tipoContato = document.getElementById('tipoContatoEdit').value;
            let contatoRaw = contatoInput.value.trim();
            let descVal = descInput.value.trim();
            let platformVal = plataformaInput.value;

            const inputsToClear = [nomeInput, contatoInput, descInput, plataformaInput];
            inputsToClear.forEach(input => input.classList.remove('inputError'));

            let missingFields = [];

            if (!nomeVal) {
                missingFields.push('Nome');
                nomeInput.classList.add('inputError');
            }
            if (!contatoRaw) {
                missingFields.push('Contato');
                contatoInput.classList.add('inputError');
            }
            if (!descVal) {
                missingFields.push('Descrição');
                descInput.classList.add('inputError');
            }
            if (!platformVal) {
                missingFields.push('Plataforma');
                plataformaInput.classList.add('inputError');
            }

            if (missingFields.length > 0) {
                mostrarNotificacao('Preencha os campos: ' + missingFields.join(', '), 'error', 7000);
                return;
            }

            const prefixos = { steam: 'Steam', psn: 'PSN', xbox: 'Xbox' };
            let contatoFinal = (prefixos[tipoContato] || tipoContato) + ': ' + contatoRaw;

            let formData = new FormData();
            formData.append('nome', nomeVal);
            formData.append('contato', contatoFinal);
            formData.append('descricao', descVal);
            formData.append('plataforma', platformVal);

            let fileInput = document.getElementById('uploadFoto');
            if (!fileInput.disabled && fileInput.files.length > 0) {
                formData.append('foto', fileInput.files[0]);
            }

            fetch('../process/perfil_action.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('nomeView').innerText = nomeVal;
                        document.getElementById('contatoView').innerText = contatoFinal;
                        document.getElementById('descView').innerText = descVal;
                        document.getElementById('plataformaView').innerText = platformVal;

                        if (data.fotoAtualizada && data.novaFoto) {
                            document.getElementById('previewFoto').innerHTML = '<img src="../uploads/' + data.novaFoto + '" style="width:100%;height:100%;border-radius:12px;object-fit:cover;">';
                        }

                        document.getElementById('viewMode').style.display = 'block';
                        document.getElementById('editMode').style.display = 'none';
                        document.getElementById('nomeView').style.display = 'block';
                        document.getElementById('nomeEdit').style.display = 'none';
                        document.getElementById('btnEditar').style.display = 'block'; // Volta mostrar o botão de editar
                        document.getElementById('uploadLabel').style.cursor = 'not-allowed';
                        document.getElementById('uploadLabel').title = 'Clique em editar para alterar foto';
                        document.getElementById('previewFoto').title = 'Clique em editar para alterar foto';
                        document.getElementById('previewFoto').style.border = '2px solid #a70000';
                        document.getElementById('uploadFoto').disabled = true;

                        mostrarNotificacao('Perfil atualizado com sucesso!', 'success');
                    } else {
                        mostrarNotificacao('Verifique seus dados e tente novamente', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarNotificacao('Verifique seus dados e tente novamente', 'error');
                });
        }



        // Script JS original e novo do RAWG

        let numJogosUser = <?php echo count($jogos); ?>;



        function abrirModalRAWG(forced = false) {
            document.getElementById('modalRawg').style.display = 'flex';
            if (forced) {
                document.getElementById('btnCloseModal').style.display = 'none';
                mostrarNotificacao("Selecione pelo menos 1 jogo", 'error');
            } else {
                document.getElementById('btnCloseModal').style.display = 'block';
            }
        }

        function fecharModalRAWG() {
            if (numJogosUser === 0) return; // Nao deixa fechar se tiver 0
            document.getElementById('modalRawg').style.display = 'none';
        }

        function getPlatformRawgId(platName) {
            switch (platName) {
                case 'PC': return '1';
                case 'PlayStation': return '2';
                case 'Xbox': return '3';
                case 'iOS': return '4';
                case 'Android': return '8';
                default: return '';
            }
        }

        let searchTimeout;

        function debouncedBuscarJogosRawg() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(buscarJogosRawg, 500);
        }

        function debouncedBuscarObRawg() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(buscarObRawg, 500);
        }

        function buscarJogosRawg() {
            let query = document.getElementById('rawgSearchInput').value.trim();
            if (!query) {
                document.getElementById('rawgResults').innerHTML = '';
                return;
            }

            let platSelected = document.getElementById('plataformaEdit').value;
            let platParam = getPlatformRawgId(platSelected);
            let platQuery = platParam ? `&parent_platforms=${platParam}` : '';

            let resultsDiv = document.getElementById('rawgResults');
            resultsDiv.innerHTML = '<p style="text-align:center;">Buscando...</p>';

            fetch(`https://api.rawg.io/api/games?key=9ded2b73f0bf43478084f3b3389f15a7&search=${encodeURIComponent(query)}&search_precise=true&exclude_additions=true&ordering=-added&page_size=8${platQuery}`)
                .then(res => res.json())
                .then(data => {
                    resultsDiv.innerHTML = '';
                    if (data.results && data.results.length > 0) {
                        data.results.forEach(game => {
                            let genres = game.genres.map(g => g.name).join(', ');
                            let releaseDb = game.released || 'Desconhecida';
                            let gameDiv = document.createElement('div');
                            gameDiv.style = "display:flex; align-items:center; background:#333; margin-bottom:10px; padding:10px; border-radius:5px; cursor:pointer;";
                            gameDiv.innerHTML = `
                            <img src="${game.background_image || '../fotos/default_game.png'}" style="width:50px; height:50px; object-fit:cover; border-radius:5px; margin-right:10px;">
                            <div>
                                <h4 style="margin:0; font-size:14px; color: white;">${game.name}</h4>
                                <p style="margin:0; font-size:11px; color:#aaa;">Lançamento: ${releaseDb}</p>
                                <p style="margin:0; font-size:11px; color:#aaa;">Gêneros: ${genres}</p>
                            </div>
                        `;
                            gameDiv.onclick = () => salvarJogoRawg(game.id, game.name, game.background_image, genres);
                            resultsDiv.appendChild(gameDiv);
                        });
                    } else {
                        resultsDiv.innerHTML = '<p style="text-align:center;">Nenhum jogo encontrado.</p>';
                    }
                })
                .catch(err => {
                    resultsDiv.innerHTML = '<p style="text-align:center;color:red;">Erro na busca.</p>';
                });
        }

        function salvarJogoRawg(id, name, image, genres) {
            fetch('../process/save_game_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ rawg_id: id, name: name, image: image, genres: genres })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        mostrarNotificacao("Jogo adicionado!", 'success');
                        window.location.reload();
                    } else {
                        mostrarNotificacao('Verifique seus dados e tente novamente', 'error');
                    }
                })
                .catch(err => mostrarNotificacao('Verifique seus dados e tente novamente', 'error'));
        }

        function removerJogo(id) {
            if (confirm("Deseja remover este jogo do seu perfil?")) {
                fetch('../process/remove_game_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ jogo_id: id })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            mostrarNotificacao('Verifique seus dados e tente novamente', 'error');
                        }
                    });
            }
        }

        function abrirModalLogout() {
            document.getElementById('modalLogout').style.display = 'flex';
        }

        function fecharModalLogout() {
            document.getElementById('modalLogout').style.display = 'none';
        }

        function abrirModalExcluirConta() {
            document.getElementById('confirmarExcluirInput').value = '';
            document.getElementById('btnConfirmarExcluir').disabled = true;
            document.getElementById('btnConfirmarExcluir').style.opacity = '0.4';
            document.getElementById('modalExcluirConta').style.display = 'flex';
        }

        function fecharModalExcluirConta() {
            document.getElementById('modalExcluirConta').style.display = 'none';
        }

        function verificarTextoExcluir() {
            const val = document.getElementById('confirmarExcluirInput').value;
            const btn = document.getElementById('btnConfirmarExcluir');
            if (val === 'EXCLUIR') {
                btn.disabled = false;
                btn.style.opacity = '1';
            } else {
                btn.disabled = true;
                btn.style.opacity = '0.4';
            }
        }

        async function confirmarExcluirConta() {
            const btn = document.getElementById('btnConfirmarExcluir');
            btn.textContent = 'Excluindo...';
            btn.disabled = true;

            try {
                const res = await fetch('../process/delete_account_action.php', { method: 'POST' });
                const data = await res.json();
                if (data.success) {
                    window.location.href = '../html/inicio.php';
                } else {
                    mostrarNotificacao('Verifique seus dados e tente novamente', 'error');
                    btn.textContent = '🗑️ Confirmar Exclusão';
                    btn.disabled = false;
                    btn.style.opacity = '1';
                }
            } catch (e) {
                mostrarNotificacao('Verifique seus dados e tente novamente', 'error');
                btn.textContent = '🗑️ Confirmar Exclusão';
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        }
    </script>

    <!-- MODAL DE CONFIRMAÇÃO DE LOGOUT -->
    <div id="modalLogout"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.75); z-index:999999; justify-content:center; align-items:center;">
        <div
            style="background:#1a1a1a; border:2px solid #a70000; border-radius:16px; padding:35px 30px; max-width:380px; width:90%; text-align:center; color:white; box-shadow: 0 0 30px rgba(167,0,0,0.4); animation: fadeInScale 0.2s ease;">
            <div style="font-size:48px; margin-bottom:12px;">🚪</div>
            <h2 style="margin:0 0 10px 0; font-size:22px; color:#fff;">Sair da conta?</h2>
            <p style="color:#aaa; font-size:14px; margin-bottom:28px;">Tem certeza que deseja encerrar sua sessão?</p>
            <div style="display:flex; gap:12px; justify-content:center;">
                <button onclick="fecharModalLogout()"
                    style="flex:1; padding:12px; background:transparent; border:1px solid #555; color:#ccc; border-radius:8px; cursor:pointer; font-size:15px; transition:0.2s;"
                    onmouseover="this.style.borderColor='#888'; this.style.color='#fff';"
                    onmouseout="this.style.borderColor='#555'; this.style.color='#ccc';">Cancelar</button>
                <a href="../process/logout_action.php" style="flex:1; text-decoration:none;">
                    <button
                        style="width:100%; padding:12px; background:#a70000; border:none; color:white; border-radius:8px; cursor:pointer; font-size:15px; font-weight:bold; transition:0.2s;"
                        onmouseover="this.style.background='#d10000'" onmouseout="this.style.background='#a70000'">Sim,
                        sair</button>
                </a>
            </div>
        </div>
    </div>

    <style>
        .editInput.inputError,
        .editSelect.inputError,
        .editTextarea.inputError {
            border: 2px solid #ff3333 !important;
            box-shadow: 0 0 12px rgba(255, 0, 0, 0.4);
        }

        .infoMessagePermanent {
            background: #111;
            border: 1px solid #a70000;
            color: #fff;
        }

        .warningMessage {
            color: #fff3cd;
            background: #664400;
            border: 1px solid #ffb300;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
            animation: slideDown 0.3s ease-out;
            position: relative;
            z-index: 10;
        }

        .warningMessage.show {
            display: block;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.85);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>

    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO DE CONTA -->
    <div id="modalExcluirConta"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:999999; justify-content:center; align-items:center;">
        <div
            style="background:#1a1a1a; border:2px solid #ff4444; border-radius:16px; padding:35px 30px; max-width:400px; width:90%; text-align:center; color:white; box-shadow: 0 0 40px rgba(255,68,68,0.35); animation: fadeInScale 0.2s ease;">
            <div style="font-size:48px; margin-bottom:12px;">⚠️</div>
            <h2 style="margin:0 0 8px 0; font-size:22px; color:#ff4444;">Excluir Conta?</h2>
            <p style="color:#aaa; font-size:13px; margin-bottom:8px; line-height:1.5;">Esta ação é <strong
                    style="color:#ff4444;">irreversível</strong>. Todos os seus dados, fotos, matches e jogos serão
                permanentemente deletados.</p>
            <p style="color:#ccc; font-size:13px; margin-bottom:16px;">Para confirmar, digite <strong
                    style="color:#ff4444; letter-spacing:2px;">EXCLUIR</strong> abaixo:</p>
            <input type="text" id="confirmarExcluirInput" oninput="verificarTextoExcluir()"
                placeholder="Digite EXCLUIR para confirmar"
                style="width:100%; padding:12px; border-radius:8px; border:1px solid #555; background:#2a2a2a; color:white; font-size:14px; text-align:center; box-sizing:border-box; margin-bottom:20px; outline:none; letter-spacing:1px;">
            <div style="display:flex; gap:12px; justify-content:center;">
                <button onclick="fecharModalExcluirConta()"
                    style="flex:1; padding:12px; background:transparent; border:1px solid #555; color:#ccc; border-radius:8px; cursor:pointer; font-size:15px; transition:0.2s;"
                    onmouseover="this.style.borderColor='#888'; this.style.color='#fff';"
                    onmouseout="this.style.borderColor='#555'; this.style.color='#ccc';">Cancelar</button>
                <button id="btnConfirmarExcluir" onclick="confirmarExcluirConta()" disabled
                    style="flex:1; padding:12px; background:#cc0000; border:none; color:white; border-radius:8px; cursor:pointer; font-size:15px; font-weight:bold; transition:0.2s; opacity:0.4;"
                    onmouseover="if(!this.disabled) this.style.background='#ff2222';"
                    onmouseout="if(!this.disabled) this.style.background='#cc0000';">🗑️ Confirmar Exclusão</button>
            </div>
        </div>
    </div>

    <!-- MODAL DE PRIMEIRO ACESSO / ONBOARDING -->
    <?php if (empty($fotoUsuario) || count($jogos) === 0 || empty($descricaoUsuario) || empty($plataformaUsuario) || empty($generoUsuario)): ?>
        <div id="modalOnboarding"
            style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.95); z-index:100000; display:flex; justify-content:center; align-items:flex-start; overflow-y:auto; padding: 20px;">
            <div
                style="background:#1a1a1a; padding:30px; border-radius:15px; width:100%; max-width:500px; color:white; border: 2px solid #a70000; box-shadow: 0 0 20px rgba(218, 32, 32, 0.3); margin-top:20px; margin-bottom:20px;">
                <h2 style="color:#a70000; text-align:center; margin-bottom:10px;">Completar Perfil</h2>
                <div style="background:#111; border:1px solid #a70000; color:#fff; padding:14px 16px; border-radius:12px; margin-bottom:18px; font-size:14px;">
                    <strong>Aviso:</strong> Obrigatorio o preenchimento de todos os campos para salvar o perfil corretamente.
                </div>
                <div id="obErroMsg" style="display:none; background:#d10000; color:white; padding:12px; border-radius:8px; margin-bottom:15px; font-size:14px; text-align:center; font-weight:bold; box-shadow: 0 0 10px rgba(209, 0, 0, 0.5);">
                </div>
                <p style="text-align:center; font-size:14px; color:#ccc; margin-bottom:20px;">Para aproveitar o GameConneCt,
                    preencha os seus dados abaixo, personalize seu perfil com uma foto e logo em seguida escolha os seus 3
                    jogos favoritos do momento para darmos <strong>Match</strong> com outras pessoas que jogam o mesmo que você!</p>

                <div style="text-align:center; margin-bottom:20px;">
                    <label for="obFotoInput" style="cursor:pointer; display:inline-block;">
                        <div id="obFotoPreview" title="Clique para adicionar foto"
                            style="width:150px; height:150px; border-radius:12px; border:2px dashed #a70000; display:flex; justify-content:center; align-items:center; background:#1a1a1a; overflow:hidden; transition:0.3s; box-shadow:0 0 15px rgba(167,0,0,0.2); margin:0 auto;">
                            <?php if (!empty($fotoUsuario)): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($fotoUsuario); ?>"
                                    style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <span style="font-size:40px; color:#a70000; font-weight:bold;">+</span>
                            <?php endif; ?>
                        </div>
                    </label>
                    <input type="file" name="foto" id="obFotoInput" style="display:none;" onchange="previewObImage(event)">
                    <p style="font-size:12px; color:#a70000; margin-top:5px;">Toque para enviar foto</p>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:#fff;">Seu Gênero:</label>
                    <select id="obGenero"
                        style="width:100%; padding:10px; border-radius:8px; border:none; background:#333; color:white; font-size:14px;">
                        <option value="" disabled <?php echo empty($generoUsuario) ? 'selected' : ''; ?>>Selecione...
                        </option>
                        <option value="masculino" <?php echo ($generoUsuario ?? '') === 'masculino' ? 'selected' : ''; ?>>
                            Masculino</option>
                        <option value="feminino" <?php echo ($generoUsuario ?? '') === 'feminino' ? 'selected' : ''; ?>>
                            Feminino</option>
                        <option value="nao_binario" <?php echo ($generoUsuario ?? '') === 'nao_binario' ? 'selected' : ''; ?>>
                            Não binário</option>
                        <option value="outro" <?php echo ($generoUsuario ?? '') === 'outro' ? 'selected' : ''; ?>>
                            Outro</option>
                    </select>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:#fff;">Console principal:</label>
                    <select id="obPlataforma"
                        style="width:100%; padding:10px; border-radius:8px; border:none; background:#333; color:white; font-size:14px;">
                        <option value="" disabled <?php echo empty($plataformaUsuario) ? 'selected' : ''; ?>>Selecione...
                        </option>
                        <option value="PC" <?php echo ($plataformaUsuario ?? '') === 'PC' ? 'selected' : ''; ?>>PC</option>
                        <option value="Xbox" <?php echo ($plataformaUsuario ?? '') === 'Xbox' ? 'selected' : ''; ?>>Xbox
                        </option>
                        <option value="PlayStation" <?php echo ($plataformaUsuario ?? '') === 'PlayStation' ? 'selected' : ''; ?>>PlayStation</option>
                        <option value="Android" <?php echo ($plataformaUsuario ?? '') === 'Android' ? 'selected' : ''; ?>>
                            Android</option>
                        <option value="iOS" <?php echo ($plataformaUsuario ?? '') === 'iOS' ? 'selected' : ''; ?>>iOS</option>
                    </select>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:#fff;">Sua Descrição:</label>
                    <textarea id="obDescricao" rows="3"
                        style="width:100%; padding:10px; border-radius:8px; border:none; background:#333; color:white; font-size:14px; resize:vertical; box-sizing:border-box;"
                        placeholder="Fale um pouco sobre você e seus estilos de jogos..."><?php echo htmlspecialchars($descricaoUsuario ?? ''); ?></textarea>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:#fff;">Contato:</label>
                    <div style="display:flex; gap:10px;">
                        <select id="obTipoContato" style="padding:10px; border-radius:8px; border:none; background:#333; color:white; font-size:14px; min-width:110px;">
                            <option value="steam">Steam</option>
                            <option value="psn">PSN</option>
                            <option value="xbox">Xbox</option>
                        </select>
                        <input type="text" id="obContato"
                            value="<?php echo htmlspecialchars(preg_replace('/^(Steam:|PSN:|Xbox:)\s*/', '', $contatoUsuario ?? '')); ?>"
                            style="flex:1; padding:10px; border-radius:8px; border:none; background:#333; color:white; font-size:14px; box-sizing:border-box;"
                            placeholder="Seu nome de usuário">
                    </div>
                </div>

                <hr style="border:1px solid #444; margin:20px 0;">

                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:#a70000;">Escolha 1 Jogo
                        Favorito:</label>
                    <?php if (count($jogos) > 0): ?>
                        <p style="color:#28a745; font-size:14px;">✅ Você já tem <?php echo count($jogos); ?> jogo(s)
                            selecionado(s)!</p>
                    <?php else: ?>
                        <div style="display:flex; gap:10px;">
                            <input type="text" id="obRawgInput"
                                style="flex:1; padding:10px; border-radius:8px; border:none; background:#333; color:white;"
                                placeholder="Digite o nome do jogo..." oninput="debouncedBuscarObRawg()">
                            <button onclick="buscarObRawg()"
                                style="padding:10px 15px; background:#a70000; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold;">🔍</button>
                        </div>
                        <div id="obRawgResults" style="margin-top:10px; max-height:150px; overflow-y:auto; border-radius:8px;">
                        </div>

                        <input type="hidden" id="obRawgId">
                        <input type="hidden" id="obRawgName">
                        <input type="hidden" id="obRawgImage">
                        <input type="hidden" id="obRawgGenres">

                        <div id="obSelectedGamePreview"
                            style="display:none; margin-top:10px; background:#222; padding:10px; border-radius:8px; border:1px solid #a70000; align-items:center; gap:10px;">
                        </div>
                    <?php endif; ?>
                </div>

                <button onclick="finalizarOnboarding(event)">Salvar Perfil</button>

                <style>
                    button {
                        width: 100%;
                        padding: 15px;
                        background: #a70000;
                        color: white;
                        font-weight: bold;
                        font-size: 16px;
                        border: none;
                        border-radius: 8px;
                        cursor: pointer;
                        margin-top: 10px;
                        text-transform: uppercase;
                        transition: 0.3s;
                    }

                    button:hover {
                        background: #d10000;
                    }

                    .fieldErrorBorder {
                        border: 2px solid #ff3333 !important;
                        box-shadow: 0 0 10px rgba(255, 50, 50, 0.35);
                    }
                </style>


            </div>
        </div>

        <div id="modalPreferencia"
            style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.95); z-index:100001; display:none; justify-content:center; align-items:center; padding: 20px;">
            <div
                style="background:#1a1a1a; padding:30px; border-radius:15px; width:100%; max-width:400px; color:white; border: 2px solid #a70000; box-shadow: 0 0 20px rgba(218, 32, 32, 0.3);">
                <h2 style="color:#a70000; text-align:center; margin-bottom:10px;">Quem você deseja encontrar?</h2>
                <p style="text-align:center; font-size:14px; color:#ccc; margin-bottom:20px;">Selecione uma ou mais opções.</p>
                <div style="margin-bottom:20px; display:flex; flex-direction:column; gap:10px; font-size:16px; color: white;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="pref" value="homem" style="width:20px; height:20px;"> Homem
                    </label>
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="pref" value="mulher" style="width:20px; height:20px;"> Mulher
                    </label>
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="pref" value="ambos" style="width:20px; height:20px;"> Geral (Ambos)
                    </label>
                </div>
                <button onclick="salvarPreferenciaEFinalizar()"
                    style="width:100%; background:#a70000; color:white; padding:12px; border:none; border-radius:8px; font-size:16px; font-weight:bold; cursor:pointer; box-shadow:0 0 10px rgba(167,0,0,0.5);">
                    Salvar e Continuar
                </button>
            </div>
        </div>

        <script>
            let hasGameSelected = <?php echo count($jogos) > 0 ? 'true' : 'false'; ?>;
            let hasFotoSelecionada = <?php echo !empty($fotoUsuario) ? 'true' : 'false'; ?>;

            function getPlatformRawgId(plat) {
                if(plat === 'PC') return 1;
                if(plat === 'PlayStation') return 2;
                if(plat === 'Xbox') return 3;
                if(plat === 'iOS') return 4;
                if(plat === 'Android') return 8;
                return '';
            }

            function mostrarErroOnboarding(msg) {
                const erroDiv = document.getElementById('obErroMsg');
                if (erroDiv) {
                    erroDiv.textContent = msg;
                    erroDiv.style.display = 'block';
                    erroDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    mostrarNotificacao(msg, 'error');
                }
            }

            let timeoutObRawg = null;
            function debouncedBuscarObRawg() {
                clearTimeout(timeoutObRawg);
                timeoutObRawg = setTimeout(() => {
                    buscarObRawg();
                }, 600);
            }

            function previewObImage(event) {
                const file = event.target.files[0];
                if (!file) return;

                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/avif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    mostrarErroOnboarding("Formato de imagem não permitido! Use JPEG, PNG, GIF, AVIF ou WEBP.");
                    event.target.value = ''; // limpa input
                    hasFotoSelecionada = false; // marca que não tem foto
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    mostrarErroOnboarding("A imagem é muito grande! Escolha uma foto com menos de 2MB.");
                    event.target.value = ''; // limpa input
                    hasFotoSelecionada = false;
                    return;
                }

                document.getElementById('obErroMsg').style.display = 'none';
                hasFotoSelecionada = true;

                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('obFotoPreview');
                    if (!preview) return; // previne erro se div não existir
                    preview.innerHTML = ''; // Limpa o conteúdo antigo (emoji ou imagem)

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';

                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            }

            function limparErrosOnboarding() {
                const campos = ['obPlataforma', 'obDescricao', 'obContato', 'obGenero', 'obRawgInput'];
                campos.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.remove('fieldErrorBorder');
                });
                const previewFoto = document.getElementById('obFotoPreview');
                if (previewFoto) previewFoto.classList.remove('fieldErrorBorder');
                const gamePreview = document.getElementById('obSelectedGamePreview');
                if (gamePreview) gamePreview.classList.remove('fieldErrorBorder');
                const erroDiv = document.getElementById('obErroMsg');
                if (erroDiv) erroDiv.style.display = 'none';
            }

            function marcarErroOnboarding(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.add('fieldErrorBorder');
                if (el.scrollIntoView) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            function buscarObRawg() {
                let query = document.getElementById('obRawgInput').value.trim();
                if (!query) {
                    document.getElementById('obRawgResults').innerHTML = '';
                    return;
                }

                let platSelected = document.getElementById('obPlataforma').value;
                let platParam = getPlatformRawgId(platSelected);
                let platQuery = platParam ? `&parent_platforms=${platParam}` : '';

                let resultsDiv = document.getElementById('obRawgResults');
                resultsDiv.innerHTML = '<p style="text-align:center; color:#ccc; font-size:12px;">Buscando...</p>';

                fetch(`https://api.rawg.io/api/games?key=9ded2b73f0bf43478084f3b3389f15a7&search=${encodeURIComponent(query)}&search_precise=true&search_exact=true&exclude_additions=true&page_size=4${platQuery}`)
                    .then(res => res.json())
                    .then(data => {
                        resultsDiv.innerHTML = '';
                        if (data.results && data.results.length > 0) {
                            data.results.forEach(game => {
                                let genres = game.genres.map(g => g.name).join(', ');
                                let releaseDb = game.released || '???';
                                let cover = game.background_image || '../fotos/default_game.png';

                                let gameDiv = document.createElement('div');
                                gameDiv.style = "display:flex; align-items:center; background:#444; margin-bottom:5px; padding:8px; border-radius:5px; cursor:pointer;";
                                gameDiv.innerHTML = `
                            <img src="${cover}" style="width:40px; height:40px; object-fit:cover; border-radius:5px; margin-right:10px;">
                            <div style="flex:1;">
                                <h4 style="margin:0; font-size:13px; color: white;">${game.name}</h4>
                                <p style="margin:0; font-size:10px; color:#aaa;">${releaseDb} | ${genres}</p>
                            </div>
                        `;
                                gameDiv.onclick = () => selecionarJogoOnboarding(game.id, game.name, cover, genres);
                                resultsDiv.appendChild(gameDiv);
                            });
                        } else {
                            resultsDiv.innerHTML = '<p style="text-align:center;color:#ccc;font-size:12px;">Nenhum jogo encontrado.</p>';
                        }
                    })
                    .catch(err => {
                        resultsDiv.innerHTML = '<p style="text-align:center;color:red;font-size:12px;">Erro na API.</p>';
                    });
            }

            function selecionarJogoOnboarding(id, name, image, genres) {
                document.getElementById('obRawgId').value = id;
                document.getElementById('obRawgName').value = name;
                document.getElementById('obRawgImage').value = image;
                document.getElementById('obRawgGenres').value = genres;

                document.getElementById('obRawgResults').innerHTML = '';
                document.getElementById('obRawgInput').value = '';

                let preview = document.getElementById('obSelectedGamePreview');
                preview.style.display = 'flex';
                preview.innerHTML = `
                <img src="${image}" style="width:50px; height:50px; object-fit:cover; border-radius:5px;">
                <div style="flex:1;">
                    <h4 style="margin:0; font-size:14px; color:white;">${name}</h4>
                    <p style="margin:0; font-size:11px; color:#28a745; font-weight:bold;">Selecionado!</p>
                </div>
                <button onclick="removerSelecaoOnboarding()" style="background:transparent; border:none; color:red; cursor:pointer; font-size:18px;" title="Remover">🗑️</button>
            `;

                hasGameSelected = true;
            }

            function removerSelecaoOnboarding() {
                document.getElementById('obRawgId').value = '';
                document.getElementById('obRawgName').value = '';
                document.getElementById('obRawgImage').value = '';
                document.getElementById('obRawgGenres').value = '';
                document.getElementById('obSelectedGamePreview').style.display = 'none';
                hasGameSelected = false;
            }

            async function finalizarOnboarding(event) {
                const btnSalvar = event.target;
                const originalText = btnSalvar.textContent;
                btnSalvar.disabled = true;
                btnSalvar.textContent = "Salvando...";

                const fileInput = document.getElementById('obFotoInput');
                const plat = document.getElementById('obPlataforma').value;
                const desc = document.getElementById('obDescricao').value.trim();
                const contRaw = document.getElementById('obContato').value.trim();
                const tipoContOb = document.getElementById('obTipoContato').value;
                const prefixosOb = { steam: 'Steam', psn: 'PSN', xbox: 'Xbox' };
                const cont = (prefixosOb[tipoContOb] || tipoContOb) + ': ' + contRaw;
                const genero = document.getElementById('obGenero').value;

                limparErrosOnboarding();

                let erros = [];
                if (!hasFotoSelecionada) {
                    erros.push("a Foto do Perfil");
                    const previewFoto = document.getElementById('obFotoPreview');
                    if (previewFoto) previewFoto.classList.add('fieldErrorBorder');
                }
                if (!genero) {
                    erros.push("o Gênero");
                    marcarErroOnboarding('obGenero');
                }
                if (!plat) {
                    erros.push("o Console");
                    marcarErroOnboarding('obPlataforma');
                }
                if (!desc) {
                    erros.push("a Descrição");
                    marcarErroOnboarding('obDescricao');
                }
                if (!contRaw) {
                    erros.push("o Contato");
                    marcarErroOnboarding('obContato');
                }
                if (!hasGameSelected) {
                    erros.push("pelo menos 1 Jogo Favorito");
                    marcarErroOnboarding('obRawgInput');
                    const gamePreview = document.getElementById('obSelectedGamePreview');
                    if (gamePreview) gamePreview.classList.add('fieldErrorBorder');
                }

                if (erros.length > 0) {
                    mostrarErroOnboarding("Por favor, preencha: " + erros.join(", "));
                    btnSalvar.disabled = false;
                    btnSalvar.textContent = originalText;
                    return;
                }

                if (genero === 'feminino' || genero === 'nao_binario' || genero === 'outro') {
                    // Mostrar modal de preferência antes de salvar
                    document.getElementById('modalPreferencia').style.display = 'flex';
                    window.currentSaveEvent = event;
                    window.currentBtnSalvar = btnSalvar;
                    window.currentOriginalText = originalText;
                    // Reset botão pois vamos aguardar o modal
                    btnSalvar.disabled = false;
                    btnSalvar.textContent = originalText;
                    return;
                }

                // Se masculino, salva direto
                await executarSalvamentoOnboarding(btnSalvar, originalText, plat, desc, cont, genero, null, fileInput);
            }

            async function salvarPreferenciaEFinalizar() {
                const checkboxes = document.querySelectorAll('input[name="pref"]:checked');
                if (checkboxes.length === 0) {
                    alert("Selecione pelo menos uma preferência");
                    return;
                }
                
                let preferencias = '';
                if (checkboxes.length > 1) {
                    preferencias = 'ambos'; // Se marcou mais de um, mapeia pro banco como 'ambos'
                } else {
                    let val = checkboxes[0].value;
                    if (val === 'ambos' || val === 'geral') {
                        preferencias = 'ambos';
                    } else {
                        preferencias = val; // homem ou mulher
                    }
                }
                
                document.getElementById('modalPreferencia').style.display = 'none';
                
                const btnSalvar = window.currentBtnSalvar;
                const originalText = window.currentOriginalText;
                btnSalvar.disabled = true;
                btnSalvar.textContent = "Salvando...";

                const plat = document.getElementById('obPlataforma').value;
                const desc = document.getElementById('obDescricao').value.trim();
                const contRaw = document.getElementById('obContato').value.trim();
                const tipoContOb = document.getElementById('obTipoContato').value;
                const prefixosOb = { steam: 'Steam', psn: 'PSN', xbox: 'Xbox' };
                const cont = (prefixosOb[tipoContOb] || tipoContOb) + ': ' + contRaw;
                const genero = document.getElementById('obGenero').value;
                const fileInput = document.getElementById('obFotoInput');

                await executarSalvamentoOnboarding(btnSalvar, originalText, plat, desc, cont, genero, preferencias, fileInput);
            }

            async function executarSalvamentoOnboarding(btnSalvar, originalText, plat, desc, cont, genero, generoPreferencia, fileInput) {
                let formData = new FormData();
                formData.append('nome', "<?php echo isset($nomeUsuario) ? trim($nomeUsuario) : ''; ?>");
                formData.append('plataforma', plat);
                formData.append('descricao', desc);
                formData.append('contato', cont);
                formData.append('genero', genero);
                formData.append('genero_preferencia', generoPreferencia || null);

                if (fileInput && fileInput.files && fileInput.files.length > 0) {
                    formData.append('foto', fileInput.files[0]);
                }

                try {
                    // 1. Salvar perfil
                    const res = await fetch('../process/perfil_action.php', { method: 'POST', body: formData });
                    const data = await res.json();

                    if (!data.success) {
                        throw new Error(data.message || "Erro ao salvar perfil");
                    }

                    if (data.fotoAtualizada && data.novaFoto) {
                        document.getElementById('obFotoPreview').innerHTML =
                            `<img src="../uploads/${data.novaFoto}" style="width:100%;height:100%;object-fit:cover;">`;
                    }

                    // 2. Salvar Jogo RAWG
                    const gId = document.getElementById('obRawgId')?.value;
                    if (gId) {
                        const gName = document.getElementById('obRawgName').value;
                        const gImg = document.getElementById('obRawgImage').value;
                        const gGen = document.getElementById('obRawgGenres').value;

                        const resGame = await fetch('../process/save_game_action.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ rawg_id: gId, name: gName, image: gImg, genres: gGen })
                        });
                        const dataGame = await resGame.json();
                        if (!dataGame.success) {
                            throw new Error(dataGame.message || "Erro ao salvar o jogo selecionado");
                        }
                    }

                    // Feedback visual de sucesso
                    btnSalvar.textContent = "✔ Perfil salvo!";
                    btnSalvar.style.backgroundColor = "#28a745"; // verde
                    setTimeout(() => window.location.reload(), 1200); // recarrega depois de 1,2s

                } catch (err) {
                    console.error(err);
                    mostrarErroOnboarding(err.message || 'Verifique seus dados e tente novamente');
                    btnSalvar.textContent = "❌ Erro!";
                    btnSalvar.style.backgroundColor = "#d10000"; // vermelho
                    setTimeout(() => {
                        btnSalvar.disabled = false;
                        btnSalvar.textContent = originalText;
                        btnSalvar.style.backgroundColor = "#a70000"; // cor original
                    }, 2000);
                }
            }
        </script>
    <?php endif; ?>
</body>

</html>