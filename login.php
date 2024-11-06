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

// Se o formulário de login for enviado, processa a autenticação
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];

    // Consulta para verificar se o usuário existe no banco de dados
    $stmt = $pdo->prepare("SELECT * FROM tbl_usuarios WHERE usu_nome = ? AND usu_email = ?");
    $stmt->execute([$nome, $email]);

    // Se um usuário for encontrado
    if ($stmt->rowCount() > 0) {
        // O usuário foi encontrado, redireciona para a página de tarefas
        header("Location: tarefas.php?usuario=" . urlencode($nome)); // Passa o nome como parâmetro
        exit(); // Impede que o código continue executando após o redirecionamento
    } else {
        // Caso contrário, exibe uma mensagem de erro
        $error_message = "Usuário não encontrado. Verifique o nome e email.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PÁGINA DE LOGIN</title>
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

        p.error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>PÁGINA DE LOGIN</h1>
    </div>

    <div class="content">
        <!-- Formulário para login -->
        <form method="POST">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>

            <input type="submit" value="FAZER LOGIN">
        </form>

        <!-- Exibir mensagem de erro, caso haja -->
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
    </div>

</body>
</html> 
