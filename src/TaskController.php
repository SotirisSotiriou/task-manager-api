<?php

class TaskController{
    private TaskGateway $gateway;

    public function __construct(TaskGateway $gateway){
        $this->gateway = $gateway;
    }

    
    public function processRequest(string $method, int $user_id): void{

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



    public function getValidationErrors(array $data, string $method): array{
        $errors = [];



        return $errors;
    }
}