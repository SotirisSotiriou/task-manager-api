<?php

class TaskController{
    private TaskGateway $gateway;

    public function __construct(TaskGateway $gateway){
        $this->gateway = $gateway;
    }

    
    public function processRequest(string $method, int $user_id): void{
        $data_json = file_get_contents('php://input');
        
        $data = (array)json_decode($data_json, true);

        $id = null;
        if(!empty($data['id'])){
            $id = $data['id'];
        }

        $errors = $this->getValidationErrors($data, $method);
        if(!empty($errors)){
            $this->respondUnprocessableEntity($errors);
            return;
        }

        if($id === null){
            switch($method){
                case "POST":
                    $output_data = $this->gateway->addNewTask($user_id, $data);
                    $this->respondTaskCreated($id);
                    break;

                case "GET":
                    $output_data = $this->gateway->getAllUserTasks($user_id);
                    echo json_encode($output_data);
                    break;

                default:
                    $this->respondMethodNotAllowed("POST, GET");
            }
        }
        else{
            switch($method){
                case "GET":
                    $output_data = $this->gateway->getUserTaskByID($id, $user_id);
                    if(empty($output_data)){
                        $this->respondTaskNotFound($id);
                    }
                    else{
                        echo json_encode($output_data);
                    }
                    break;

                case "PATCH":
                    $output_data = $this->gataway->updateTaskInfo($id, $data);
                    if($output_data > 0){
                        $this->respondTaskUpdated($id);
                    }
                    else{
                        $this->respondTaskNotFound($id);
                    }
                    break;

                case "DELETE":
                    //TODO
                    $output_data = $this->gateway->deleteTask($id);
                    if($output_data > 0){
                        $this->respondTaskDeleted($id);
                    }
                    else{
                        $this->respondTaskNotFound($id);
                    }
                    break;

                default:
                    $this->respondMethodNotAllowed("GET, PATCH, DELETE");
            }
        }
    }


    private function respondUnprocessableEntity(array $errors): void{
        http_response_code(422);
        echo json_encode(["errors" => $errors]);
    }


    private function respondMethodNotAllowed(string $allowed_methods): void{
        http_response_code(405);
        header("Allow: $allowed_methods");
    }

  
    private function respondEmptyData(): void{
        http_response_code(404);
        echo json_encode(["message" => "Empty data"]);
    }


    private function respondTaskNotFound(string $task_id, string $user_id): void{
        http_response_code(404);
        echo json_encode(["message" => "Task with ID $task_id not found for user with ID $user_id"]);
    }


    private function respondTaskCreated(string $id): void{
        http_response_code(201);
        echo json_encode(["message" => "Task created", "id" => $id]);
    }


    private function respondTaskDeleted(string $id): void{
        http_response_code(200);
        echo json_encode(["message" => "Task deleted", "id" => $id]);
    }


    private function respondTaskUpdated(string $id): void{
        http_response_code(200);
        echo json_encode(["message" => "Task updated", "id" => $id]);
    }


    public function getValidationErrors(array $data, string $method): array{
        $errors = [];

        switch($method){
            case "POST":
                if(empty($data["priority"])){
                    $errors[] = "priority is required";
                }
                else if(filter_var($data["priority"], FILTER_VALIDATE_INT) === false){
                    $errors[] = "priority invalid format";
                }

                if(empty($data["is_completed"])){
                    $errors[] = "is_completed is required";
                }
                else if(filter_var($data["is_completed"], FILTER_VALIDATE_INT) === false){
                    $errors[] = "is_completed invalid format";
                }
                else if(!($data["is_completed"] === 1 or $data["is_completed"] === 0)){
                    $errors[] = "is_completed must be 1 or 0";
                }
                break;
            
            case "GET":
                break;

            case "PATCH":
                if(!empty($data["priority"])){
                    if(filter_var($data["priority"], FILTER_VALIDATE_INT) === false){
                        $errors[] = "priority invalid format";
                    }
                    else if(!in_array($data["priority"], [0,1,2,3], true)){
                        $errors[] = "priority must be 0, 1, 2 or 3";
                    }
                }
                
                if(!empty($data["is_completed"])){
                    if(filter_var($data["is_completed"], FILTER_VALIDATE_INT) === false){
                        $errors[] = "is_completed invalid format";
                    }
                    else if(!in_array($data["is_completed"], [0,1], true)){
                        $errors[] = "is_completed must be 1 or 0";
                    }
                }
                break;

            case "DELETE":
                break;
        }

        return $errors;
    }
}