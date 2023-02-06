<?php

class TaskGateway{

    private PDO $conn;

    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }


    public function getAllUserTasks(int $id): array | false{
        $sql = "SELECT *
        FROM task
        WHERE user_id = :user_id
        ORDER BY number ASC";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":user_id", $id, PDO::PARAM_INT);

        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        if(empty($data)){
            return false;
        }

        return $data;
    }


    public function getUserTaskByID(int $task_id, int $user_id): array | false{
        $sql = "SELECT *
                FROM task
                WHERE user_id = :user_id AND id = :task_id";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":task_id", $task_id, PDO::PARAM_INT);
        $stmt->bindvalue(":user_id", $user_id, PDO::PARAM_INT);

        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        if(empty($data)){
            return false;
        }

        return $data;
    }


    public function addNewTask(int $user_id, array $data): int{
        $sql = "INSERT INTO task (name, priority, is_completed) VALUES (:name, :priority, :is_completed)";

        $stmt = $this->conn->prepare($sql);

        $data["is_completed"] = $data["is_completed"] === 1 ? true : false;

        $stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);
        $stmt->bindValue(":priority", $data["priority"], PDO::PARAM_INT);
        $stmt->bindValue(":is_completed", $data["is_completed"] ?? false, PDO::PARAM_BOOL);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }


    public function updateTaskInfo(int $task_id, array $data): int{
        $fields = [];

        if(array_key_exists("name", $data)){
            $fields["name"] = [$data["name"], PDO::PARAM_INT];
        }

        if(array_key_exists("priority", $data)){
            $fields["priority"] = [$data["priority"], PDO::PARAM_INT];
        }

        if(array_key_exists("is_completed", $data)){
            $fields["is_completed"] = [$data["is_completed"], PDO::PARAM_INT];
        }

        if(empty($fields)){
            return 0;
        }else{
            $sets = array_map(function($value){
                return "$value = :$value";
            }, array_keys($fields));

            $sql = "UPDATE task SET " . implode(", ", $sets) . " WHERE id = :id";

            $stmt = $this->conn->prepare($sql);

            $stmt->bindValue(":id", $task_id);

            foreach($fields as $name => $values){
                $stmt->bindValue(":$name", $values[0], $values[1]);
            }

            $stmt->execute();

            return $stmt->rowCount();
        }
    } 
    
    
    public function deleteTask(int $id): int{
        $sql = "DELETE FROM task WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
}