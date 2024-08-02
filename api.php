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
            $CurrentUser = [
                'success' => true,
                'message' => 'Login successful',
                'data' => ['username' => "aiahkins", "CID" => '02-1920-03954', "image" => "image1.jpg"]
            ];
        } else if ($username == "maloiskie" && $password == "password") {
            $CurrentUser = [
                'success' => true,
                'message' => 'Login successful',
                'data' => ['username' => "maloiskie", "CID" => '02-1920-03955']
            ];
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found!']);
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

    public function addSalesToCurrentCashier($cashierID, $items)
    {
        if (file_exists($this->filePath)) {
            $jsonData = file_get_contents(($this->filePath));
            $data = json_decode($jsonData, true);
        } else {
            echo json_encode(array("error" => "data.json not found wtf!!"));

        }

        if (isset($data['SavedSales'])) {
            $data["SavedSales"] = [];
        }

        $newSales = [
            "cashierID" => $cashierID,
            "items" => $items
        ];

        $data["SavedSales"][] = $newSales;

        if (file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT))) {
            echo json_encode(array("success" => "Sales saved"));
        } else {
            echo json_encode(array("error" => "Wtf error!????"));
        }
    }

    public function saveSomeItems($cashierID, $customerID, $items)
    {

        if (file_exists($this->filePath)) {
            $jsonData = file_get_contents(($this->filePath));
            $data = json_decode($jsonData, true);

            if (!isset($data["SavedItems"])) {
                $data["SavedItems"] = [];
            }
            $newTransaction = [
                'cashierID' => $cashierID,
                'customerID' => $customerID,
                'items' => $items
            ];

            $data['SavedItems'][] = $newTransaction;

            if (file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT))) {
                echo json_encode(array('success' => 'Successfully Added Items to the saved items'));
            } else {
                echo json_encode(array('error' => 'wtf!'));
            }


        }
    }

    public function retrieveSaveItems($cashierID, $customerID)
    {

        if (!file_exists($this->filePath)) {
            echo json_encode(array("error" => 'Data file not found.'));
        }

        $jsonData = file_get_contents($this->filePath);

        $data = json_decode($jsonData, true);


        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(array("error" => 'Error decoding JSON data.'));
        }

        $filteredItems = array_filter($data['SavedItems'], function ($savedItem) use ($cashierID, $customerID) {
            return $savedItem['cashierID'] === $cashierID && $savedItem['customerID'] === $customerID;
        });

        echo json_encode($filteredItems);
    }

}

$api = new Api();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$operation = isset($_REQUEST["op"]) ? $_REQUEST["op"] : null;
error_log("Received operation: " . $operation);

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
        if (isset($_REQUEST["cashierID"]) && isset($_REQUEST["items"])) {
            $cashierId = $_REQUEST["cashierID"];
            $newItem = $_REQUEST["items"];
            $api->addSalesToCurrentCashier($cashierId, $newItem);
        } else {
            echo json_encode(array("error" => "Missing cashier ID or items"));
        }
        break;

    case "saveSomeItems":
        if (isset($_REQUEST["cashierID"])) {
            if (isset($_REQUEST["customerID"])) {
                if (isset($_REQUEST["items"])) {
                    $cashierID = $_REQUEST["cashierID"];
                    $customerID = $_REQUEST["customerID"];
                    $items = $_REQUEST["items"];
                    $api->saveSomeItems($cashierID, $customerID, $items);
                } else {
                    echo json_encode(array("error" => "Items not found wtf!"));
                }
            } else {
                echo json_encode(array("error" => "Customer ID Not Set"));
            }
        } else {
            echo json_encode(array("error" => "Casheir ID not set!"));
        }
        break;

    case "retrieveSaveItems":
        if (isset($_REQUEST["cashierID"])) {
            if (isset($_REQUEST["customerID"])) {
                $cashierID = $_REQUEST["cashierID"];
                $customerID = $_REQUEST["customerID"];
                $api->retrieveSaveItems($cashierID, $customerID);
            } else {
                echo json_encode(array("error" => "Customer ID is not set"));
            }
        } else {
            echo json_encode(array("error" => "Cashier ID is not set"));
        }
        break;

    default:
        echo json_encode(array("error" => "No Such Operation"));
        break;
}
?>