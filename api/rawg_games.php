<?php
/**
 * Script para buscar jogos na RAWG API e popular a tabela `games` no banco de dados.
 * ATENÇÃO: É necessário substituir 'SUA_CHAVE_API_AQUI' pela sua API Key real do RAWG.
 */

require_once __DIR__ . '/../config/conex.php';

// Coloque sua RAWG API KEY aqui
$rawg_api_key = '9ded2b73f0bf43478084f3b3389f15a7';

if (empty($rawg_api_key) || $rawg_api_key === 'SUA_CHAVE_API_AQUI') {
    die("ERRO: Você precisa colocar a sua chave da RAWG API no arquivo rawg_games.php na variável \$rawg_api_key\n");
}

// Vamos buscar as primeiras 2 páginas (aprox 40 jogos mais populares)
$pages_to_fetch = 2;
$games_inserted = 0;

for ($page = 1; $page <= $pages_to_fetch; $page++) {
    // Busca jogos populares
    $url = "https://api.rawg.io/api/games?key={$rawg_api_key}&page={$page}&page_size=20&ordering=-added";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // É importante adicionar um User-Agent ao fazer requisições para a RAWG (conforme doc deles)
    curl_setopt($ch, CURLOPT_USERAGENT, 'GameConnectTCC/1.0');

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo "Erro ao buscar jogos na RAWG. HTTP Code: $httpCode\n";
        continue;
    }

    $data = json_decode($response, true);
    if (!isset($data['results']) || empty($data['results'])) {
        break;
    }

    foreach ($data['results'] as $game) {
        $rawg_id = $game['id'];
        $name = $game['name'];
        $image = $game['background_image'] ?? '';

        // Formatar os gêneros (ex: "Action, RPG, Strategy")
        $genres_arr = [];
        if (isset($game['genres'])) {
            foreach ($game['genres'] as $genre) {
                $genres_arr[] = $genre['name'];
            }
        }
        $genres_str = implode(', ', $genres_arr);

        // Identificar se é Online/Multiplayer através das Tags
        $is_online = 0;
        if (isset($game['tags'])) {
            foreach ($game['tags'] as $tag) {
                $tagName = strtolower($tag['slug']);
                if ($tagName === 'multiplayer' || $tagName === 'online' || $tagName === 'co-op') {
                    $is_online = 1;
                    break;
                }
            }
        }

        try {
            $sql = "INSERT IGNORE INTO games (rawg_id, name, image, genres) 
                    VALUES (:rawg_id, :name, :image, :genres)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'rawg_id' => $rawg_id,
                'name' => $name,
                'image' => $image,
                'genres' => $genres_str
            ]);

            if ($stmt->rowCount() > 0) {
                $games_inserted++;
                echo "Inserido: $name \n";
            }
        } catch (PDOException $e) {
            echo "Erro ao inserir $name: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nBusca finalizada! Foram inseridos/atualizados $games_inserted jogos no banco de dados.\n";
?>