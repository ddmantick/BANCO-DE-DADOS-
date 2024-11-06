<?php
// Configuração do banco de dados
$host = 'localhost';
$dbname = 'tarefas';
$username = 'root';
$password = '';

// Conexão ao banco de dados usando PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Erro na conexão com o banco de dados: ' . $e->getMessage();
}

// Verifica se o nome do usuário foi passado como parâmetro na URL
if (isset($_GET['usuario'])) {
    $usuario_nome = $_GET['usuario'];

    // Consulta para pegar as tarefas associadas ao usuário, incluindo a prioridade
    $stmt = $pdo->prepare("SELECT t.tar_codigo, t.tar_setor, t.tar_descricao, t.tar_prioridade
                           FROM tbl_tarefas t
                           JOIN tbl_usuarios u ON t.usu_codigo = u.usu_codigo
                           WHERE u.usu_nome = ?");
    $stmt->execute([$usuario_nome]);

    // Armazena os resultados da consulta
    $tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Caso o parâmetro 'usuario' não esteja presente, interrompe a execução
    echo "Usuário não especificado.";
    exit();
}

// Processa a adição de uma nova tarefa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setor'], $_POST['descricao'], $_POST['prioridade'])) {
    $setor = $_POST['setor'];
    $descricao = $_POST['descricao'];
    $prioridade = $_POST['prioridade']; // Captura o valor da prioridade

    // Consulta para obter o código do usuário
    $stmt = $pdo->prepare("SELECT usu_codigo FROM tbl_usuarios WHERE usu_nome = ?");
    $stmt->execute([$usuario_nome]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $usu_codigo = $usuario['usu_codigo'];

        // Inserir a nova tarefa na tabela de tarefas com a prioridade
        $stmt = $pdo->prepare("INSERT INTO tbl_tarefas (tar_setor, tar_descricao, tar_prioridade, usu_codigo)
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([$setor, $descricao, $prioridade, $usu_codigo]);

        // Redireciona para evitar o reenvio do formulário
        header("Location: " . $_SERVER['PHP_SELF'] . "?usuario=" . urlencode($usuario_nome));
        exit();
    }
}

// Processa a exclusão de uma tarefa
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $tar_codigo = $_GET['excluir'];

    // Exclui a tarefa do banco de dados
    $stmt = $pdo->prepare("DELETE FROM tbl_tarefas WHERE tar_codigo = ?");
    $stmt->execute([$tar_codigo]);

    // Redireciona após a exclusão
    header("Location: " . $_SERVER['PHP_SELF'] . "?usuario=" . urlencode($usuario_nome));
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PÁGINA DE TAREFAS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f4f9;
        }

        .header {
            text-align: center;
            background-color: #333;
            color: #fff;
            padding: 15px 0;
            margin-bottom: 20px;
        }

        .content {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        form input, form textarea, form select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        form textarea {
            height: 100px;
        }

        .btn-excluir {
            background-color: #e74c3c;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-excluir:hover {
            background-color: #c0392b;
        }

        .btn-entrar {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        .btn-entrar:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>TAREFAS DE <?php echo isset($usuario_nome) ? htmlspecialchars($usuario_nome) : 'Usuário Desconhecido'; ?></h1>
    </div>

    <div class="content">
        <!-- Formulário para adicionar tarefa -->
        <h2>Adicionar Nova Tarefa</h2>
        <form method="POST">
            <label for="setor">Setor:</label>
            <input type="text" id="setor" name="setor" required>

            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" required></textarea>

            <label for="prioridade">Prioridade:</label>
            <select id="prioridade" name="prioridade" required>
                <option value="Alta">Alta</option>
                <option value="Média">Média</option>
                <option value="Baixa">Baixa</option>
            </select>

            <input type="submit" value="Adicionar Tarefa">
        </form>

        <h2>Lista de Tarefas</h2>

        <?php if (isset($tarefas) && count($tarefas) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Setor</th>
                        <th>Descrição</th>
                        <th>Prioridade</th> <!-- Coluna de Prioridade -->
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tarefas as $tarefa): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tarefa['tar_setor']); ?></td>
                            <td><?php echo htmlspecialchars($tarefa['tar_descricao']); ?></td>
                            <td><?php echo htmlspecialchars($tarefa['tar_prioridade']); ?></td> <!-- Exibe Prioridade -->
                            <td>
                                <!-- Botão de excluir -->
                                <a href="?usuario=<?php echo urlencode($usuario_nome); ?>&excluir=<?php echo $tarefa['tar_codigo']; ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir esta tarefa?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Não há tarefas associadas a este usuário.</p>
        <?php endif; ?>

        <!-- Botão para acessar a página de status -->
        <a href="status.php?usuario=<?php echo urlencode($usuario_nome); ?>" class="btn-entrar">Entrar para Status</a>
    </div>

</body>
</html>
