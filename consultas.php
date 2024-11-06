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

// Processa a exclusão de uma tarefa
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $tar_codigo = $_GET['excluir'];

    // Exclui a tarefa do banco de dados
    $stmt = $pdo->prepare("DELETE FROM tbl_tarefas WHERE tar_codigo = ?");
    $stmt->execute([$tar_codigo]);

    // Redireciona após a exclusão
    header("Location: consultas.php");
    exit();
}

// Consulta para pegar todas as tarefas
$stmt = $pdo->query("SELECT t.tar_codigo, t.tar_setor, t.tar_descricao, t.tar_prioridade, u.usu_nome
                     FROM tbl_tarefas t
                     JOIN tbl_usuarios u ON t.usu_codigo = u.usu_codigo");
$tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todas as Tarefas - Consultas</title>
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
    </style>
</head>
<body>

    <div class="header">
        <h1>Todas as Tarefas - Consultas</h1>
    </div>

    <div class="content">
        <h2>Lista de Tarefas</h2>

        <?php if (isset($tarefas) && count($tarefas) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Setor</th>
                        <th>Descrição</th>
                        <th>Prioridade</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tarefas as $tarefa): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tarefa['tar_setor']); ?></td>
                            <td><?php echo htmlspecialchars($tarefa['tar_descricao']); ?></td>
                            <td><?php echo htmlspecialchars($tarefa['tar_prioridade']); ?></td>
                            <td><?php echo htmlspecialchars($tarefa['usu_nome']); ?></td>
                            <td>
                                <!-- Botão de excluir -->
                                <a href="consultas.php?excluir=<?php echo $tarefa['tar_codigo']; ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir esta tarefa?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Não há tarefas registradas.</p>
        <?php endif; ?>
    </div>

</body>
</html>
