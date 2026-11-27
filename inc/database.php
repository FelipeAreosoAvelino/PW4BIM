<?php

require_once __DIR__ . '/auth.php';

// Faz o mysqli lançar exceções em caso de erro
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * Criptografia de senhas (bcrypt via password_hash).
 * Para conferir use password_verify($senhaDigitada, $hashDoBanco).
 */
function criptografia($senha)
{
    return password_hash($senha, PASSWORD_DEFAULT);
}

/**
 * Garante que nome de tabela/coluna tenha apenas letras, números e _
 * (nomes não podem ser parametrizados, então são validados).
 */
function sql_identifier($nome)
{
    if (!is_string($nome) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $nome)) {
        throw new InvalidArgumentException("Nome de tabela ou coluna inválido.");
    }
    return $nome;
}

function open_database()
{
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log("Erro ao conectar no banco: " . $e->getMessage());
        throw new Exception("Erro ao conectar no banco de dados.");
    }
}

function close_database($conn)
{
    if ($conn instanceof mysqli) {
        try {
            $conn->close();
        } catch (Exception $e) {
            error_log("Erro ao encerrar conexão: " . $e->getMessage());
        }
    }
}

/**
 * Pesquisa um registro pelo ID (retorna array) ou, sem ID, todos os
 * registros da tabela (retorna lista). Retorna null se não houver nada.
 */
function find($table = null, $id = null)
{
    $found = null;
    $database = null;

    try {
        $table = sql_identifier($table);
        $database = open_database();

        if ($id !== null && $id !== '') {
            $id = (int) $id;
            $stmt = $database->prepare("SELECT * FROM `$table` WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $found = $stmt->get_result()->fetch_assoc() ?: null;
            $stmt->close();
        } else {
            $result = $database->query("SELECT * FROM `$table`");
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $found = $rows ?: null;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        flash("Não foi possível consultar os dados.", "danger");
    }

    close_database($database);
    return $found;
}

/**
 * Pesquisa todos os registros de uma tabela.
 */
function find_all($table)
{
    return find($table);
}

/**
 * Busca por texto (LIKE) em uma coluna, de forma segura.
 */
function search($table, $column, $term)
{
    $found = null;
    $database = null;

    try {
        $table = sql_identifier($table);
        $column = sql_identifier($column);
        $database = open_database();

        $like = '%' . addcslashes((string) $term, '%_\\') . '%';
        $stmt = $database->prepare("SELECT * FROM `$table` WHERE `$column` LIKE ?");
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $found = $rows ?: null;
    } catch (Exception $e) {
        error_log($e->getMessage());
        flash("Não foi possível pesquisar os dados.", "danger");
    }

    close_database($database);
    return $found;
}

/**
 * Valida o array de dados e devolve [colunas, valores].
 * As chaves vindas de name="x['campo']" têm as aspas removidas.
 */
function prepare_data($data)
{
    if (!is_array($data) || empty($data)) {
        throw new InvalidArgumentException("Nenhum dado para gravar.");
    }

    $columns = [];
    $values = [];

    foreach ($data as $key => $value) {
        if (is_array($value) || is_object($value)) {
            throw new InvalidArgumentException("Valor inválido no campo '$key'.");
        }
        $columns[] = sql_identifier(trim((string) $key, "'\" "));
        $values[] = $value;
    }

    return [$columns, $values];
}

/**
 * Insere um registro. Retorna true/false e define a mensagem.
 */
function save($table = null, $data = null)
{
    $database = null;
    $ok = false;

    try {
        $table = sql_identifier($table);
        list($columns, $values) = prepare_data($data);

        $cols = '`' . implode('`,`', $columns) . '`';
        $marks = implode(',', array_fill(0, count($values), '?'));

        $database = open_database();
        $stmt = $database->prepare("INSERT INTO `$table` ($cols) VALUES ($marks)");
        $stmt->bind_param(str_repeat('s', count($values)), ...$values);
        $stmt->execute();
        $stmt->close();

        flash("Registro cadastrado com sucesso.", "success");
        $ok = true;
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        if ($e->getCode() == 1062) {
            flash("Já existe um registro com esses dados.", "danger");
        } else {
            flash("Não foi possível realizar a operação.", "danger");
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        flash("Não foi possível realizar a operação.", "danger");
    }

    close_database($database);
    return $ok;
}

/**
 * Atualiza um registro por ID. Retorna true/false e define a mensagem.
 */
function update($table = null, $id = 0, $data = null)
{
    $database = null;
    $ok = false;

    try {
        $table = sql_identifier($table);
        list($columns, $values) = prepare_data($data);

        $set = implode(',', array_map(function ($c) {
            return "`$c` = ?";
        }, $columns));

        $values[] = (int) $id;

        $database = open_database();
        $stmt = $database->prepare("UPDATE `$table` SET $set WHERE id = ?");
        $stmt->bind_param(str_repeat('s', count($values) - 1) . 'i', ...$values);
        $stmt->execute();
        $stmt->close();

        flash("Registro atualizado com sucesso.", "success");
        $ok = true;
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        if ($e->getCode() == 1062) {
            flash("Já existe um registro com esses dados.", "danger");
        } else {
            flash("Não foi possível realizar a operação.", "danger");
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        flash("Não foi possível realizar a operação.", "danger");
    }

    close_database($database);
    return $ok;
}

/**
 * Remove um registro por ID. Retorna true se removeu.
 */
function remove($table = null, $id = null)
{
    $database = null;
    $ok = false;

    try {
        $table = sql_identifier($table);
        $id = (int) $id;

        if ($id <= 0) {
            throw new InvalidArgumentException("ID inválido.");
        }

        $database = open_database();
        $stmt = $database->prepare("DELETE FROM `$table` WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $ok = $stmt->affected_rows > 0;
        $stmt->close();

        if ($ok) {
            flash("Registro removido com sucesso.", "success");
        } else {
            flash("Registro não encontrado.", "danger");
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        flash("Não foi possível realizar a operação.", "danger");
    }

    close_database($database);
    return $ok;
}