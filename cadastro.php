<?php
// Configuração do banco de dados
$host = 'localhost'; // Ou o IP do seu servidor de banco de dados
$dbname = 'tarefas';
$username = 'root';  // Usuário do banco de dados
$password = '';      // Senha do banco (se houver)

// Conexão ao banco de dados usando PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Erro na conexão com o banco de dados: ' . $e->getMessage();
}

// Variáveis para armazenar dados do formulário
$tasks = [];

// Se o formulário for enviado, processa o cadastro de usuário e tarefa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pegando os dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    
    // Inserindo o usuário na tabela tbl_usuarios
    $stmt = $pdo->prepare("INSERT INTO tbl_usuarios (usu_nome, usu_email) VALUES (?, ?)");
    $stmt->execute([$nome, $email]);
    
    // Pegando o código do usuário recém-inserido
    $usu_codigo = $pdo->lastInsertId();

    // Inserindo uma tarefa na tabela tbl_tarefas associada ao usuário
    $setor = 'Setor Exemplo';  // Defina o valor ou colete de um campo do formulário
    $propriedade = 'Propriedade Exemplo';  // Defina o valor ou colete de um campo do formulário
    $descricao = 'Descrição Exemplo';  // Defina o valor ou colete de um campo do formulário
    $status = 'Ativa';  // Defina o valor ou colete de um campo do formulário

    $stmt = $pdo->prepare("INSERT INTO tbl_tarefas (tar_setor, tar_propriedade, tar_descricao, tar_status, usu_codigo) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$setor, $propriedade, $descricao, $status, $usu_codigo]);

    echo "Usuário e tarefa cadastrados com sucesso!";
}

// Processando a exclusão de um usuário
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];

    // Excluindo o usuário da tabela tbl_usuarios
    $stmt = $pdo->prepare("DELETE FROM tbl_usuarios WHERE usu_codigo = ?");
    $stmt->execute([$deleteId]);

    // Excluindo todas as tarefas associadas a esse usuário
    $stmt = $pdo->prepare("DELETE FROM tbl_tarefas WHERE usu_codigo = ?");
    $stmt->execute([$deleteId]);

    echo "Usuário e suas tarefas foram excluídos com sucesso!";
}

// Recuperando os dados da tabela de usuários para exibição
$stmt = $pdo->query("SELECT * FROM tbl_usuarios");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            max-width: 500px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        form input[type="text"], form input[type="email"], form input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ccc;
            text-align: left;
        }

        th, td {
            padding: 10px;
        }

        th {
            background-color: #f2f2f2;
        }

        p {
            text-align: center;
            font-size: 1.2em;
            color: #555;
        }

        .delete-btn {
            color: red;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>PÁGINA PRINCIPAL</h1>
    </div>

    <div class="content">
        <!-- Formulário para adicionar usuários -->
        <form method="POST">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>

            <input type="submit" value="ADICIONAR LOGIN">
        </form>

        <!-- Tabela de Usuários -->
        <?php if (count($tasks) > 0): ?>
            <h2>USUÁRIOS CADASTRADOS</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task['usu_nome']); ?></td>
                            <td><?php echo htmlspecialchars($task['usu_email']); ?></td>
                            <td>
                                <a href="?delete=<?php echo $task['usu_codigo']; ?>" class="delete-btn" onclick="return confirm('Você tem certeza que deseja excluir este usuário?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum usuário cadastrado ainda.</p>
        <?php endif; ?>
    </div>

</body>
</html>
