<?php

class SimpleComments
{
    private \PDO $pdo;
    private string $table_name;

    public function __construct(\PDO $pdo, string $table_name)
    {
        $this->pdo = $pdo;
        $this->table_name = $table_name;
    }

    public function saveComment(string $name, string $comment)
    {
        $sql = "INSERT INTO {$this->table_name} (name, comment) VALUES (:name, :comment);";

        try
        {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->execute();
            $comment_status = [
                'type' => 'success',
                'message' => 'Комментарий добавлен!'
            ];
        } catch(PDOException $e) {
            error_log("Error writing a comment to the database: " . $e->getMessage());
            $comment_status = [
                'type' => 'error',
                'message' => 'Ошибка при записи комментария в БД!'
            ];
        }
        $stmt = null;
        // Сохраняем сообщение об успехе/ошибке в transient
        set_transient('comment_status', $comment_status, 180);  // время жизни 180 секунд
    }

    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table_name}";

        try {
            $stmt = $this->pdo->query($sql);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error reading from the table {$this->table_name} " . $e->getMessage());
            set_transient(
                'comment_status',
                [
                    'type' => 'error',
                    'message' => 'Ошибка чтения из таблицы с комментариями в выбранной БД.'
                ],
                180  // время жизни в секундах
            );
        }
        $total = $row ? $row['total'] : 0;
        $row = null;
        $stmt = null;
        return $total;
    }

    public function getComments(int $per_page, int $offset)
    {
        $sql = "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $comments = $stmt->fetchAll(PDO::FETCH_OBJ);            

        } catch(PDOException $e) {
            error_log("Error reading from the table {$this->table_name} " . $e->getMessage());
            set_transient(
                'comment_status',
                [
                    'type' => 'error',
                    'message' => 'Ошибка чтения из таблицы с комментариями в выбранной БД!'
                ],
                180  // время жизни в секундах
            );
            $comments = [];
        }
        $stmt = null;
        return $comments;
    }

    public function deleteComments(array $selected_ids)
    {
        $placeholders = str_repeat('?, ', count($selected_ids) - 1) . '?';
        $sql = "DELETE FROM {$this->table_name} WHERE id IN ($placeholders)";

        try
        {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($selected_ids);
            $deleted_count = $stmt->rowCount();
            $comment_status = [
                'type' => 'success',
                'message' => "Удалено комментариев: {$deleted_count}"
            ];
        } catch(PDOException $e) {
            error_log("Error when deleting a comment from the database: " . $e->getMessage());
            $comment_status = [
                'type' => 'error',
                'message' => "Ошибка при удалении комментариев!"
            ];
            $deleted_count = 0;
        }
        
        $stmt = null;   // Закрываем соеденение с БД

        // Сохраняем сообщение об успехе/ошибке в transient
        set_transient('comment_status', $comment_status, 180);  // время жизни 180 секунд

        return $deleted_count;
    }
}