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

    // Consulta para pegar as tarefas associadas ao usuário
    $stmt = $pdo->prepare("SELECT t.tar_codigo, t.tar_setor, t.tar_descricao, t.tar_prioridade, t.tar_status, u.usu_nome
                           FROM tbl_tarefas t
                           JOIN tbl_usuarios u ON t.usu_codigo = u.usu_codigo
                           WHERE u.usu_nome = ?");
    $stmt->execute([$usuario_nome]);

    // Armazena os resultados da consulta
    $tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para pegar os dados do usuário
    $stmt_user = $pdo->prepare("SELECT * FROM tbl_usuarios WHERE usu_nome = ?");
    $stmt_user->execute([$usuario_nome]);
    $usuario = $stmt_user->fetch(PDO::FETCH_ASSOC);
} else {
    // Caso o parâmetro 'usuario' não esteja presente, interrompe a execução
    echo "Usuário não especificado.";
    exit();
}

// Processar a atualização do status da tarefa
if (isset($_POST['atualizar_status'])) {
    $tar_codigo = $_POST['tar_codigo'];
    $novo_status = $_POST['novo_status'];

    // Atualiza o status da tarefa no banco de dados
    $stmt = $pdo->prepare("UPDATE tbl_tarefas SET tar_status = ? WHERE tar_codigo = ?");
    $stmt->execute([$novo_status, $tar_codigo]);

    // Redireciona para atualizar a página
    header("Location: " . $_SERVER['PHP_SELF'] . "?usuario=" . urlencode($usuario_nome));
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status do Usuário</title>
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
            max-width: 900px;
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

        .btn-status {
            background-color: #3498db;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-status:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Status do Usuário: <?php echo isset($usuario['usu_nome']) ? htmlspecialchars($usuario['usu_nome']) : 'Usuário Desconhecido'; ?></h1>
    </div>

    <div class="content">
        <h2>Dados do Usuário</h2>
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['usu_nome']); ?></p>
        <p><strong>E-mail:</strong> <?php echo htmlspecialchars($usuario['usu_email']); ?></p>

        <h2>Lista de Tarefas</h2>

        <?php if (isset($tarefas) && count($tarefas) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Setor</th>
                        <th>Descrição</th>
                        <th>Prioridade</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tarefas as $tarefa): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tarefa['tar_setor']); ?></td>
                            <td><?php echo htmlspecialchars($tarefa['tar_descricao']); ?></td>
                            <td><?php echo htmlspecialchars($tarefa['tar_prioridade']); ?></td>
                            <td><?php echo htmlspecialchars($tarefa['tar_status']); ?></td>
                            <td>
                                <!-- Formulário para atualizar o status da tarefa -->
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="tar_codigo" value="<?php echo $tarefa['tar_codigo']; ?>">
                                    <select name="novo_status">
                                        <option value="A Fazer" <?php echo $tarefa['tar_status'] == 'A Fazer' ? 'selected' : ''; ?>>A Fazer</option>
                                        <option value="Fazendo" <?php echo $tarefa['tar_status'] == 'Fazendo' ? 'selected' : ''; ?>>Fazendo</option>
                                        <option value="Pronto" <?php echo $tarefa['tar_status'] == 'Pronto' ? 'selected' : ''; ?>>Pronto</option>
                                    </select>
                                    <button type="submit" name="atualizar_status" class="btn-status">Atualizar</button>
                                </form>

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

    </div>

</body>
</html>
