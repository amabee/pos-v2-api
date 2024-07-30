<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

class Api
{
    private $filePath = 'data.json';

    public function Login($username, $password)
    {
        if ($username == "aiahkins" && $password == "password") {
            $CurrentUser = ['username' => "aiahkins", "CID" => '02-1920-03954'];
        } else if ($username == "maloiskie" && $password == "password") {
            $CurrentUser = ['username' => "maloiskie", "CID" => '02-1920-03955'];
        } else {
            echo json_encode(array("error" => "User not found!"));
            return;
        }

        echo json_encode($CurrentUser);
    }

    public function GetItem($barCode)
    {
        $products = [
            [
                "barcode" => "1001",
                "p_name" => "Bulad",
                "price" => 10
            ],
            [
                "barcode" => "1002",
                "p_name" => "Mantika",
                "price" => 30
            ],
            [
                "barcode" => "1003",
                "p_name" => "Noodles",
                "price" => 20
            ],
            [
                "barcode" => "1004",
                "p_name" => "Sabon",
                "price" => 35
            ],
            [
                "barcode" => "1005",
                "p_name" => "Shampoo",
                "price" => 15
            ]
        ];

        foreach ($products as $product) {
            if ($product["barcode"] === $barCode) {
                echo json_encode($product);
                return;
            }
        }

        echo json_encode(array("error" => "Product not found"));
    }

    public function addSalesToCurrentCashier($cashierId, $newItem)
    {
        $jsonData = file_get_contents($this->filePath);
        $data = json_decode($jsonData, true);

        if ($data === null) {
            echo json_encode(array("error" => "Error reading JSON data"));
            return;
        }

        if ($data['cashier']['id'] !== $cashierId) {
            echo json_encode(array("error" => "Cashier not found"));
            return;
        }

        $data['items'][] = $newItem;

        if (file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT)) === false) {
            echo json_encode(array("error" => "Error writing JSON data"));
        } else {
            echo json_encode(array("success" => "Sales updated for cashier ID: $cashierId"));
        }
    }
}

$api = new Api();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$operation = isset($_REQUEST["op"]) ? $_REQUEST["op"] : null;

switch ($operation) {
    case "login":
        if (isset($_REQUEST["username"]) && isset($_REQUEST["password"])) {
            $username = $_REQUEST["username"];
            $password = $_REQUEST["password"];
            $api->Login($username, $password);
        } else {
            echo json_encode(array("error" => "Missing username or password"));
        }
        break;

    case "getItem":
        if (isset($_REQUEST["barCode"])) {
            $barCode = $_REQUEST["barCode"];
            $api->GetItem($barCode);
        } else {
            echo json_encode(array("error" => "Barcode not set"));
        }
        break;

    case "addSalesToCurrentCashier":
        if (isset($_REQUEST["cid"]) && isset($_REQUEST["items"])) {
            $cashierId = $_REQUEST["cid"];
            $newItem = $_REQUEST["items"];
            $api->addSalesToCurrentCashier($cashierId, $newItem);
        } else {
            echo json_encode(array("error" => "Missing cashier ID or items"));
        }
        break;

    default:
        echo json_encode(array("error" => "No Such Operation"));
        break;
}
?>