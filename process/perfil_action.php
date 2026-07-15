<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Previne vazamento de erros HTML em requisições AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ini_set('display_errors', 0);
    ob_start();
}
include_once '../config/conex.php';
$nomeUsuario = '';
$dataNascimento = '';
$contatoUsuario = '';
$descricaoUsuario = '';
$idade = '';
$fotoUsuario = '';
$generoUsuario = '';
$generoPreferenciaUsuario = '';
$plataformaUsuario = '';
$jogos = [];

try {

    // 🔹 Atualizar dados do usuário (se for POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['usuarios_id'])) {
            if (ob_get_length())
                ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sessão expirada. Por favor, faça login novamente.']);
            exit;
        }
        $usuarioId = $_SESSION['usuarios_id'];

        $nome = $_POST['nome'] ?? '';
        $contato = $_POST['contato'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $plataforma = $_POST['plataforma'] ?? '';
        $genero = $_POST['genero'] ?? null;
        $genero_preferencia = $_POST['genero_preferencia'] ?? null;
        $updateGenero = isset($_POST['genero']);

        // Converte strings vazias em null para o ENUM
        if ($updateGenero) {
            if ($genero === '' || $genero === 'null')
                $genero = null;
            if ($genero_preferencia === '' || $genero_preferencia === 'null')
                $genero_preferencia = null;

            if ($genero === null) {
                if (ob_get_length())
                    ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'O gênero é obrigatório no cadastro.']);
                exit;
            }
        }

        if (trim($nome) === '' || trim($contato) === '' || trim($descricao) === '' || trim($plataforma) === '') {
            if (ob_get_length())
                ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos.']);
            exit;
        }

        // Verifica se tem data_nascimento no POST
        $nascimento = $_POST['data_nascimento'] ?? null;

        if ($updateGenero) {
            if ($nascimento !== null && $nascimento !== '') {
                $sqlUpdate = "UPDATE usuarios SET nome = :nome, data_nascimento = :data_nascimento, contato = :contato, descricao = :descricao, plataforma = :plataforma, genero = :genero, genero_preferencia = :genero_preferencia WHERE id = :id";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute(['nome' => $nome, 'data_nascimento' => $nascimento, 'contato' => $contato, 'descricao' => $descricao, 'plataforma' => $plataforma, 'genero' => $genero, 'genero_preferencia' => $genero_preferencia, 'id' => $usuarioId]);
            } else {
                $sqlUpdate = "UPDATE usuarios SET nome = :nome, contato = :contato, descricao = :descricao, plataforma = :plataforma, genero = :genero, genero_preferencia = :genero_preferencia WHERE id = :id";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute(['nome' => $nome, 'contato' => $contato, 'descricao' => $descricao, 'plataforma' => $plataforma, 'genero' => $genero, 'genero_preferencia' => $genero_preferencia, 'id' => $usuarioId]);
            }
        } else {
            if ($nascimento !== null && $nascimento !== '') {
                $sqlUpdate = "UPDATE usuarios SET nome = :nome, data_nascimento = :data_nascimento, contato = :contato, descricao = :descricao, plataforma = :plataforma WHERE id = :id";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute(['nome' => $nome, 'data_nascimento' => $nascimento, 'contato' => $contato, 'descricao' => $descricao, 'plataforma' => $plataforma, 'id' => $usuarioId]);
            } else {
                $sqlUpdate = "UPDATE usuarios SET nome = :nome, contato = :contato, descricao = :descricao, plataforma = :plataforma WHERE id = :id";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute(['nome' => $nome, 'contato' => $contato, 'descricao' => $descricao, 'plataforma' => $plataforma, 'id' => $usuarioId]);
            }
        }

        $fotoAtualizada = false;
        $novaFoto = '';
        $erroFoto = '';

        // 🔹 Upload da foto seguro
        if (isset($_FILES['foto'])) {
            if ($_FILES['foto']['error'] === 0) {
                $pasta = "../uploads/";
                if (!is_dir($pasta)) {
                    mkdir($pasta, 0755, true);
                }
                $nomeOriginal = $_FILES['foto']['name'];
                $ext = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
                $permitidos = ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'avif', 'webp'];

                if (in_array($ext, $permitidos)) {
                    if ($_FILES['foto']['size'] <= 5 * 1024 * 1024) { // 5MB
                        $sourcePath = $_FILES['foto']['tmp_name'];

                        // 🔹 Verifica se a extensão GD está disponível para redimensionamento
                        if (!function_exists('imagecreatetruecolor')) {
                            $nomeArquivo = uniqid() . "." . $ext;
                            $caminho = $pasta . $nomeArquivo;
                            if (move_uploaded_file($sourcePath, $caminho)) {
                                $sqlFoto = "UPDATE usuarios SET foto = :foto WHERE id = :id";
                                $stmtFoto = $pdo->prepare($sqlFoto);
                                $stmtFoto->execute(['foto' => $nomeArquivo, 'id' => $usuarioId]);
                                $fotoAtualizada = true;
                                $novaFoto = $nomeArquivo;

                                if (ob_get_length())
                                    ob_clean();
                                header('Content-Type: application/json');
                                echo json_encode(['success' => true, 'fotoAtualizada' => true, 'novaFoto' => $nomeArquivo, 'message' => 'Foto salva sem redimensionamento (GD desativado).']);
                                exit;
                            } else {
                                $erroFoto = 'Erro ao mover arquivo para a pasta uploads.';
                            }
                        } else {
                            $imgInfo = getimagesize($sourcePath);
                            if ($imgInfo !== false) {
                                $nomeArquivo = uniqid() . "." . $ext;
                                $caminho = $pasta . $nomeArquivo;

                                list($width, $height) = $imgInfo;
                                $maxWidth = 500;
                                $maxHeight = 500;
                                $ratio = min($maxWidth / $width, $maxHeight / $height);
                                $newWidth = round($width * $ratio);
                                $newHeight = round($height * $ratio);

                                $thumb = imagecreatetruecolor($newWidth, $newHeight);
                                $source = null;

                                switch ($imgInfo['mime']) {
                                    case 'image/png':
                                        $source = imagecreatefrompng($sourcePath);
                                        imagealphablending($thumb, false);
                                        imagesavealpha($thumb, true);
                                        break;
                                    case 'image/gif':
                                        $source = imagecreatefromgif($sourcePath);
                                        $transIndex = imagecolortransparent($source);
                                        if ($transIndex >= 0) {
                                            $transColor = imagecolorsforindex($source, $transIndex);
                                            $transIndexNew = imagecolorallocate($thumb, $transColor['red'], $transColor['green'], $transColor['blue']);
                                            imagefill($thumb, 0, 0, $transIndexNew);
                                            imagecolortransparent($thumb, $transIndexNew);
                                        }
                                        break;
                                    case 'image/jpeg':
                                    case 'image/pjpeg':
                                        $source = imagecreatefromjpeg($sourcePath);
                                        break;
                                    case 'image/webp':
                                        if (function_exists('imagecreatefromwebp')) {
                                            $source = imagecreatefromwebp($sourcePath);
                                        } else {
                                            $erroFoto = 'Servidor não suporta WebP.';
                                        }
                                        break;
                                    case 'image/avif':
                                        if (function_exists('imagecreatefromavif')) {
                                            $source = imagecreatefromavif($sourcePath);
                                        } else {
                                            $erroFoto = 'Servidor não suporta AVIF.';
                                        }
                                        break;
                                    default:
                                        $erroFoto = 'Tipo de imagem não suportado: ' . $imgInfo['mime'];
                                        break;
                                }

                                if ($source) {
                                    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                                    $salvo = false;
                                    switch ($imgInfo['mime']) {
                                        case 'image/png':
                                            $salvo = imagepng($thumb, $caminho, 8);
                                            break;
                                        case 'image/gif':
                                            $salvo = imagegif($thumb, $caminho);
                                            break;
                                        case 'image/jpeg':
                                        case 'image/pjpeg':
                                            $salvo = imagejpeg($thumb, $caminho, 80);
                                            break;
                                        case 'image/webp':
                                            $salvo = imagewebp($thumb, $caminho, 80);
                                            break;
                                        case 'image/avif':
                                            $salvo = imageavif($thumb, $caminho, 80);
                                            break;
                                    }

                                    imagedestroy($thumb);
                                    imagedestroy($source);

                                    if ($salvo) {
                                        $sqlFoto = "UPDATE usuarios SET foto = :foto WHERE id = :id";
                                        $stmtFoto = $pdo->prepare($sqlFoto);
                                        $stmtFoto->execute(['foto' => $nomeArquivo, 'id' => $usuarioId]);
                                        $fotoAtualizada = true;
                                        $novaFoto = $nomeArquivo;
                                    } else {
                                        $erroFoto = 'Não foi possível salvar o arquivo no servidor.';
                                    }
                                }
                            } else {
                                $erroFoto = 'O arquivo enviado não é uma imagem válida.';
                            }
                        }
                    } else {
                        $erroFoto = 'A imagem é muito grande. O limite é 5MB.';
                    }
                } else {
                    $erroFoto = 'Formato de arquivo não permitido: ' . $ext;
                }
            } else {
                if ($_FILES['foto']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['foto']['error'] === UPLOAD_ERR_FORM_SIZE) {
                    $erroFoto = 'A imagem é muito grande para o servidor. Tente uma foto menor (idealmente menos de 2MB).';
                } else {
                    $erroFoto = 'Erro no upload: ' . $_FILES['foto']['error'];
                }
            }
        }

        // Se tentou enviar foto mas deu erro, retorna sucesso false
        if (!empty($erroFoto)) {
            if (ob_get_length())
                ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $erroFoto]);
            exit;
        }

        // Limpa qualquer saída acidental (espaços, avisos) antes de enviar o JSON
        if (ob_get_length())
            ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'fotoAtualizada' => $fotoAtualizada, 'novaFoto' => $novaFoto]);
        exit;
    }

    // 🔹 Buscar usuário logado e seus jogos
    if (isset($_SESSION['usuarios_id'])) {
        $usuarioId = $_SESSION['usuarios_id'];

        // Buscar jogos do usuário logado
        $sqlJogos = "
            SELECT g.id, g.rawg_id, g.name as nome, g.image as foto, g.genres 
            FROM usuario_top_jogos utj
            JOIN games g ON utj.jogo_id = g.id
            WHERE utj.usuario_id = :id
            LIMIT 3
        ";
        $stmtJogos = $pdo->prepare($sqlJogos);
        $stmtJogos->execute(['id' => $usuarioId]);

        while ($rowJogo = $stmtJogos->fetch(PDO::FETCH_ASSOC)) {
            $jogos[] = $rowJogo;
        }

        // Buscar dados do perfil
        $sql = "SELECT nome, data_nascimento, contato, descricao, plataforma, foto, genero, genero_preferencia FROM usuarios WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $usuarioId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $nomeUsuario = $row['nome'];
            $dataNascimento = $row['data_nascimento'];
            $contatoUsuario = $row['contato'];
            $descricaoUsuario = $row['descricao'];
            $plataformaUsuario = $row['plataforma'];
            $fotoUsuario = $row['foto'];
            $generoUsuario = $row['genero'];
            $generoPreferenciaUsuario = $row['genero_preferencia'];

            if ($dataNascimento) {
                $nascimentoLocal = new DateTime($dataNascimento);
                $hoje = new DateTime();
                $idade = $hoje->diff($nascimentoLocal)->y;
            }
        }
    }

} catch (Throwable $e) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (ob_get_length())
            ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        exit;
    }
    echo "Erro: " . $e->getMessage();
}
?>